<?php

use App\Http\Controllers\Api\Admin\ParkingSpaceController;
use App\Http\Controllers\Api\Admin\ReservationController as AdminReservationController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\CalendarController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);

    Route::put('/profile', [ProfileController::class, 'update']);
    Route::put('/profile/password', [ProfileController::class, 'changePassword']);

    Route::get('/calendar/week', [CalendarController::class, 'week']);
    Route::get('/calendar/day', [CalendarController::class, 'day']);

    Route::apiResource('reservations', ReservationController::class)->except(['update']);

    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::apiResource('spaces', ParkingSpaceController::class)->except(['show']);
        Route::apiResource('users', UserController::class)->except(['show']);
        Route::get('reservations', [AdminReservationController::class, 'index']);
        Route::post('reservations/{reservation}/cancel', [AdminReservationController::class, 'adminCancel']);
    });
});
