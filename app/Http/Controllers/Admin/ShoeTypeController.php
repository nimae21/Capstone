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
            ->paginate(10);

        return view('admin.shoe-types.index', [
            'shoeTypes'      => $shoeTypes,
            'totalTypes'     => ShoeType::count(),
            'activeTypes'    => ShoeType::where('is_active', true)->count(),
            'inactiveTypes'  => ShoeType::where('is_active', false)->count(),
        ]);
    }

    public function store(Request $request)
    {
        $request->merge([
            'shoe_type_name' => ucwords(strtolower(trim($request->shoe_type_name))),
            'description'    => trim($request->description),
        ]);

        $request->validate([
            'shoe_type_name' => 'required|string|max:255',
            'description'    => 'nullable|string',
        ]);

        $exists = ShoeType::whereRaw(
            'LOWER(shoe_type_name) = ?',
            [strtolower($request->shoe_type_name)]
        )->exists();

        if ($exists) {
            return back()
                ->withErrors([
                    'shoe_type_name' => 'This shoe type already exists.'
                ])
                ->withInput();
        }

        ShoeType::create([
            'shoe_type_name' => $request->shoe_type_name,
            'description'    => $request->description,
            'display_order'  => 0,
            'is_active'      => true,
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
        $request->merge([
            'shoe_type_name' => ucwords(strtolower(trim($request->shoe_type_name))),
            'description'    => trim($request->description),
        ]);

        $request->validate([
            'shoe_type_name' => 'required|string|max:255',
            'description'    => 'nullable|string',
            'display_order'  => 'required|integer|min:0',
            'is_active'      => 'required|boolean',
        ]);

        $exists = ShoeType::whereRaw(
            'LOWER(shoe_type_name) = ?',
            [strtolower($request->shoe_type_name)]
        )
        ->where('shoe_type_id', '!=', $shoeType->shoe_type_id)
        ->exists();

        if ($exists) {
            return back()
                ->withErrors([
                    'shoe_type_name' => 'This shoe type already exists.'
                ])
                ->withInput();
        }

        $shoeType->update([
            'shoe_type_name' => $request->shoe_type_name,
            'description'    => $request->description,
            'display_order'  => $request->display_order,
            'is_active'      => $request->is_active,
        ]);

        return redirect()
            ->route('admin.shoe-types.index')
            ->with('success', 'Shoe type updated successfully.');
    }

    public function destroy(ShoeType $shoeType)
    {
        $shoeType->update([
            'is_active' => false,
        ]);

        return redirect()
            ->route('admin.shoe-types.index')
            ->with('success', 'Shoe type deactivated successfully.');
    }

    public function restore($shoe_type_id)
    {
        $shoeType = ShoeType::findOrFail($shoe_type_id);

        $shoeType->update([
            'is_active' => true,
        ]);

        return redirect()
            ->route('admin.shoe-types.index')
            ->with('success', 'Shoe type activated successfully.');
    }
}