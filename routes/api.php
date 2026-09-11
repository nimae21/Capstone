<?php

use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\ApprovalController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\PushDeviceController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Super Admin mobile API
|--------------------------------------------------------------------------
| The Ionic app is the Super Admin console. Every route below is guarded by
| Sanctum authentication, the active-account check and an explicit
| super_admin role check, so hiding a screen in the app is never the only
| thing protecting an endpoint.
*/

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:api-login');

Route::middleware(['auth:sanctum', 'active', 'super_admin', 'throttle:authenticated_api'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [ProfileController::class, 'show']);

    // Phone notifications (bound to the authenticated mobile token).
    Route::get('/push/status', [PushDeviceController::class, 'status']);
    Route::put('/push/device', [PushDeviceController::class, 'store'])->middleware('throttle:30,1');
    Route::delete('/push/device', [PushDeviceController::class, 'destroy']);
    Route::post('/push/test', [PushDeviceController::class, 'test'])->middleware('throttle:3,1');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Orders - read only, matching the website's Super Admin permissions.
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/counts', [OrderController::class, 'counts']);
    Route::get('/orders/{order}', [OrderController::class, 'show'])->whereNumber('order');

    // Approval queue
    Route::get('/approvals', [ApprovalController::class, 'index']);
    Route::get('/approvals/counts', [ApprovalController::class, 'counts']);
    Route::get('/approvals/{approval}', [ApprovalController::class, 'show'])->whereNumber('approval');
    Route::post('/approvals/review', [ApprovalController::class, 'review'])->middleware('throttle:30,1');

    // Users and Admins
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/counts', [UserController::class, 'counts']);
    Route::get('/users/{user}', [UserController::class, 'show'])->whereNumber('user');
    Route::patch('/users/{user}/status', [UserController::class, 'status'])->middleware('throttle:30,1');

    Route::get('/admins/{admin}', [AdminController::class, 'show'])->whereNumber('admin');
    Route::get('/admin-invitations', [AdminController::class, 'invitations']);
    Route::post('/admin-invitations', [AdminController::class, 'invite'])->middleware('throttle:10,1');

    // Inventory overview (read only)
    Route::get('/inventory', [InventoryController::class, 'index']);

    // Audit trail (read only)
    Route::get('/activity-logs', [ActivityLogController::class, 'index']);
    Route::get('/activity-logs/filters', [ActivityLogController::class, 'filters']);
    Route::get('/activity-logs/{log}', [ActivityLogController::class, 'show'])->whereNumber('log');

    // In-app notification centre
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead']);
});