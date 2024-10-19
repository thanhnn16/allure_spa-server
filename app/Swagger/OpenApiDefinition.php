<?php

namespace App\Swagger;

/**
 * @OA\OpenApi(
 *     @OA\Info(
 *         version="1.0.0",
 *         title="Allure SPA API",
 *         description="API for managing Allure SPA"
 *     ),
 *     @OA\Server(
 *         description="Production API server",
 *         url="https://allurespa.io.vn"
 *     ),
 *     @OA\Server(
 *         description="Local API server",
 *         url="http://localhost:8000"
 *     )
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class OpenApiDefinition
{
}
