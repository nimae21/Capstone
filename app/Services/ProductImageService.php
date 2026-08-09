<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProductImageService
{
    /**
     * Store newly uploaded images for a product.
     *
     * @param  UploadedFile[]  $images
     */
    public function storeMany(Product $product, array $images): void
    {
        $displayOrder = $product->images()->max('display_order') ?? 0;
        $hasPrimary = $product->images()->where('is_primary', true)->exists();

        foreach ($images as $index => $image) {
            $path = $image->store('products', 'public');

            ProductImage::create([
                'product_id'    => $product->product_id,
                'image_path'    => $path,
                'display_order' => ++$displayOrder,
                'is_primary'    => !$hasPrimary && $index === 0,
            ]);
        }
    }

    public function delete(ProductImage $image): void
    {
        Storage::disk('public')->delete($image->image_path);

        $product = $image->product;
        $wasPrimary = $image->is_primary;

        $image->delete();

        if ($wasPrimary) {
            $product->images()->first()?->update(['is_primary' => true]);
        }
    }

    public function setPrimary(ProductImage $image): void
    {
        ProductImage::where('product_id', $image->product_id)
            ->update(['is_primary' => false]);

        $image->update(['is_primary' => true]);
    }
}