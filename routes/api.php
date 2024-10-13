<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\TreatmentController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ImportController;
use App\Http\Controllers\Api\DashboardController;


Route::middleware('throttle:api')->group(function () {
    // Test route '/' return 'Hello World'
    Route::get('/hello-world', function () {
        return 'Hello World';
    });

    // Auth routes
    Route::post('/auth/register', [RegisteredUserController::class, 'storeApi']);
    Route::post('/auth/login', [AuthenticatedSessionController::class, 'storeApi']);

    Route::get('/products/search', [ProductController::class, 'searchProducts']);
    Route::get('/treatments/search', [TreatmentController::class, 'searchTreatments']);

    // Treatment routes
    Route::get('/treatments', [TreatmentController::class, 'index']);
    Route::get('/treatment-categories', [TreatmentController::class, 'categories']);

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/appointments', [AppointmentController::class, 'index']);
        Route::post('/appointments', [AppointmentController::class, 'store']);
        Route::put('/appointments/{appointment}', [AppointmentController::class, 'update']);
        Route::delete('/appointments/{appointment}', [AppointmentController::class, 'destroy']);

        // Thêm route mới cho tìm kiếm người dùng
        Route::get('/users/search', [UserController::class, 'searchUsers']);

        Route::get('/users/get-staff-list', [UserController::class, 'getStaffList']);
        Route::get('/user-treatment-packages/{user}', [UserController::class, 'getUserTreatmentPackages']);
    });

    Route::post('/import', [ImportController::class, 'importAll']);

    Route::get('/dashboard', [DashboardController::class, 'index']);
});
