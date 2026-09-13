<?php

namespace App\Console\Commands;

use App\Models\ProductImage;
use App\Services\ProductImageProcessor;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class BackfillProductImageVariants extends Command
{
    protected $signature = 'product-images:backfill
        {--apply : Generate and record variants; without this option the command is a dry run}
        {--batch= : Rows loaded per batch}
        {--max= : Maximum images inspected in this run}
        {--product= : Restrict to one product ID}
        {--image= : Restrict to one image ID}';

    protected $description = 'Safely backfill responsive product-image variants in bounded, resumable batches';

    public function handle(ProductImageProcessor $processor): int
    {
        $batch = max(1, min(200, (int) ($this->option('batch') ?: config('product_images.backfill_batch'))));
        $maximum = max(1, min(1000, (int) ($this->option('max') ?: config('product_images.backfill_max'))));
        $apply = (bool) $this->option('apply');
        $base = $this->pendingQuery();

        if (! $apply) {
            $ids = (clone $base)->orderBy('image_id')->limit($maximum)->pluck('image_id');
            $remaining = (clone $base)->count();
            $this->info("Dry run: {$remaining} image(s) need variants; this bounded run would inspect {$ids->count()}.");
            if ($ids->isNotEmpty()) {
                $this->line('Image IDs: '.$ids->implode(', '));
            }
            $this->comment('No storage objects or database rows were changed. Pass --apply to process them.');

            return self::SUCCESS;
        }

        $processed = 0;
        $failed = 0;
        $cursor = 0;

        while ($processed + $failed < $maximum) {
            $limit = min($batch, $maximum - $processed - $failed);
            $images = (clone $base)->where('image_id', '>', $cursor)
                ->orderBy('image_id')->limit($limit)->get();
            if ($images->isEmpty()) {
                break;
            }

            foreach ($images as $image) {
                $cursor = (int) $image->image_id;
                $attributes = [];
                try {
                    $attributes = $processor->generateVariants($image);
                    $this->verify($attributes);
                    $saved = DB::transaction(function () use ($image, $attributes) {
                        $current = ProductImage::whereKey($image->getKey())->lockForUpdate()->first();
                        if (! $current || $current->variants_generated_at) {
                            return false;
                        }

                        $current->fill($attributes)->save();

                        return true;
                    }, 3);
                    if (! $saved) {
                        $processor->deleteUnreferenced($this->variantPaths($attributes));
                        $this->line("Skipped image {$image->getKey()}; it was completed or removed concurrently.");

                        continue;
                    }
                    $processed++;
                    $this->line("Generated variants for image {$image->getKey()}.");
                } catch (Throwable $exception) {
                    if ($attributes !== []) {
                        $processor->deleteUnreferenced($this->variantPaths($attributes));
                    }
                    $failed++;
                    report($exception);
                    $this->error("Image {$image->getKey()} failed: {$exception->getMessage()}");
                }
            }
        }

        $remaining = $this->pendingQuery()->count();
        $this->info("Processed {$processed}; failed {$failed}; {$remaining} remain. Re-run safely to resume.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function pendingQuery(): Builder
    {
        return ProductImage::query()
            ->where(function (Builder $query) {
                $query->whereNull('thumbnail_path')
                    ->orWhereNull('medium_path')
                    ->orWhereNull('large_path')
                    ->orWhereNull('variants_generated_at');
            })
            ->when($this->option('product'), fn (Builder $query, $id) => $query->where('product_id', (int) $id))
            ->when($this->option('image'), fn (Builder $query, $id) => $query->where('image_id', (int) $id));
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
                throw new \RuntimeException("Generated {$variant} verification failed.");
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
