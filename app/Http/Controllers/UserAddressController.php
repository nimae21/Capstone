<?php

namespace App\Http\Controllers;

use App\Models\UserAddress;
use Illuminate\Http\Request;

class UserAddressController extends Controller
{
    public function index()
    {
        $addresses = auth()->user()->addresses()->paginate(10);
        return view('addresses.index', compact('addresses'));
    }

    public function create()
    {
        return view('addresses.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'street' => 'required|string|max:255',
            'barangay' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'province' => 'required|string|max:255',
            'postal_code' => 'required|string|max:10',
            'is_default' => 'nullable|boolean',
        ]);

        try {
            // If setting as default, unset other defaults
            if ($validated['is_default']) {
                auth()->user()->addresses()->update(['is_default' => false]);
            }

            auth()->user()->addresses()->create($validated);

            return redirect()
                ->route('addresses.index')
                ->with('success', 'Address added successfully!');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to add address: ' . $e->getMessage());
        }
    }

    public function edit(UserAddress $address)
    {
        // Ensure user can only edit their own addresses
        if ($address->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        return view('addresses.edit', compact('address'));
    }

    public function update(Request $request, UserAddress $address)
    {
        // Ensure user can only update their own addresses
        if ($address->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'street' => 'required|string|max:255',
            'barangay' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'province' => 'required|string|max:255',
            'postal_code' => 'required|string|max:10',
            'is_default' => 'nullable|boolean',
        ]);

        try {
            // If setting as default, unset other defaults
            if ($validated['is_default']) {
                auth()->user()->addresses()->where('address_id', '!=', $address->address_id)->update(['is_default' => false]);
            }

            $address->update($validated);

            return redirect()
                ->route('addresses.index')
                ->with('success', 'Address updated successfully!');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to update address: ' . $e->getMessage());
        }
    }

    public function destroy(UserAddress $address)
    {
        // Ensure user can only delete their own addresses
        if ($address->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        try {
            $address->delete();

            return redirect()
                ->route('addresses.index')
                ->with('success', 'Address deleted successfully!');
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Failed to delete address: ' . $e->getMessage());
        }
    }

    public function setDefault(UserAddress $address)
    {
        // Ensure user can only set their own addresses as default
        if ($address->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        try {
            // Unset all other defaults
            auth()->user()->addresses()->update(['is_default' => false]);

            // Set this one as default
            $address->update(['is_default' => true]);

            return back()->with('success', 'Default address updated!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update default address: ' . $e->getMessage());
        }
    }
}
