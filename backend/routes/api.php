<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\Api\CalendarController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::put('/profile/password', [ProfileController::class, 'changePassword']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/calendar/week', [CalendarController::class, 'week']);
    Route::get('/calendar/day', [CalendarController::class, 'day']);
});

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {});
