<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use App\Traits\HasCaseInsensitiveUniqueName;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use HasCaseInsensitiveUniqueName;

    public function index(Request $request)
    {
        $query = Category::query();

        if ($request->filled('search')) {
            $query->where('category_name', 'LIKE', '%' . trim($request->search) . '%');
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $categories = $query->orderBy('category_name')->paginate(10)->withQueryString();

        return view('admin.categories.index', [
            'categories'         => $categories,
            'totalCategories'    => Category::count(),
            'activeCategories'   => Category::where('is_active', true)->count(),
            'inactiveCategories' => Category::where('is_active', false)->count(),
        ]);
    }

    public function store(StoreCategoryRequest $request)
    {
        $this->abortIfDuplicateName(
            Category::class,
            'category_name',
            $request->category_name
        );

        Category::create([
            'category_name'        => $request->category_name,
            'category_description' => $request->category_description,
            'is_active'             => true,
        ]);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $this->abortIfDuplicateName(
            Category::class,
            'category_name',
            $request->category_name,
            $category->category_id,
            'category_id'
        );

        $category->update([
            'category_name'        => $request->category_name,
            'category_description' => $request->category_description,
        ]);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            return back()->with(
                'error',
                'This category is being used by one or more products and cannot be deactivated.'
            );
        }

        $category->update(['is_active' => false]);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category deactivated successfully.');
    }

    public function restore($category_id)
    {
        Category::findOrFail($category_id)->update(['is_active' => true]);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category activated successfully.');
    }
}