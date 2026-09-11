<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AccountEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * The mobile application is the Super Admin console. Operational Admins
     * and Customers authenticate on the website only, so a valid password for
     * one of those accounts must never mint a mobile token.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', AccountEmail::storedAddress((string) $request->email))->first();

        // Unknown, suspended and wrong-password all answer identically, so the
        // endpoint never confirms that an account exists or that it is disabled.
        if (! $user || ! $user->is_active || ! Hash::check((string) $request->password, (string) $user->password)) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        if (! $user->isSuperAdmin()) {
            return response()->json(['message' => 'This app is reserved for Super Admin accounts.'], 403);
        }

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => self::profile($user),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out.']);
    }

    public static function profile(User $user): array
    {
        return [
            'name' => $user->full_name,
            'email' => $user->email,
            'role' => $user->role,
            'role_label' => 'Super Admin',
            'is_active' => (bool) $user->is_active,
            'initials' => $user->initials,
        ];
    }
}