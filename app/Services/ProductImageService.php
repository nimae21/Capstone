<?php

namespace App\Services;

use App\Jobs\CleanupProductImageObjects;
use App\Jobs\ProcessProductImageVariants;
use App\Models\BackgroundOperation;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProductImageService
{
    public function __construct(
        private readonly ProductImageProcessor $processor,
        private readonly ReliableJobDispatcher $dispatcher,
    ) {}

    /**
     * @param  UploadedFile[]  $images
     */
    public function storeMany(Product $product, array $images, ?string $color = null): void
    {
        $descriptors = $this->processor->stageMany($images, false);
        $displayOrder = $product->images()->max('display_order') ?? 0;
        $hasPrimary = $product->images()->where('is_primary', true)->exists();

        try {
            DB::transaction(function () use ($product, $descriptors, $color, &$displayOrder, $hasPrimary) {
                foreach ($descriptors as $index => $descriptor) {
                    $created = ProductImage::create(array_merge($descriptor, [
                        'product_id' => $product->product_id,
                        'color' => $color,
                        'display_order' => ++$displayOrder,
                        'is_primary' => ! $hasPrimary && $index === 0,
                    ]));
                    $this->scheduleVariantGeneration($created);
                }
            });
        } catch (Throwable $exception) {
            $this->processor->deleteQuietly($this->pathsFromDescriptors($descriptors));
            throw $exception;
        }
    }

    public function delete(ProductImage $image): void
    {
        DB::transaction(function () use ($image) {
            $locked = ProductImage::whereKey($image->getKey())->lockForUpdate()->firstOrFail();
            $paths = $locked->storagePaths();
            $productId = $locked->product_id;
            $wasPrimary = $locked->is_primary;
            $locked->delete();

            if ($wasPrimary) {
                ProductImage::where('product_id', $productId)
                    ->orderBy('display_order')->orderBy('image_id')->first()?->update(['is_primary' => true]);
            }

            $this->scheduleCleanup($paths, 'deleted:'.$locked->getKey());
        }, 3);
    }

    public function setPrimary(ProductImage $image): void
    {
        DB::transaction(function () use ($image) {
            ProductImage::where('product_id', $image->product_id)->lockForUpdate()->get();
            ProductImage::where('product_id', $image->product_id)->update(['is_primary' => false]);
            ProductImage::whereKey($image->getKey())->update(['is_primary' => true]);
        }, 3);
    }

    public function assignColor(ProductImage $image, ?string $color): void
    {
        $image->update(['color' => $color ?: null]);
    }

    public function scheduleVariantGeneration(ProductImage $image): BackgroundOperation
    {
        $key = 'product-image-variants:'.$image->getKey().':'.substr(
            hash('sha256', $image->content_hash ?: $image->image_path),
            0,
            40,
        );
        $operation = BackgroundOperation::firstOrCreate(
            ['operation_key' => $key],
            ['type' => 'product_image_variants', 'payload' => ['image_id' => $image->getKey()], 'available_at' => now()],
        );
        if (! $operation->processed_at) {
            $this->dispatcher->dispatch(new ProcessProductImageVariants($key));
        }

        return $operation;
    }

    public function scheduleCleanup(array $paths, string $reason): BackgroundOperation
    {
        $paths = array_values(array_unique(array_filter(
            $paths,
            fn ($path) => is_string($path)
                && str_starts_with($path, 'products/')
                && ! str_contains($path, '..')
                && ! str_contains($path, chr(92))
                && ! str_contains($path, chr(0)),
        )));
        $key = 'product-image-cleanup:'.substr(hash('sha256', $reason.'|'.implode('|', $paths)), 0, 48);
        $operation = BackgroundOperation::firstOrCreate(
            ['operation_key' => $key],
            ['type' => 'product_image_cleanup', 'payload' => ['paths' => $paths], 'available_at' => now()],
        );
        if (! $operation->processed_at) {
            $this->dispatcher->dispatch(new CleanupProductImageObjects($key));
        }

        return $operation;
    }

    public function pathsFromDescriptors(array $descriptors): array
    {
        $paths = [];
        foreach ($descriptors as $descriptor) {
            if (is_string($descriptor)) {
                $paths[] = $descriptor;

                continue;
            }
            foreach (['image_path', 'thumbnail_path', 'medium_path', 'large_path'] as $key) {
                if (! empty($descriptor[$key])) {
                    $paths[] = $descriptor[$key];
                }
            }
        }

        return array_values(array_unique($paths));
    }
}
