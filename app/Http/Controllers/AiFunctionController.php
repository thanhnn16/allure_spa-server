<?php

namespace App\Http\Controllers;

use App\Services\AiFunctionCallingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\AiChatConfig;
use Illuminate\Validation\Rule;

class AiFunctionController extends Controller
{
    protected $functionCallingService;

    public function __construct(AiFunctionCallingService $functionCallingService)
    {
        $this->functionCallingService = $functionCallingService;
    }

    /**
     * @OA\Post(
     *     path="/api/ai/function-call",
     *     summary="Handle AI function calls",
     *     tags={"AI Functions"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"function", "args"},
     *             @OA\Property(property="function", type="string", description="Function name to call"),
     *             @OA\Property(property="args", type="object", description="Function arguments")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function handleFunctionCall(Request $request)
    {
        try {
            // Get available functions from config
            $availableFunctions = collect(AiChatConfig::getFunctionDeclarations())
                ->pluck('name')
                ->toArray();

            $validated = $request->validate([
                'function' => ['required', 'string', Rule::in($availableFunctions)],
                'args' => 'array' // Bỏ required
            ]);

            // Đảm bảo args luôn là array
            $args = $validated['args'] ?? [];

            // Validate function-specific arguments
            $this->validateFunctionArgs($validated['function'], $args);

            // Handle function call
            $result = $this->functionCallingService->handleFunctionCall(
                $validated['function'],
                $validated['args']
            );

            return response()->json($result);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Function call validation error: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'error' => 'Validation error',
                'details' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Function call error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Internal server error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate function-specific arguments
     */
    protected function validateFunctionArgs($function, $args)
    {
        $functionDeclaration = collect(AiChatConfig::getFunctionDeclarations())
            ->firstWhere('name', $function);

        if (!$functionDeclaration) {
            throw new \InvalidArgumentException("Unknown function: {$function}");
        }

        $parameters = $functionDeclaration['parameters'];

        // Check required parameters
        if (isset($parameters['required'])) {
            foreach ($parameters['required'] as $required) {
                if (!isset($args[$required])) {
                    throw new \Illuminate\Validation\ValidationException(validator([], []), [
                        $required => ["The {$required} parameter is required"]
                    ]);
                }
            }
        }

        // Validate parameter types and enums
        if (isset($parameters['properties'])) {
            foreach ($parameters['properties'] as $param => $schema) {
                if (isset($args[$param])) {
                    // Validate enum values
                    if (isset($schema['enum']) && !in_array($args[$param], $schema['enum'])) {
                        throw new \Illuminate\Validation\ValidationException(validator([], []), [
                            $param => ["Invalid value for {$param}. Must be one of: " . implode(', ', $schema['enum'])]
                        ]);
                    }

                    // Validate types
                    switch ($schema['type']) {
                        case 'string':
                            if (!is_string($args[$param])) {
                                throw new \Illuminate\Validation\ValidationException(validator([], []), [
                                    $param => ["The {$param} must be a string"]
                                ]);
                            }
                            break;
                        case 'number':
                            if (!is_numeric($args[$param])) {
                                throw new \Illuminate\Validation\ValidationException(validator([], []), [
                                    $param => ["The {$param} must be a number"]
                                ]);
                            }
                            break;
                        case 'integer':
                            if (!is_int($args[$param])) {
                                throw new \Illuminate\Validation\ValidationException(validator([], []), [
                                    $param => ["The {$param} must be an integer"]
                                ]);
                            }
                            break;
                        case 'boolean':
                            if (!is_bool($args[$param])) {
                                throw new \Illuminate\Validation\ValidationException(validator([], []), [
                                    $param => ["The {$param} must be a boolean"]
                                ]);
                            }
                            break;
                        case 'array':
                            if (!is_array($args[$param])) {
                                throw new \Illuminate\Validation\ValidationException(validator([], []), [
                                    $param => ["The {$param} must be an array"]
                                ]);
                            }
                            break;
                    }
                }
            }
        }
    }

    /**
     * @OA\Get(
     *     path="/api/ai/available-functions",
     *     summary="Get list of available AI functions",
     *     tags={"AI Functions"},
     *     @OA\Response(
     *         response=200,
     *         description="List of available functions",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     */
    public function getAvailableFunctions()
    {
        try {
            $functions = collect(AiChatConfig::getFunctionDeclarations())
                ->map(function ($function) {
                    return [
                        'name' => $function['name'],
                        'description' => $function['description'],
                        'parameters' => $function['parameters']
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $functions
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting available functions: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Internal server error'
            ], 500);
        }
    }
}
