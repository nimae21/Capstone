<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Product;
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

    $products = Product::with(['category', 'brand', 'images'])
        ->where('is_active', true)
        ->orderBy('product_name')
        ->paginate(6);

    return view('guest.index', compact('products'));
}
//fixed folder name
}