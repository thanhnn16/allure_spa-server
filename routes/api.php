<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiUserController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\TreatmentController;
use App\Http\Controllers\Api\UserTreatmentPackageController;

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
        Route::get('/products/search', [ProductController::class, 'searchProducts']);
        Route::get('/treatments/search', [TreatmentController::class, 'searchTreatments']);

        Route::get('/user-treatment-packages/{user}', [UserTreatmentPackageController::class, 'index']);


        Route::get('/treatments', [TreatmentController::class, 'index']);

        Route::get('/users/get-staff-list', [UserController::class, 'getStaffList']);
    });
});
