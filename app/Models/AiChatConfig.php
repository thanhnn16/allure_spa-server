<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="AiChatConfig",
 *     title="Cấu hình Chat AI",
 *     description="Model quản lý các cài đặt cấu hình chat AI"
 * )
 */
class AiChatConfig extends Model
{
    use HasFactory;

    /**
     * @OA\Property(
     *     property="ai_name",
     *     type="string", 
     *     description="Tên cài đặt cấu hình"
     * )
     * @OA\Property(
     *     property="type",
     *     type="string",
     *     description="Loại cài đặt cấu hình"
     * )
     * @OA\Property(
     *     property="context",
     *     type="string",
     *     description="Mô tả về cài đặt cấu hình"
     * )
     * @OA\Property(
     *     property="language",
     *     type="string",
     *     description="Ngôn ngữ của cài đặt cấu hình"
     * )
     * @OA\Property(
     *     property="gemini_settings",
     *     type="array",
     *     description="Cài đặt Gemini ca cài đặt cấu hình"
     * )
     * @OA\Property(
     *     property="is_active",
     *     type="boolean",
     *     description="Trạng thái cấu hình"
     * )
     * @OA\Property(
     *     property="priority",
     *     type="integer",
     *     description="Độ ưu tiên cấu hình"
     * )
     * @OA\Property(
     *     property="version",
     *     type="string",
     *     description="Phiên bản cấu hình"
     * )
     * @OA\Property(
     *     property="model_type",
     *     type="string",
     *     description="Loại model"
     * )
     * @OA\Property(
     *     property="max_tokens",
     *     type="integer",
     *     description="Số lượng token tối đa"
     * )
     * @OA\Property(
     *     property="temperature",
     *     type="number",
     *     description="Nhiệt độ"
     * )
     * @OA\Property(
     *     property="top_p",
     *     type="number",
     *     description="Top P"
     * )
     * @OA\Property(
     *     property="top_k",
     *     type="integer",
     *     description="Top K"
     * )
     * @OA\Property(
     *     property="metadata",
     *     type="array",
     *     description="Metadata"
     * )
     * @OA\Property(
     *     property="last_used_at",
     *     type="string",
     *     description="Thời gian sử dụng"
     * )
     */
    protected $fillable = [
        'ai_name',
        'type',
        'context',
        'api_key',
        'language',
        'gemini_settings',
        'is_active',
        'priority',
        'version',
        'model_type',
        'max_tokens',
        'temperature',
        'top_p',
        'top_k',
        'metadata',
        'last_used_at'
    ];

    protected $hidden = [
        'api_key'
    ];

    protected $casts = [
        'gemini_settings' => 'array',
        'metadata' => 'array',
        'is_active' => 'boolean',
        'temperature' => 'decimal:2',
        'top_p' => 'decimal:2',
        'last_used_at' => 'datetime'
    ];

    // Model types available
    const MODEL_TYPES = [
        'gemini-1.5-pro' => 'Gemini 1.5 Pro',
        'gemini-1.0-pro' => 'Gemini 1.0 Pro',
        'gemini-vision-pro' => 'Gemini Vision Pro'
    ];

    // Config types with descriptions
    const TYPES = [
        'system_prompt' => [
            'name' => 'Mô Tả Hệ Thống',
            'description' => 'Cấu hình vai trò và hành vi cơ bản của AI'
        ],
        'vision_config' => [
            'name' => 'Phân Tích Hình Ảnh',
            'description' => 'Cấu hình cho khả năng nhận diện và phân tích hình ảnh'
        ],
        'general' => [
            'name' => 'Cài Đặt Chung',
            'description' => 'Cấu hình chung cho model AI'
        ]
    ];

    // Languages supported
    const LANGUAGES = [
        'vi' => 'Tiếng Việt',
        'en' => 'English',
        'ja' => '日本語'
    ];

    public static function getTypes()
    {
        return collect(self::TYPES)->map(fn($type) => $type['name'])->toArray();
    }

    public static function getModelTypes()
    {
        return self::MODEL_TYPES;
    }

    public static function getLanguages()
    {
        return self::LANGUAGES;
    }

    // Get active config by type
    public static function getActiveConfig($type)
    {
        return self::where('type', $type)
            ->where('is_active', true)
            ->orderBy('priority', 'desc')
            ->first();
    }

    // Track usage
    public function markAsUsed()
    {
        $this->update(['last_used_at' => now()]);
    }

    // Get formatted settings for Gemini
    public function getGeminiConfig()
    {
        return [
            'model' => $this->model_type,
            'generationConfig' => [
                'temperature' => (float)$this->temperature,
                'topP' => (float)$this->top_p,
                'topK' => (int)$this->top_k,
                'maxOutputTokens' => (int)$this->max_tokens,
            ],
            'metadata' => $this->metadata ?? []
        ];
    }
}