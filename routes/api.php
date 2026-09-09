<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\ActivityLogController;

// Force JSON responses for all API errors (prevents HTML 403/404 pages)
Route::middleware(['api'])->group(function () {});

// Public
Route::post('/login', [AuthController::class, 'login']);

// Protected — admin only
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Phone notifications (bound to the authenticated mobile token).
    Route::get('/push/status', [\App\Http\Controllers\Api\PushDeviceController::class, 'status']);
    Route::put('/push/device', [\App\Http\Controllers\Api\PushDeviceController::class, 'store'])->middleware('throttle:30,1');
    Route::delete('/push/device', [\App\Http\Controllers\Api\PushDeviceController::class, 'destroy']);
    Route::post('/push/test', [\App\Http\Controllers\Api\PushDeviceController::class, 'test'])->middleware('throttle:3,1');

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