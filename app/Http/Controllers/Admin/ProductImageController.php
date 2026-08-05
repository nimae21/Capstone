<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    /**
     * Upload product images.
     */
    public function store(Request $request, Product $product)
    {
        $request->validate([
            'images.*' => 'required|image|mimes:jpg,jpeg,png,webp,gif|max:5120',
        ]);

        $displayOrder = $product->images()->max('display_order') ?? 0;
        $hasPrimary = $product->images()->where('is_primary', true)->exists();

        foreach ($request->file('images') as $index => $image) {

            $path = $image->store('products', 'public');

            ProductImage::create([
                'product_id'    => $product->product_id,
                'image_path'    => $path,
                'display_order' => ++$displayOrder,
                'is_primary'    => !$hasPrimary && $index === 0,
            ]);
        }

        return back()->with(
            'success',
            'Images uploaded successfully.'
        );
    }

    /**
     * Delete a product image.
     */
    public function destroy(ProductImage $image)
    {
        Storage::disk('public')->delete($image->image_path);

        $product = $image->product;
        $wasPrimary = $image->is_primary;

        $image->delete();

        if ($wasPrimary) {
            $nextImage = $product->images()->first();

            if ($nextImage) {
                $nextImage->update([
                    'is_primary' => true,
                ]);
            }
        }

        return back()->with(
            'success',
            'Image deleted successfully.'
        );
    }

    /**
     * Set an image as the primary image.
     */
    public function setPrimary(ProductImage $image)
    {
        ProductImage::where('product_id', $image->product_id)
            ->update([
                'is_primary' => false,
            ]);

        $image->update([
            'is_primary' => true,
        ]);

        return back()->with(
            'success',
            'Primary image updated successfully.'
        );
    }
}