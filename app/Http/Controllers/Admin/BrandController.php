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
    Brand::create([
        'brand_name' => $request->brand_name
    ]);

    return redirect()->route('admin.brands.index');
}


public function destroy(Brand $brand)
{
    $brand->delete();
    return redirect()->route('admin.brands.index');
}
}
