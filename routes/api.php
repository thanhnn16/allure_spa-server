<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\InvoiceController;

Route::middleware('throttle:api')->group(function () {
    // Test route '/' return 'Hello World'
    Route::get('/hello-world', function () {
        return 'Hello World';
    });

    // Auth routes
    Route::post('/auth/register', [RegisteredUserController::class, 'storeApi']);
    Route::post('/auth/login', [AuthenticatedSessionController::class, 'storeApi']);

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/appointments', [AppointmentController::class, 'index']);
        Route::post('/appointments', [AppointmentController::class, 'store']);
        Route::put('/appointments/{appointment}', [AppointmentController::class, 'update']);
        Route::delete('/appointments/{appointment}', [AppointmentController::class, 'destroy']);

        // Thêm route mới cho tìm kiếm người dùng
        Route::get('/users/search', [UserController::class, 'searchUsers']);
        Route::get('/products/search', [InvoiceController::class, 'searchProducts']);
        Route::get('/treatments/search', [InvoiceController::class, 'searchTreatments']);
    });
});
