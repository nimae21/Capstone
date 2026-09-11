<?php

use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PushDeviceController;
use Illuminate\Support\Facades\Route;

// Force JSON responses for all API errors (prevents HTML 403/404 pages)
Route::middleware(['api'])->group(function () {});

// Public
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:api-login');

// Protected — admin only
Route::middleware(['auth:sanctum', 'active', 'admin'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Phone notifications (bound to the authenticated mobile token).
    Route::get('/push/status', [PushDeviceController::class, 'status']);
    Route::put('/push/device', [PushDeviceController::class, 'store'])->middleware('throttle:30,1');
    Route::delete('/push/device', [PushDeviceController::class, 'destroy']);
    Route::post('/push/test', [PushDeviceController::class, 'test'])->middleware('throttle:3,1');

    // Orders
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{order}', [OrderController::class, 'show'])->whereNumber('order');
    Route::put('/orders/{order}/status', [OrderController::class, 'updateStatus'])->whereNumber('order');
    Route::get('/orders/pending', [OrderController::class, 'pending']);
    Route::post('/orders/{order}/confirm', [OrderController::class, 'confirm']);

    // Analytics
    Route::get('/analytics', [AnalyticsController::class, 'index']);

    // Activity logs
    Route::get('/activity-logs', [ActivityLogController::class, 'index']);
});
