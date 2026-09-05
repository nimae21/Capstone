<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\ProductImageService;
use Illuminate\Http\Request;

class ProductImageController extends Controller
{
    public function __construct(
        protected ProductImageService $imageService
    ) {}

    public function store(Request $request, Product $product)
    {
        $request->validate([
            'images.*' => 'required|image|mimes:jpg,jpeg,png,webp,gif|max:5120',
            'color' => 'nullable|string|max:50',
        ]);

        $this->imageService->storeMany($product, $request->file('images'), $request->color);

        return back()->with('success', 'Images uploaded successfully.');
    }

    public function destroy(ProductImage $image)
    {
        $this->imageService->delete($image);

        return back()->with('success', 'Image deleted successfully.');
    }

    public function setPrimary(ProductImage $image)
    {
        $this->imageService->setPrimary($image);

        return back()->with('success', 'Primary image updated successfully.');
    }

    public function assignColor(Request $request, ProductImage $image)
    {
        $request->validate([
            'color' => 'nullable|string|max:50',
        ]);

        $this->imageService->assignColor($image, $request->color);

        return back()->with('success', 'Image color updated successfully.');
    }
}