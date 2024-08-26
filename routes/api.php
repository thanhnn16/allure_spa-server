<?php

use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// Test route '/' return 'Hello World'
Route::get('/', function () {
    return 'Hello World';
});


// Auth routes
Route::post('/auth/register', [RegisteredUserController::class, 'storeApi']);
Route::post('/auth/login', [RegisteredUserController::class, 'storeApi']);
