<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AdminUserController extends Controller
{
    // List all users
    public function index()
    {
       $totalUsers = User::count();
$totalAdmins = User::where('role', 'admin')->count();
$totalRegularUsers = User::where('role', 'user')->count();

$search = request('search');

$users = User::query()
    ->when($search, function ($query) use ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('role', 'like', "%{$search}%");
        });
    })
    ->latest()
    ->paginate(10)
    ->withQueryString();

return view('admin.users.index', compact(
    'users',
    'totalUsers',
    'totalAdmins',
    'totalRegularUsers'
));
    }

    // Show create admin form
    public function createAdmin()
    {
        return view('admin.users.create-admin');
    }

    // Store new admin
    public function storeAdmin(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'suffix' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'password' => ['required', Password::min(8)->letters()->numbers()->symbols()],
        ]);

        try {
            $user = User::create([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'middle_name' => $validated['middle_name'],
                'suffix' => $validated['suffix'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'admin',
                'email_verified_at' => now(), // Auto-verify admin accounts
            ]);

            return redirect()
                ->route('admin.users.index')
                ->with('success', "Admin account '{$user->first_name} {$user->last_name}' created successfully!");
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to create admin: ' . $e->getMessage());
        }
    }

    // Show edit user form
    public function edit(User $user)
    {
         if ($user->id === auth()->id()) {
        return redirect()
            ->route('admin.users.index')
            ->with('error', 'You cannot edit your own account from the Users Management page.');
    }
        return view('admin.users.edit', compact('user'));
    }

    // Update user
    public function update(Request $request, User $user)

    {
         if ($user->id === auth()->id()) {
        return redirect()
            ->route('admin.users.index')
            ->with('error', 'You cannot edit your own account from the Users Management page.');
    }
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'suffix' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id . ',id|max:255',
            'role' => 'required|in:user,admin',
        ]);

        try {
            $user->update($validated);

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'User updated successfully!');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to update user: ' . $e->getMessage());
        }
    }

    public function toggleStatus(User $user)
{
    if (
        $user->role === 'admin' &&
        $user->is_active &&
        User::where('role', 'admin')
            ->where('is_active', true)
            ->count() <= 1
    ) {
        return back()->with(
            'error',
            'You cannot suspend the last active administrator.'
        );
    }

    $user->is_active = ! $user->is_active;
    $user->save();

    return back()->with(
        'success',
        $user->is_active
            ? 'User activated successfully.'
            : 'User suspended successfully.'
    );
}
}
