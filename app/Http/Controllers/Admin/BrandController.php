<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $query = Brand::query();

        if ($request->filled('search')) {
            $query->where('brand_name', 'like', '%' . trim($request->search) . '%');
        }

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
        $brandName = $this->validateBrand($request);

        Brand::create([
            'brand_name' => $brandName,
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
        $brandName = $this->validateBrand($request, $brand->brand_id);

        $brand->update([
            'brand_name' => $brandName,
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

    /**
     * Validate and normalize brand name.
     */
    private function validateBrand(Request $request, $ignoreId = null)
    {
        $request->merge([
            'brand_name' => ucwords(strtolower(trim($request->brand_name)))
        ]);

        $request->validate([
            'brand_name' => 'required|string|max:255',
        ]);

        $exists = Brand::whereRaw('LOWER(brand_name) = ?', [
            strtolower($request->brand_name)
        ]);

        if ($ignoreId) {
            $exists->where('brand_id', '!=', $ignoreId);
        }

        if ($exists->exists()) {
            abort(
                back()
                    ->withErrors([
                        'brand_name' => 'This brand already exists.'
                    ])
                    ->withInput()
            );
        }

        return $request->brand_name;
    }
}