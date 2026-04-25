<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Brand;

class BrandController extends Controller
{
   public function index()
        {
            $brands = Brand::paginate(5);
            return view('admin.brands.index',compact('brands'));
    }


    public function store(Request $request)
{
    $request->validate([
        'brand_name' => 'required|string|max:255|unique:brands,brand_name'
    ]);

    $brand = Brand::create([
        'brand_name' => $request->brand_name
    ]);

    if ($request->ajax()) {
        return response()->json([
            'success' => true,
            'message' => 'Brand "' . $brand->brand_name . '" created successfully!',
            'brand' => $brand
        ]);
    }

    return redirect()->route('admin.brands.index')->with('success', 'Brand created successfully!');
}


public function destroy(Brand $brand)
{
    $brand->delete();
    return redirect()->route('admin.brands.index');
}

public function edit(Brand $brand)
{
    return view('admin.brands.edit', compact('brand'));
}

public function update(Request $request, Brand $brand)
{
    $request->validate([
        'brand_name' => 'required|string|max:255',
    ]);

    $brand->update([
        'brand_name' => $request->brand_name,
    ]);

    return redirect()
        ->route('admin.brands.index')
        ->with('success', 'Brand updated successfully!');
}
}
