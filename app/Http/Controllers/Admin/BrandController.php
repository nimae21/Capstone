<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBrandRequest;
use App\Http\Requests\UpdateBrandRequest;
use App\Models\Brand;
use App\Traits\HasCaseInsensitiveUniqueName;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    use HasCaseInsensitiveUniqueName;

    public function index(Request $request)
    {
        $query = Brand::query();

        if ($request->filled('search')) {
            $query->where('brand_name', 'like', '%' . trim($request->search) . '%');
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $brands = $query->orderBy('brand_name')->paginate(10)->withQueryString();

        return view('admin.brands.index', [
            'brands'         => $brands,
            'totalBrands'    => Brand::count(),
            'activeBrands'   => Brand::where('is_active', true)->count(),
            'inactiveBrands' => Brand::where('is_active', false)->count(),
        ]);
    }

    public function store(StoreBrandRequest $request)
    {
        $this->abortIfDuplicateName(
            Brand::class,
            'brand_name',
            $request->brand_name
        );

        Brand::create([
            'brand_name' => $request->brand_name,
            'is_active'  => true,
        ]);

        return redirect()
            ->route('admin.brands.index')
            ->with('success', 'Brand created successfully!');
    }

    public function edit(Brand $brand)
    {
        return view('admin.brands.edit', compact('brand'));
    }

    public function update(UpdateBrandRequest $request, Brand $brand)
    {
        $this->abortIfDuplicateName(
            Brand::class,
            'brand_name',
            $request->brand_name,
            $brand->brand_id,
            'brand_id'
        );

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

        $brand->update(['is_active' => false]);

        return redirect()
            ->route('admin.brands.index')
            ->with('success', 'Brand deactivated successfully.');
    }

    public function restore($brand_id)
    {
        Brand::findOrFail($brand_id)->update(['is_active' => true]);

        return redirect()
            ->route('admin.brands.index')
            ->with('success', 'Brand activated successfully.');
    }
}