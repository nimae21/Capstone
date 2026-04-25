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
        ->paginate(5);

    return view('pages.men', compact('products'));
}

    public function women()
    {
        $products = Product::with('variants.stocks')
        ->where('category_id', 2) 
        ->paginate(5);

    return view('pages.women', compact('products'));
    }

    public function kids()
    {
        $products = Product::with('variants.stocks')
        ->where('category_id', 5) 
        ->paginate(5);

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

    public function showProduct($id)
{
    $product = Product::with('variants.stocks')
        ->findOrFail($id);

    return view('product.show', compact('product'));
}

    
}
    