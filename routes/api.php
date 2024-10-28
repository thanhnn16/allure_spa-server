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
use App\Http\Controllers\PayOSController;


Route::middleware('throttle:api')->group(function () {
    // Test route '/' return 'Hello World'
    Route::get('/hello-world', function () {
        return 'Hello World';
    });

    // Auth routes
    Route::post('/auth/register', [RegisteredUserController::class, 'storeApi']);
    Route::post('/auth/login', [AuthenticatedSessionController::class, 'storeApi']);

    // Search routes
    Route::get('/products/search', [ProductController::class, 'searchProducts']);
    Route::get('/services/search', [ServiceController::class, 'searchServices']);

    // Service routes
    Route::get('/services', [ServiceController::class, 'index']);
    Route::get('/services/{service}', [ServiceController::class, 'show']);
    Route::get('/service-categories', [ServiceController::class, 'categories']);

    // Product routes
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);
    Route::get('/product-categories', [ProductController::class, 'categories']);

    Route::post('/payos/test', [PayOSController::class, 'testPayment']);
    Route::post('/payos/verify', [PayOSController::class, 'verifyPayment']);

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
        Route::get('/invoices', [InvoiceController::class, 'index']);
        Route::post('/invoices', [InvoiceController::class, 'store']);
        Route::get('/invoices/{invoice}', [InvoiceController::class, 'show']);
        Route::put('/invoices/{invoice}', [InvoiceController::class, 'update']);
        Route::delete('/invoices/{invoice}', [InvoiceController::class, 'destroy']);

        // Route::post('/payos/test', [PayOSController::class, 'testPayment']);
        // Route::post('/payos/verify', [PayOSController::class, 'verifyPayment']);

    });
});
