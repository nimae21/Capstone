<?php

namespace App\Http\Controllers;

use App\Models\Product;

class PageController extends Controller
{
    public function home()
    {
        return view('pages.home');
    }

    /**
     * Reusable product query for category pages.
     * Eager-loads images so views never trigger N+1 queries.
     */
    private function getProductsByCategory(int $categoryId)
    {
        return Product::with(['variants.stocks', 'images'])
            ->where('is_active', true)
            ->where('category_id', $categoryId)
            ->orderBy('product_name')
            ->paginate(5);
    }

    public function men()
    {
        return view('pages.men', [
            'products' => $this->getProductsByCategory(1),
        ]);
    }

    public function women()
    {
        return view('pages.women', [
            'products' => $this->getProductsByCategory(2),
        ]);
    }

    public function kids()
    {
        return view('pages.kids', [
            'products' => $this->getProductsByCategory(5),
        ]);
    }

    public function sale()
    {
        return view('pages.sale', [
            'products' => $this->getProductsByCategory(7),
        ]);
    }

    public function new()
    {
        return view('pages.new', [
            'products' => $this->getProductsByCategory(7),
        ]);
    }

    public function showProduct($id)
    {
        $product = Product::with([
            'images',
            'category',
            'brand',
            'variants.stocks',
        ])->findOrFail($id);

        foreach ($product->variants as $variant) {
            $variant->available_stock = $variant->stocks->sum('remaining_quantity');

            $latestStock = $variant->stocks()
                ->latest('deliver_date')
                ->first();

            $variant->current_price = $latestStock?->price ?? 0;
        }

        return view('product.show', compact('product'));
    }
}