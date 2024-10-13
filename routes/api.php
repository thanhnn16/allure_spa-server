<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\TreatmentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ImportController;


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

        // Add these routes inside the authenticated group
        Route::get('/users/search', [UserController::class, 'searchUsers']);
        Route::get('/users/get-staff-list', [UserController::class, 'getStaffList']);
        Route::get('/users/{userId}/treatment-packages', [UserController::class, 'getUserTreatmentPackages']);

        Route::get('/user-treatment-packages/{user}', [UserController::class, 'getUserTreatmentPackages']);

        Route::post('/import', [ImportController::class, 'importAll']);

        Route::get('/dashboard', [DashboardController::class, 'index']);
    });
});
