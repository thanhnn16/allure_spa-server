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

    protected function respondWithError($message, $statusCode = 400)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'status_code' => $statusCode,
            'data' => null
        ], $statusCode);
    }
}
