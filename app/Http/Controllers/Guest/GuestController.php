<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Stock;
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

    $price = Stock::selectRaw('MIN(stocks.price)')
        ->join('product_variants', 'product_variants.product_variant_id', '=', 'stocks.product_variant_id')
        ->whereColumn('product_variants.product_id', 'products.product_id');

    $products = Product::with(['images', 'primaryImage'])
        ->joinSub(ProductSales::totals(), 'sales', function ($join) {
            $join->on('sales.product_id', '=', 'products.product_id');
        })
        ->where('products.is_active', true)
        ->select('products.*', 'sales.total_sold')
        ->addSelect(['display_price' => $price])
        ->orderByDesc('sales.total_sold')
        ->orderBy('products.product_id')
        ->limit(3)
        ->get();

    return view('guest.index', compact('products'));
}
//fixed folder name
//sana all fix na
}
