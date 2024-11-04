<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;
use App\Enums\AuthErrorCode;

class BaseController extends Controller
{
    /**
     * Return JSON response
     */
    protected function respondWithJson($data, $message = '', $status = 200): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'status_code' => $status,
            'success' => $status >= 200 && $status < 300,
            'data' => $data
        ], $status);
    }

    protected function respondWithInertia($component, $props = []): Response
    {
        return Inertia::render($component, $props);
    }

    protected function respondWithError($errorCode, $status = 400): JsonResponse
    {
        $statusCodes = [
            AuthErrorCode::USER_NOT_FOUND->value => 404,
            AuthErrorCode::WRONG_PASSWORD->value => 401,
            AuthErrorCode::EMAIL_ALREADY_EXISTS->value => 422,
            AuthErrorCode::PHONE_ALREADY_EXISTS->value => 422,
            AuthErrorCode::VALIDATION_ERROR->value => 422,
            AuthErrorCode::MISSING_CONTACT_INFO->value => 422,
            AuthErrorCode::SERVER_ERROR->value => 500,
            AuthErrorCode::UNAUTHORIZED_ACCESS->value => 403
        ];

        $status = $statusCodes[$errorCode] ?? $status;

        return response()->json([
            'status_code' => $errorCode,
            'message' => $status,
            'success' => false,
            'data' => null
        ], $status);
    }
}
