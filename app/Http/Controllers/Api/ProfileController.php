<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'user' => AuthController::profile($user),
            'permissions' => [
                // Mirrors the website: the Super Admin governs, it does not operate.
                'manage_orders' => false,
                'manage_catalog' => false,
                'run_pos' => false,
                'review_approvals' => true,
                'manage_accounts' => true,
                'invite_admins' => true,
                'view_logs' => true,
            ],
        ]);
    }
}