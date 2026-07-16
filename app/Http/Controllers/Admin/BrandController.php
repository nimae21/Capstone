<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Brand;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $query = Brand::query();

        // Search
        if ($request->filled('search')) {
            $query->where('brand_name', 'LIKE', '%' . trim($request->search) . '%');
        }

        // Status Filter
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $brands = $query
            ->orderBy('brand_name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.brands.index', [
            'brands' => $brands,
            'totalBrands' => Brand::count(),
            'activeBrands' => Brand::where('is_active', true)->count(),
            'inactiveBrands' => Brand::where('is_active', false)->count(),
        ]);
    }

    public function store(Request $request)
{
    // Normalize the brand name
    $request->merge([
        'brand_name' => ucwords(strtolower(trim($request->brand_name)))
    ]);

    // Basic validation
    $request->validate([
        'brand_name' => 'required|string|max:255',
    ]);

    // Check for duplicate (case-insensitive)
    $exists = Brand::whereRaw('LOWER(brand_name) = ?', [
        strtolower($request->brand_name)
    ])->exists();

    if ($exists) {
        return back()
            ->withErrors([
                'brand_name' => 'This brand already exists.'
            ])
            ->withInput();
    }

    // Save
    Brand::create([
        'brand_name' => $request->brand_name,
        'is_active' => true,
    ]);

    return redirect()
        ->route('admin.brands.index')
        ->with('success', 'Brand created successfully!');
}

    public function edit(Brand $brand)
    {
        return view('admin.brands.edit', compact('brand'));
    }

    public function update(Request $request, Brand $brand)
{
    // Normalize the brand name
    $request->merge([
        'brand_name' => ucwords(strtolower(trim($request->brand_name)))
    ]);

    // Basic validation
    $request->validate([
        'brand_name' => 'required|string|max:255',
    ]);

    // Check for duplicate (excluding the current brand)
    $exists = Brand::whereRaw('LOWER(brand_name) = ?', [
        strtolower($request->brand_name)
    ])
    ->where('brand_id', '!=', $brand->brand_id)
    ->exists();

    if ($exists) {
        return back()
            ->withErrors([
                'brand_name' => 'This brand already exists.'
            ])
            ->withInput();
    }

    // Update
    $brand->update([
        'brand_name' => $request->brand_name,
    ]);

    return redirect()
        ->route('admin.brands.index')
        ->with('success', 'Brand updated successfully!');
}

    public function destroy(Brand $brand)
    {
        if ($brand->products()->exists()) {
            return back()->with(
                'error',
                'This brand is being used by one or more products and cannot be deactivated.'
            );
        }

        $brand->update([
            'is_active' => false,
        ]);

        return redirect()
            ->route('admin.brands.index')
            ->with('success', 'Brand deactivated successfully.');
    }

    public function restore($brand_id)
    {
        $brand = Brand::findOrFail($brand_id);

        $brand->update([
            'is_active' => true,
        ]);

        return redirect()
            ->route('admin.brands.index')
            ->with('success', 'Brand activated successfully.');
    }
}