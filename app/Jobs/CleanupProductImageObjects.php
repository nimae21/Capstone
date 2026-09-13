<?php

namespace App\Jobs;

use App\Models\BackgroundOperation;
use App\Models\ProductImage;
use App\Services\QueueMonitor;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class CleanupProductImageObjects implements ShouldBeEncrypted, ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries;

    public int $timeout;

    public bool $failOnTimeout = true;

    public int $uniqueFor;

    public function __construct(public string $operationKey)
    {
        $this->tries = (int) config('background_jobs.tries', 5);
        $this->timeout = (int) config('background_jobs.image_cleanup_timeout', 60);
        $this->uniqueFor = (int) config('background_jobs.unique_for', 600);
        $this->onQueue(config('background_jobs.queue', 'background'));
        $this->afterCommit();
    }

    public function handle(QueueMonitor $monitor): void
    {
        $monitor->beat();
        $operation = BackgroundOperation::where('operation_key', $this->operationKey)->first();
        if (! $operation || $operation->processed_at) {
            return;
        }

        try {
            foreach ((array) ($operation->payload['paths'] ?? []) as $path) {
                if (! is_string($path)
                    || ! str_starts_with($path, 'products/')
                    || str_contains($path, '..')
                    || str_contains($path, chr(92))
                    || str_contains($path, chr(0))) {
                    throw new RuntimeException('Refusing an invalid product-image cleanup path.');
                }

                $referenced = ProductImage::query()->where(function ($query) use ($path) {
                    $query->where('image_path', $path)
                        ->orWhere('thumbnail_path', $path)
                        ->orWhere('medium_path', $path)
                        ->orWhere('large_path', $path);
                })->exists();

                if (! $referenced && ! Storage::disk(config('product_images.disk'))->delete($path)) {
                    throw new RuntimeException('Storage did not confirm image deletion.');
                }
            }

            BackgroundOperation::whereKey($operation->getKey())->update([
                'processed_at' => now(),
                'last_error' => null,
            ]);
        } catch (\Throwable $exception) {
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

    public function failed(?\Throwable $exception): void
    {
        BackgroundOperation::where('operation_key', $this->operationKey)->update([
            'available_at' => now()->addMinutes(5),
            'last_error' => substr($exception?->getMessage() ?? 'product_image_cleanup_failed', 0, 255),
        ]);
    }
}
