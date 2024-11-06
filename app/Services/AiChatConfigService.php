<?php

namespace App\Services;

use App\Models\AiChatConfig;
use Illuminate\Support\Facades\Log;

class AiChatConfigService
{
    /**
     * Lấy tất cả cấu hình AI
     */
    public function getAllConfigs()
    {
        // Lấy tất cả configs thông thường
        $regularConfigs = AiChatConfig::whereNotIn('type', [AiChatConfig::GLOBAL_API_KEY_TYPE])->get();
        
        // Lấy global API key config
        $globalApiKey = AiChatConfig::where('type', AiChatConfig::GLOBAL_API_KEY_TYPE)
            ->where('is_active', true)
            ->first()?->api_key;

        // Thêm global API key vào metadata của mỗi config
        return $regularConfigs->map(function ($config) use ($globalApiKey) {
            $config->global_api_key = $globalApiKey;
            return $config;
        });
    }

    /**
     * Lấy các cấu hình đang được kích hoạt
     */
    public function getActiveConfigs()
    {
        return AiChatConfig::where('is_active', true)->get();
    }

    /**
     * Lấy cấu hình theo loại
     * @param string $type Loại cấu hình (system_prompt, search_config, vision_config, general)
     */
    public function getConfigsByType($type)
    {
        return AiChatConfig::where('type', $type)
            ->where('is_active', true)
            ->get();
    }

    /**
     * Tạo mới cấu hình AI
     * @param array $data Dữ liệu cấu hình
     * @throws \Exception
     */
    public function createConfig(array $data)
    {
        try {
            // Đảm bảo các trường bắt buộc
            $defaultData = [
                'is_active' => true,
                'priority' => 0,
                'version' => '1.0.0',
                'model_type' => 'gemini-1.5-pro',
                'max_tokens' => 2048,
                'temperature' => 0.90,
                'top_p' => 1.00,
                'top_k' => 40,
            ];

            // Merge data với default values
            $mergedData = array_merge($defaultData, $data);

            // Đảm bảo các trường JSON được lưu đúng định dạng
            $jsonFields = ['safety_settings', 'function_declarations', 'tool_config'];
            foreach ($jsonFields as $field) {
                if (isset($mergedData[$field])) {
                    if (is_string($mergedData[$field])) {
                        $mergedData[$field] = json_decode($mergedData[$field], true);
                    }
                } else {
                    $mergedData[$field] = null;
                }
            }

            return AiChatConfig::create($mergedData);
        } catch (\Exception $e) {
            Log::error('Error creating AI config: ' . $e->getMessage());
            throw $e;
        }
    }

    public function updateConfig($id, array $data)
    {
        try {
            $config = AiChatConfig::findOrFail($id);

            // Xử lý các trường JSON (không bao gồm context)
            $jsonFields = ['safety_settings', 'function_declarations', 'tool_config'];
            foreach ($jsonFields as $field) {
                if (isset($data[$field])) {
                    if (is_string($data[$field])) {
                        $data[$field] = json_decode($data[$field], true);
                    }
                }
            }

            // Đảm bảo context luôn là string
            if (isset($data['context']) && is_array($data['context'])) {
                $data['context'] = json_encode($data['context']);
            }

            $config->update($data);
            return $config->fresh();
        } catch (\Exception $e) {
            Log::error('Error updating AI config: ' . $e->getMessage());
            throw $e;
        }
    }

    public function deleteConfig($id)
    {
        try {
            $config = AiChatConfig::findOrFail($id);
            return $config->delete();
        } catch (\Exception $e) {
            Log::error('Error deleting AI config: ' . $e->getMessage());
            throw $e;
        }
    }

    public function handleConfigUpload($file, $type)
    {
        try {
            $content = file_get_contents($file->path());
            $fileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

            $data = [
                'ai_name' => $fileName,
                'type' => $type,
                'context' => $file->getClientOriginalExtension() === 'json'
                    ? json_decode($content, true)['content'] ?? $content
                    : $content,
                'language' => 'vi',
                'api_key' => null,
                'gemini_settings' => $file->getClientOriginalExtension() === 'json'
                    ? json_decode($content, true)['settings'] ?? null
                    : null,
                'is_active' => true,
                'priority' => 0
            ];

            return $this->createConfig($data);
        } catch (\Exception $e) {
            Log::error('Error handling config upload: ' . $e->getMessage());
            throw $e;
        }
    }

    // Helper function to check if a string is valid JSON
    private function isJson($string)
    {
        if (!is_string($string)) {
            return false;
        }
        json_decode($string);
        return (json_last_error() === JSON_ERROR_NONE);
    }
}
