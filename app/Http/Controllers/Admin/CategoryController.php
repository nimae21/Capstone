<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;    
use App\Models\Category;

class CategoryController extends Controller
    {
        public function index()
        {
            $categories = Category::all();  
            return view('admin.categories.index',compact('categories'));
    }


    public function store(Request $request)
{
    Category::create([
        'category_name' => $request->category_name,
        'category_description' => $request->category_description,
    ]);

    return redirect()->route('admin.categories.index');
}


public function destroy(Category $category)
{
    $category->delete();
    return redirect()->route('admin.categories.index');
}
}
