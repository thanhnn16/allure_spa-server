<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\InvoiceController;


Route::middleware('throttle:api')->group(function () {
    // Test route '/' return 'Hello World'
    Route::get('/hello-world', function () {
        return 'Hello World';
    });

    // Auth routes
    Route::post('/auth/register', [RegisteredUserController::class, 'storeApi']);
    Route::post('/auth/login', [AuthenticatedSessionController::class, 'storeApi']);

    Route::get('/products/search', [ProductController::class, 'searchProducts']);
    Route::get('/services/search', [ServiceController::class, 'searchServices']);

    // Service routes
    Route::get('/services', [ServiceController::class, 'index']);
    Route::get('/service-categories', [ServiceController::class, 'categories']);

    Route::middleware(['auth:sanctum'])->group(function () {
        // Appointment routes
        Route::get('/appointments', [AppointmentController::class, 'index']);
        Route::post('/appointments', [AppointmentController::class, 'store']);
        Route::get('/appointments/{appointment}', [AppointmentController::class, 'show']);
        Route::put('/appointments/{appointment}', [AppointmentController::class, 'update']);
        Route::delete('/appointments/{appointment}', [AppointmentController::class, 'destroy']);

        // Thêm các route mới cho cập nhật và hủy cuộc hẹn
        Route::put('/appointments/{appointment}/update', [AppointmentController::class, 'update']);
        Route::put('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel']);

        // Add these routes inside the authenticated group
        Route::get('/users/search', [UserController::class, 'searchUsers']);
        Route::get('/users/get-staff-list', [UserController::class, 'getStaffList']);
        Route::get('/users/{userId}/service-packages', [UserController::class, 'getUserServicePackages']);

        Route::get('/user-service-packages/{user}', [UserController::class, 'getUserServicePackages']);

        Route::post('/import', [ImportController::class, 'importAll']);

        Route::get('/dashboard', [DashboardController::class, 'index']);

        Route::get('/user/info', [UserController::class, 'getUserInfo']);

        // Rating routes
        Route::get('/ratings', [RatingController::class, 'index']);
        Route::get('/products/{productId}/ratings', [RatingController::class, 'getProductRatings']);
        Route::get('/services/{serviceId}/ratings', [RatingController::class, 'getServiceRatings']);
        Route::post('/ratings', [RatingController::class, 'store']);

        // Invoice routes
        Route::resource('/invoices', InvoiceController::class);
    });
});

