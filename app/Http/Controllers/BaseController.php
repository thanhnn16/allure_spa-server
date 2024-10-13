<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class BaseController extends Controller
{
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
}
