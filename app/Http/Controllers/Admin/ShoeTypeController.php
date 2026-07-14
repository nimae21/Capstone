<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShoeType;
use Illuminate\Http\Request;

class ShoeTypeController extends Controller
{
    public function index()
    {
        $shoeTypes = ShoeType::orderBy('display_order')
            ->paginate(5);

        return view('admin.shoe-types.index', compact('shoeTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'shoe_type_name' => 'required|string|max:255|unique:shoe_types,shoe_type_name',
            'description' => 'nullable|string',
        ]);

        ShoeType::create([
            'shoe_type_name' => $request->shoe_type_name,
            'description' => $request->description,
            'display_order' => 0,
            'is_active' => true,
        ]);

        return redirect()
            ->route('admin.shoe-types.index')
            ->with('success', 'Shoe type created successfully.');
    }

    public function edit(ShoeType $shoeType)
    {
        return view('admin.shoe-types.edit', compact('shoeType'));
    }

    public function update(Request $request, ShoeType $shoeType)
    {
        $request->validate([
            'shoe_type_name' => 'required|string|max:255|unique:shoe_types,shoe_type_name,' . $shoeType->shoe_type_id . ',shoe_type_id',
            'description' => 'nullable|string',
            'display_order' => 'required|integer|min:0',
            'is_active' => 'required|boolean',
        ]);

        $shoeType->update($request->only([
            'shoe_type_name',
            'description',
            'display_order',
            'is_active',
        ]));

        return redirect()
            ->route('admin.shoe-types.index')
            ->with('success', 'Shoe type updated successfully.');
    }

    public function destroy(ShoeType $shoeType)
    {
        $shoeType->delete();

        return redirect()
            ->route('admin.shoe-types.index')
            ->with('success', 'Shoe type deleted successfully.');
    }
}