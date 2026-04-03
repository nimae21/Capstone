<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;



class ProductController extends Controller
{
    public function index(){
 $products = Product::with(['category', 'brand'])->get();
        $categories = Category::all();
        $brands = Brand::all();
        return view('admin.products.index', compact('products', 'categories', 'brands'));
    }
 public function store(Request $request)
{
    Product::create([
        'product_name' => $request->product_name,
        'product_description' => $request->product_description,
        'category_id' => $request->category_id,
        'brand_id' => $request->brand_id,
    ]);

    return redirect()->route('admin.products.index');
}

 public function destroy(Product $product)
{
    $product->delete();
    return redirect()->route('admin.products.index');
}
 // Show the edit form
public function edit(Product $product)
{
    $categories = Category::all();
    $brands = Brand::all();
    return view('admin.products.edit', compact('product', 'categories', 'brands'));
}

// Update the product
public function update(Request $request, Product $product)
{
    $product->update([
        'product_name' => $request->product_name,
        'product_description' => $request->product_description,
        'category_id' => $request->category_id,
        'brand_id' => $request->brand_id,
    ]);

    return redirect()->route('admin.products.index');
}

    
}
