<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;    
use App\Models\Category;

class CategoryController extends Controller
    {
        public function index()
        {
            $categories = Category::paginate(5);
;  
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

public function edit(Category $category)
{
    return view('admin.categories.edit', compact('category'));
}

public function update(Request $request, Category $category)
{
    $request->validate([
        'category_name' => 'required|string|max:255',
        'category_description' => 'nullable|string',
    ]);

    $category->update([
        'category_name' => $request->category_name,
        'category_description' => $request->category_description,
    ]);

    return redirect()
        ->route('admin.categories.index')
        ->with('success', 'Category updated successfully!');
}

public function destroy(Category $category)
{
    $category->delete();
    return redirect()->route('admin.categories.index');
}
}
