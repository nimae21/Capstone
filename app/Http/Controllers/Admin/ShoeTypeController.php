<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreShoeTypeRequest;
use App\Http\Requests\UpdateShoeTypeRequest;
use App\Models\ShoeType;
use App\Traits\HasCaseInsensitiveUniqueName;

class ShoeTypeController extends Controller
{
    use HasCaseInsensitiveUniqueName;

    public function index()
    {
        $shoeTypes = ShoeType::orderBy('display_order')->paginate(10);

        return view('admin.shoe-types.index', [
            'shoeTypes'     => $shoeTypes,
            'totalTypes'    => ShoeType::count(),
            'activeTypes'   => ShoeType::where('is_active', true)->count(),
            'inactiveTypes' => ShoeType::where('is_active', false)->count(),
        ]);
    }

    public function store(StoreShoeTypeRequest $request)
{
    $this->abortIfDuplicateName(
        ShoeType::class,
        'shoe_type_name',
        $request->shoe_type_name
    );

    $shoeType = ShoeType::create([
        'shoe_type_name' => $request->shoe_type_name,
        'description'    => $request->description,
        'display_order'  => 0,
        'is_active'      => true,
    ]);

    if ($request->wantsJson()) {
        return response()->json([
            'id'   => $shoeType->shoe_type_id,
            'name' => $shoeType->shoe_type_name,
        ]);
    }

    return redirect()
        ->route('admin.shoe-types.index')
        ->with('success', 'Shoe type created successfully.');
}

    public function edit(ShoeType $shoeType)
    {
        return view('admin.shoe-types.edit', compact('shoeType'));
    }

    public function update(UpdateShoeTypeRequest $request, ShoeType $shoeType)
    {
        $this->abortIfDuplicateName(
            ShoeType::class,
            'shoe_type_name',
            $request->shoe_type_name,
            $shoeType->shoe_type_id,
            'shoe_type_id'
        );

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
        $shoeType->update(['is_active' => false]);

        return redirect()
            ->route('admin.shoe-types.index')
            ->with('success', 'Shoe type deactivated successfully.');
    }

    public function restore($shoe_type_id)
    {
        ShoeType::findOrFail($shoe_type_id)->update(['is_active' => true]);

        return redirect()
            ->route('admin.shoe-types.index')
            ->with('success', 'Shoe type activated successfully.');
    }
}