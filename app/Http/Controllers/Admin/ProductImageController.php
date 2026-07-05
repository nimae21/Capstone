<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    /**
     * Upload new images.
     */
    public function store(Request $request, Product $product)
    {
        $request->validate([
            'images.*' => 'required|image|mimes:jpg,jpeg,png,webp,gif|max:5120',
        ]);

        $displayOrder = $product->images()->max('display_order') ?? 0;

        foreach ($request->file('images') as $index => $image) {

            $path = $image->store('products', 'public');

            ProductImage::create([
                'product_id'    => $product->product_id,
                'image_path'    => $path,
                'display_order' => ++$displayOrder,

                // Only the first image ever uploaded becomes primary
                'is_primary' => !$product->images()
                    ->where('is_primary', true)
                    ->exists() && $index == 0,
            ]);
        }

        return back()->with(
            'success',
            'Images uploaded successfully.'
        );
    }

    /**
     * Delete image.
     */
    public function destroy(ProductImage $image)
    {
        Storage::disk('public')->delete($image->image_path);

        $product = $image->product;

        $wasPrimary = $image->is_primary;

        $image->delete();

        if ($wasPrimary) {

            $next = $product->images()->first();

            if ($next) {

                $next->update([
                    'is_primary' => true
                ]);
            }
        }

        return back()->with(
            'success',
            'Image deleted.'
        );
    }

    /**
     * Set primary image.
     */
    public function setPrimary(ProductImage $image)
    {
        ProductImage::where(
            'product_id',
            $image->product_id
        )->update([
            'is_primary' => false
        ]);

        $image->update([
            'is_primary' => true
        ]);

        return back()->with(
            'success',
            'Primary image updated.'
        );
    }
}