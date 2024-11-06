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
        return AiChatConfig::all();
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
            // Xử lý gemini_settings nếu có
            if (isset($data['gemini_settings']) && is_array($data['gemini_settings'])) {
                $data['gemini_settings'] = json_encode($data['gemini_settings']);
            }

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

            // Merge data, allowing api_key to be overwritten if provided
            $mergedData = array_merge($defaultData, $data);

            // Ensure api_key is included even if null
            if (!isset($mergedData['api_key'])) {
                $mergedData['api_key'] = null;
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
            
            // Handle api_key specially
            if (!isset($data['api_key'])) {
                // If api_key is not provided, keep the existing one
                $data['api_key'] = $config->api_key;
            }

            $config->update($data);
            return $config;
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
}
