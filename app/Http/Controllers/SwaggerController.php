<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Allure SPA API",
 *     description="API for managing Allure SPA"
 * )
 * 
 * @OA\Server(
 *     description="Local API server",
 *     url="http://localhost:8000/api"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class SwaggerController extends Controller
{
    // You can leave this empty or add methods related to API documentation if needed
}

