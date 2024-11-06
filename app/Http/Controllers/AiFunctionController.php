<?php

namespace App\Http\Controllers;

use App\Services\AiFunctionCallingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AiFunctionController extends Controller
{
    protected $functionCallingService;

    public function __construct(AiFunctionCallingService $functionCallingService)
    {
        $this->functionCallingService = $functionCallingService;
    }

    public function handleFunctionCall(Request $request)
    {
        try {
            $validated = $request->validate([
                'function' => 'required|string',
                'args' => 'required|array'
            ]);

            $result = $this->functionCallingService->handleFunctionCall(
                $validated['function'],
                $validated['args']
            );

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Function call error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 