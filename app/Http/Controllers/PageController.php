<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Product;

class PageController extends Controller
{
    public function home()
    {
        return view('pages.home');
    }

    public function men()
{
    $products = Product::with('variants.stocks')
        ->where('category_id', 1)
        ->get();

    return view('pages.men', compact('products'));
}

    public function women()
    {
        $products = Product::with('variants.stocks')
        ->where('category_id', 2) 
        ->get();

    return view('pages.women', compact('products'));
    }

    public function kids()
    {
        $products = Product::with('variants.stocks')
        ->where('category_id', 5) 
        ->get();

    return view('pages.kids', compact('products'));
    }

    public function sale()
    {
        return view('pages.sale');
    }

    public function new()
    {
        return view('pages.new');
    }

    
}
    