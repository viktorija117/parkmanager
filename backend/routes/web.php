<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordController;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');

Route::post('/forgot-password', [PasswordController::class, 'forgot'])->name('password.email');
Route::post('/reset-password', [PasswordController::class, 'reset'])->name('password.update');
