<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Test route '/' return 'Hello World'
Route::get('/api/hello-world', function () {
    return 'Hello World';
});


// Auth routes
Route::post('/auth/register', [RegisteredUserController::class, 'storeApi']);
Route::post('/auth/login', [AuthenticatedSessionController::class, 'storeApi']);
