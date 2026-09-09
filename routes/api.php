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

    // Orders
    Route::get('/orders/pending', [OrderController::class, 'pending']);
    Route::post('/orders/{order}/confirm', [OrderController::class, 'confirm']);

    // Analytics
    Route::get('/analytics', [AnalyticsController::class, 'index']);

    // Activity logs
    Route::get('/activity-logs', [ActivityLogController::class, 'index']);
});