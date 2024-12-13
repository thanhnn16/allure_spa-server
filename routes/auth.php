<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['guest', 'web'])->group(function () {
    Route::get('login', function () {
        return Inertia::render('Auth/LoginView');
    })->name('login');

    Route::post('login', [AuthController::class, 'login'])
        ->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])
        ->name('logout');
});
