<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::query();

        // Search
        if ($request->filled('search')) {
            $query->where('category_name', 'LIKE', '%' . trim($request->search) . '%');
        }

        // Status Filter
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $categories = $query
            ->orderBy('category_name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.categories.index', [
            'categories' => $categories,
            'totalCategories' => Category::count(),
            'activeCategories' => Category::where('is_active', true)->count(),
            'inactiveCategories' => Category::where('is_active', false)->count(),
        ]);
    }

    public function store(Request $request)
    {
        // Normalize
        $request->merge([
            'category_name' => ucwords(strtolower(trim($request->category_name))),
            'category_description' => trim($request->category_description),
        ]);

        // Validate
        $request->validate([
            'category_name' => 'required|string|max:255',
            'category_description' => 'nullable|string|max:255',
        ]);

        // Duplicate check
        $exists = Category::whereRaw('LOWER(category_name)=?', [
            strtolower($request->category_name)
        ])->exists();

        if ($exists) {
            return back()
                ->withErrors([
                    'category_name' => 'This category already exists.'
                ])
                ->withInput();
        }

        Category::create([
            'category_name' => $request->category_name,
            'category_description' => $request->category_description,
            'is_active' => true,
        ]);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category created successfully!');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $request->merge([
            'category_name' => ucwords(strtolower(trim($request->category_name))),
            'category_description' => trim($request->category_description),
        ]);

        $request->validate([
            'category_name' => 'required|string|max:255',
            'category_description' => 'nullable|string|max:255',
        ]);

        $exists = Category::whereRaw('LOWER(category_name)=?', [
            strtolower($request->category_name)
        ])
        ->where('category_id', '!=', $category->category_id)
        ->exists();

        if ($exists) {
            return back()
                ->withErrors([
                    'category_name' => 'This category already exists.'
                ])
                ->withInput();
        }

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
        if ($category->products()->exists()) {
            return back()->with(
                'error',
                'This category is being used by one or more products and cannot be deactivated.'
            );
        }

        $category->update([
            'is_active' => false,
        ]);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category deactivated successfully.');
    }

    public function restore($category_id)
    {
        $category = Category::findOrFail($category_id);

        $category->update([
            'is_active' => true,
        ]);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category activated successfully.');
    }
}