<?php

namespace App\Http\Controllers\guest;

use App\Http\Controllers\Controller;
use App\Models\Product;

class GuestController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'brand', 'images'])
            ->latest()
            ->paginate(6);

        return view('guest.index', compact('products'));
    }
}