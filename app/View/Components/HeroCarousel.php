<?php

namespace App\View\Components;

use App\Models\Product;
use Illuminate\View\Component;
use Illuminate\View\View;

class HeroCarousel extends Component
{
    public function render(): View
    {
        // Bound the hero independently of catalog filters and bestseller sales.
        $products = Product::query()
            ->where('is_active', true)
            ->with(['images' => fn ($query) => $query
                ->whereNotNull('image_path')
                ->where('image_path', '!=', '')
                ->reorder()->orderByDesc('is_primary')
                ->orderBy('display_order')->orderBy('image_id')->limit(1)])
            ->orderByDesc('product_id')
            ->limit(8)
            ->get(['product_id', 'product_name']);

        return view('components.hero-carousel', [
            'products' => $products,
            'fallback' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400',
        ]);
    }
}
