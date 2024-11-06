<?php

namespace App\Http\Controllers;

use App\Models\AiChatConfig;
use App\Services\AiChatConfigService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class AiConfigController extends BaseController
{
    protected $aiConfigService;

    public function __construct(AiChatConfigService $aiConfigService)
    {
        $this->aiConfigService = $aiConfigService;
    }

    /**
     * @OA\Get(
     *     path="/api/ai-configs",
     *     summary="Lấy danh sách cấu hình AI",
     *     tags={"AI Configs"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            $configs = $this->aiConfigService->getAllConfigs();
            $data = [
                'configs' => $this->formatConfigsForResponse($configs),
                'configTypes' => AiChatConfig::TYPES,
                'modelTypes' => AiChatConfig::MODEL_TYPES,
                'languages' => AiChatConfig::LANGUAGES,
                'responseFormats' => AiChatConfig::RESPONSE_FORMATS,
                'defaultSafetySettings' => AiChatConfig::SAFETY_SETTINGS,
                'defaultFunctionDeclarations' => AiChatConfig::FUNCTION_DECLARATIONS,
                'defaultToolConfig' => AiChatConfig::DEFAULT_TOOL_CONFIG,
                // Thêm các template mẫu
                'configTemplates' => [
                    'system_prompt' => [
                        'ai_name' => '',
                        'type' => 'system_prompt',
                        'context' => 'Bạn là một trợ lý AI thông minh...',
                        'language' => 'vi',
                        'model_type' => 'gemini-1.5-pro',
                        'temperature' => 0.9,
                        'max_tokens' => 2048,
                        'top_p' => 1,
                        'top_k' => 40,
                        'priority' => 0,
                        'is_active' => true
                    ],
                    'vision_config' => [
                        'ai_name' => '',
                        'type' => 'vision_config',
                        'context' => 'Hãy phân tích chi tiết hình ảnh...',
                        'language' => 'vi',
                        'model_type' => 'gemini-vision-pro',
                        'temperature' => 0.7,
                        'max_tokens' => 1024,
                        'top_p' => 1,
                        'top_k' => 40,
                        'priority' => 0,
                        'is_active' => true
                    ],
                    'general' => [
                        'ai_name' => '',
                        'type' => 'general',
                        'context' => 'Cấu hình chung cho model AI',
                        'language' => 'vi',
                        'model_type' => 'gemini-1.5-pro',
                        'temperature' => 0.9,
                        'max_tokens' => 2048,
                        'top_p' => 1,
                        'top_k' => 40,
                        'priority' => 0,
                        'is_active' => true
                    ]
                ]
            ];

            Log::info('AI Config data being sent to frontend:', $data);

            if ($request->wantsJson()) {
                return $this->respondWithJson($data);
            }

            return Inertia::render('AiConfig/AiConfigView', $data);
        } catch (\Exception $e) {
            Log::error('Error in AiConfigController@index: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * @OA\Post(
     *     path="/api/ai-configs",
     *     summary="Tạo mới cấu hình AI",
     *     tags={"AI Configs"}
     * )
     */
    public function store(Request $request)
    {
        try {
            $validated = $this->validateConfig($request);
            
            // Xử lý các trường JSON
            $jsonFields = ['safety_settings', 'function_declarations', 'tool_config'];
            foreach ($jsonFields as $field) {
                if (isset($validated[$field]) && is_string($validated[$field])) {
                    $validated[$field] = json_decode($validated[$field], true);
                }
            }

            $config = $this->aiConfigService->createConfig($validated);

            return response()->json([
                'message' => 'Configuration created successfully',
                'config' => $this->formatConfigForResponse($config),
                'configs' => $this->formatConfigsForResponse($this->aiConfigService->getAllConfigs())
            ]);
        } catch (\Exception $e) {
            Log::error('Config creation failed: ' . $e->getMessage());
            return $this->respondWithError('Failed to create configuration: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *     path="/api/ai-configs/{id}",
     *     summary="Cập nhật cấu hình AI",
     *     tags={"AI Configs"}
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $this->validateConfig($request);
            
            // Xử lý các trường JSON
            $jsonFields = ['safety_settings', 'function_declarations', 'tool_config'];
            foreach ($jsonFields as $field) {
                if (isset($validated[$field]) && is_string($validated[$field])) {
                    $validated[$field] = json_decode($validated[$field], true);
                }
            }

            $config = $this->aiConfigService->updateConfig($id, $validated);

            return response()->json([
                'message' => 'Configuration updated successfully',
                'config' => $this->formatConfigForResponse($config),
                'configs' => $this->formatConfigsForResponse($this->aiConfigService->getAllConfigs())
            ]);
        } catch (\Exception $e) {
            Log::error('Config update failed: ' . $e->getMessage());
            return $this->respondWithError('Failed to update configuration: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/ai-configs/{id}",
     *     summary="Xóa cấu hình AI",
     *     tags={"AI Configs"}
     * )
     */
    public function destroy($id)
    {
        try {
            $this->aiConfigService->deleteConfig($id);

            return response()->json([
                'message' => 'Configuration deleted successfully',
                'configs' => $this->formatConfigsForResponse($this->aiConfigService->getAllConfigs())
            ]);
        } catch (\Exception $e) {
            Log::error('Config deletion failed: ' . $e->getMessage());
            return $this->respondWithError('Failed to delete configuration: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/ai-configs/upload",
     *     summary="Upload cấu hình AI từ file",
     *     tags={"AI Configs"}
     * )
     */
    public function upload(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:json,txt|max:2048',
                'type' => 'required|string|in:system_prompt,vision_config,general'
            ]);

            $config = $this->aiConfigService->handleConfigUpload(
                $request->file('file'),
                $request->input('type')
            );

            return $this->respondWithJson(
                $this->formatConfigForResponse($config), 
                'Configuration uploaded successfully'
            );
        } catch (\Exception $e) {
            Log::error('Config upload failed: ' . $e->getMessage());
            return $this->respondWithError('Failed to upload configuration: ' . $e->getMessage());
        }
    }

    /**
     * Validate config request
     */
    protected function validateConfig(Request $request)
    {
        return $request->validate([
            'ai_name' => 'required|string|max:255',
            'type' => 'required|string|in:system_prompt,vision_config,general',
            'context' => 'required|string',
            'api_key' => 'nullable|string|max:255',
            'language' => 'required|string|in:vi,en,ja',
            'gemini_settings' => 'nullable|array',
            'is_active' => 'boolean',
            'priority' => 'integer|min:0',
            'model_type' => 'required|string|in:' . implode(',', array_keys(AiChatConfig::MODEL_TYPES)),
            'max_tokens' => 'integer|min:1|max:8192',
            'temperature' => 'numeric|min:0|max:1',
            'top_p' => 'numeric|min:0|max:1',
            'top_k' => 'integer|min:1|max:100',
            'safety_settings' => 'nullable|array',
            'function_declarations' => 'nullable|array',
            'tool_config' => 'nullable|array',
            'system_instructions' => 'nullable|string',
            'response_format' => 'nullable|string|in:' . implode(',', array_keys(AiChatConfig::RESPONSE_FORMATS)),
            'stop_sequences' => 'nullable|array',
            'metadata' => 'nullable|array'
        ]);
    }

    /**
     * Format single config for response
     */
    protected function formatConfigForResponse($config)
    {
        return [
            'id' => $config->id,
            'ai_name' => $config->ai_name,
            'type' => $config->type,
            'context' => $config->context,
            'api_key' => $config->api_key,
            'language' => $config->language,
            'model_type' => $config->model_type,
            'temperature' => $config->temperature,
            'max_tokens' => $config->max_tokens,
            'top_p' => $config->top_p,
            'top_k' => $config->top_k,
            'priority' => $config->priority,
            'is_active' => $config->is_active,
            'gemini_settings' => $config->gemini_settings,
            'safety_settings' => $config->safety_settings ?? AiChatConfig::SAFETY_SETTINGS,
            'function_declarations' => $config->function_declarations ?? AiChatConfig::FUNCTION_DECLARATIONS,
            'tool_config' => $config->tool_config ?? AiChatConfig::DEFAULT_TOOL_CONFIG,
            'system_instructions' => $config->system_instructions,
            'response_format' => $config->response_format ?? array_key_first(AiChatConfig::RESPONSE_FORMATS),
            'stop_sequences' => $config->stop_sequences ?? [],
            'metadata' => $config->metadata ?? [],
            'created_at' => $config->created_at,
            'updated_at' => $config->updated_at,
            'last_used_at' => $config->last_used_at
        ];
    }

    /**
     * Format config collection for response
     */
    protected function formatConfigsForResponse($configs)
    {
        return $configs->map(function ($config) {
            return $this->formatConfigForResponse($config);
        });
    }
}
