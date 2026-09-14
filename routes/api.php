<?php

use App\Http\Controllers\Api\NotificationController;
use Illuminate\Support\Facades\Route;

// API notifications route (supports both session and token auth)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index']);
});

// Fallback: web-based notifications endpoint for session auth
Route::middleware(['auth'])->group(function () {
    Route::get('/notifications-web', [NotificationController::class, 'index']);
});
