<?php

namespace App\Jobs;

use App\Models\BackgroundOperation;
use App\Models\ProductImage;
use App\Services\ProductImageProcessor;
use App\Services\QueueMonitor;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class ProcessProductImageVariants implements ShouldBeEncrypted, ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries;

    public int $timeout;

    public bool $failOnTimeout = true;

    public int $uniqueFor;

    public function __construct(public string $operationKey)
    {
        $this->tries = (int) config('background_jobs.tries', 5);
        $this->timeout = (int) config('background_jobs.image_processing_timeout', 120);
        $this->uniqueFor = (int) config('background_jobs.unique_for', 600);
        $this->onQueue(config('background_jobs.queue', 'background'));
        $this->afterCommit();
    }

    public function handle(ProductImageProcessor $processor, QueueMonitor $monitor): void
    {
        $monitor->beat();
        $operation = BackgroundOperation::where('operation_key', $this->operationKey)->first();
        if (! $operation || $operation->processed_at) {
            return;
        }

        $image = ProductImage::find($operation->payload['image_id'] ?? null);
        if (! $image || $image->hasResponsiveVariants()) {
            $operation->update(['processed_at' => now(), 'last_error' => null]);

            return;
        }

        $attributes = [];
        try {
            $attributes = $processor->generateVariants($image);
            $this->verify($attributes);
            $saved = DB::transaction(function () use ($image, $attributes, $operation) {
                $current = ProductImage::whereKey($image->getKey())->lockForUpdate()->first();
                $lockedOperation = BackgroundOperation::whereKey($operation->getKey())->lockForUpdate()->first();
                if (! $lockedOperation || $lockedOperation->processed_at) {
                    return false;
                }
                if ($current && ! $current->hasResponsiveVariants()) {
                    $current->fill($attributes)->save();
                }
                $lockedOperation->update(['processed_at' => now(), 'last_error' => null]);

                return (bool) $current;
            }, 3);
            if (! $saved) {
                $processor->deleteUnreferenced($this->variantPaths($attributes));
            }
        } catch (Throwable $exception) {
            BackgroundOperation::whereKey($operation->getKey())->update([
                'attempts' => DB::raw('attempts + 1'),
                'available_at' => now()->addSeconds($this->backoff()[0] ?? 60),
                'last_error' => substr($exception->getMessage(), 0, 255),
            ]);
            throw $exception;
        }
    }

    public function uniqueId(): string
    {
        return $this->operationKey;
    }

    public function backoff(): array
    {
        return config('background_jobs.backoff', [10, 30, 60, 300]);
    }

    public function failed(?Throwable $exception): void
    {
        BackgroundOperation::where('operation_key', $this->operationKey)->update([
            'available_at' => now()->addMinutes(5),
            'last_error' => substr($exception?->getMessage() ?? 'product_image_processing_failed', 0, 255),
        ]);
    }

    private function verify(array $attributes): void
    {
        $disk = Storage::disk(config('product_images.disk'));
        foreach (['thumbnail', 'medium', 'large'] as $variant) {
            $path = $attributes[$variant.'_path'] ?? null;
            if (! is_string($path) || ! str_starts_with($path, 'products/')
                || ! $disk->exists($path) || (int) $disk->size($path) < 1
                || (int) ($attributes[$variant.'_width'] ?? 0) < 1
                || (int) ($attributes[$variant.'_height'] ?? 0) < 1) {
                throw new RuntimeException("Generated {$variant} verification failed.");
            }
        }
    }

    private function variantPaths(array $attributes): array
    {
        return array_filter([
            $attributes['thumbnail_path'] ?? null,
            $attributes['medium_path'] ?? null,
            $attributes['large_path'] ?? null,
        ]);
    }
}
