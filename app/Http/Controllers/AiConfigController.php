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

    public function index(Request $request)
    {
        $configs = $this->aiConfigService->getAllConfigs();

        if ($request->wantsJson()) {
            $configsWithApiKey = $configs->map(function ($config) {
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
                ];
            });
            return $this->respondWithJson($configsWithApiKey);
        }

        return Inertia::render('AiConfig/AiConfigView', [
            'configs' => $configs->map(function ($config) {
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
                ];
            }),
            'configTypes' => AiChatConfig::TYPES
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'ai_name' => 'required|string|max:255',
                'type' => 'required|string|in:system_prompt,vision_config,general',
                'context' => 'required|string',
                'api_key' => 'nullable|string|max:255',
                'language' => 'required|string|in:vi,en,ja',
                'gemini_settings' => 'nullable|array',
                'is_active' => 'boolean',
                'priority' => 'integer|min:0',
                'model_type' => 'string|in:gemini-1.5-pro,gemini-1.0-pro,gemini-vision-pro',
                'max_tokens' => 'integer|min:1|max:8192',
                'temperature' => 'numeric|min:0|max:1',
                'top_p' => 'numeric|min:0|max:1',
                'top_k' => 'integer|min:1|max:100'
            ]);

            if ($request->has('api_key')) {
                $validated['api_key'] = $request->input('api_key');
            }

            $config = $this->aiConfigService->createConfig($validated);

            return response()->json([
                'message' => 'Configuration created successfully',
                'config' => $config,
                'configs' => $this->aiConfigService->getAllConfigs()
            ]);
        } catch (\Exception $e) {
            Log::error('Config creation failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to create configuration: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'ai_name' => 'required|string|max:255',
                'type' => 'required|string|in:system_prompt,vision_config,general',
                'context' => 'required|string',
                'api_key' => 'nullable|string|max:255',
                'language' => 'required|string|in:vi,en,ja',
                'gemini_settings' => 'nullable|array',
                'is_active' => 'boolean',
                'priority' => 'integer|min:0',
                'model_type' => 'string|in:gemini-1.5-pro,gemini-1.0-pro,gemini-vision-pro',
                'max_tokens' => 'integer|min:1|max:8192',
                'temperature' => 'numeric|min:0|max:1',
                'top_p' => 'numeric|min:0|max:1',
                'top_k' => 'integer|min:1|max:100'
            ]);

            if ($request->has('api_key')) {
                $validated['api_key'] = $request->input('api_key');
            }

            $config = $this->aiConfigService->updateConfig($id, $validated);

            return response()->json([
                'message' => 'Configuration updated successfully',
                'config' => $config,
                'configs' => $this->aiConfigService->getAllConfigs()
            ]);
        } catch (\Exception $e) {
            Log::error('Config update failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to update configuration: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->aiConfigService->deleteConfig($id);

            return response()->json([
                'message' => 'Configuration deleted successfully',
                'configs' => $this->aiConfigService->getAllConfigs()
            ]);
        } catch (\Exception $e) {
            Log::error('Config deletion failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to delete configuration: ' . $e->getMessage()
            ], 500);
        }
    }

    public function upload(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:json,txt|max:2048',
                'type' => 'required|string'
            ]);

            $config = $this->aiConfigService->handleConfigUpload(
                $request->file('file'),
                $request->input('type')
            );

            return $this->respondWithJson($config, 'Configuration uploaded successfully');
        } catch (\Exception $e) {
            Log::error('Config upload failed: ' . $e->getMessage());
            return $this->respondWithError('Failed to upload configuration: ' . $e->getMessage());
        }
    }
}
