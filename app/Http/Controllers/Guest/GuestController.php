<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\CatalogCache;
use App\Services\ProductSales;
use Illuminate\Support\Facades\Auth;

class GuestController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            return auth()->user()->isAdmin()
                ? redirect()->route('admin.dashboard')
                : redirect()->route('home');
        }

        $products = app(CatalogCache::class)->remember('guest-bestsellers', function () {
            return Product::query()
                ->forStorefrontCards()
                ->with([
                    'images' => fn ($images) => $images
                        ->select('image_id', 'product_id', 'image_path', 'thumbnail_path', 'medium_path', 'large_path', 'image_width', 'image_height', 'thumbnail_width', 'thumbnail_height', 'medium_width', 'medium_height', 'large_width', 'large_height', 'is_primary', 'display_order')
                        ->reorder()->orderByDesc('is_primary')->orderBy('display_order')->orderBy('image_id')->limit(1),
                ])
                ->joinSub(ProductSales::totals(), 'sales', fn ($join) => $join
                    ->on('sales.product_id', '=', 'products.product_id'))
                ->where('products.is_active', true)
                ->addSelect('sales.total_sold')
                ->orderByDesc('sales.total_sold')
                ->orderBy('products.product_id')
                ->limit(3)
                ->get();
        });

        return view('guest.index', compact('products'));
    }
}
