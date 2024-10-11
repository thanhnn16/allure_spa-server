<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiUserController;
use App\Http\Controllers\Api\ApiAppointmentController;
use App\Http\Controllers\Api\ApiTreatmentController;
use App\Http\Controllers\Api\ApiUserTreatmentPackageController;
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
        Route::get('/appointments', [ApiAppointmentController::class, 'index']);
        Route::post('/appointments', [ApiAppointmentController::class, 'store']);
        Route::put('/appointments/{appointment}', [ApiAppointmentController::class, 'update']);
        Route::delete('/appointments/{appointment}', [ApiAppointmentController::class, 'destroy']);

        // Thêm route mới cho tìm kiếm người dùng
        Route::get('/users/search', [ApiUserController::class, 'searchUsers']);
        Route::get('/products/search', [InvoiceController::class, 'searchProducts']);
        Route::get('/treatments/search', [InvoiceController::class, 'searchTreatments']);

        Route::get('/user-treatment-packages/{user}', [ApiUserTreatmentPackageController::class, 'index']);


        Route::get('/treatments', [ApiTreatmentController::class, 'index']);

        Route::get('/users/get-staff-list', [ApiUserController::class, 'getStaffList']);

    });
});
