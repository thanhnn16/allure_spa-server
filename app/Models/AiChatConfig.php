<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="AiChatConfig",
 *     title="Cấu hình Chat AI",
 *     description="Model quản lý các cài đặt cấu hình chat AI",
 *     @OA\Property(
 *         property="ai_name",
 *         type="string",
 *         description="Tên cài đặt cấu hình"
 *     ),
 *     @OA\Property(
 *         property="type",
 *         type="string",
 *         description="Loại cài đặt cấu hình"
 *     ),
 *     @OA\Property(
 *         property="context",
 *         type="string",
 *         description="Mô tả về cài đặt cấu hình"
 *     ),
 *     @OA\Property(
 *         property="api_key",
 *         type="string",
 *         description="API key"
 *     ),
 *     @OA\Property(
 *         property="language",
 *         type="string",
 *         description="Ngôn ngữ của cài đặt cấu hình"
 *     ),
 *     @OA\Property(
 *         property="gemini_settings",
 *         type="array",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="key", type="string"),
 *             @OA\Property(property="value", type="string")
 *         ),
 *         description="Cài đặt Gemini"
 *     ),
 *     @OA\Property(
 *         property="is_active",
 *         type="boolean",
 *         description="Trạng thái cấu hình"
 *     ),
 *     @OA\Property(
 *         property="priority",
 *         type="integer",
 *         description="Độ ưu tiên cấu hình"
 *     ),
 *     @OA\Property(
 *         property="version",
 *         type="string",
 *         description="Phiên bản cấu hình"
 *     ),
 *     @OA\Property(
 *         property="model_type",
 *         type="string",
 *         description="Loại model"
 *     ),
 *     @OA\Property(
 *         property="max_tokens",
 *         type="integer",
 *         description="Số lượng token tối đa"
 *     ),
 *     @OA\Property(
 *         property="temperature",
 *         type="number",
 *         format="float",
 *         description="Nhiệt độ"
 *     ),
 *     @OA\Property(
 *         property="top_p",
 *         type="number",
 *         format="float",
 *         description="Top P"
 *     ),
 *     @OA\Property(
 *         property="top_k",
 *         type="integer",
 *         description="Top K"
 *     ),
 *     @OA\Property(
 *         property="metadata",
 *         type="array",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="key", type="string"),
 *             @OA\Property(property="value", type="string")
 *         ),
 *         description="Metadata"
 *     ),
 *     @OA\Property(
 *         property="safety_settings",
 *         type="array",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="category", type="string"),
 *             @OA\Property(property="threshold", type="string")
 *         ),
 *         description="Cài đặt an toàn"
 *     ),
 *     @OA\Property(
 *         property="function_declarations",
 *         type="array",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="name", type="string"),
 *             @OA\Property(property="description", type="string"),
 *             @OA\Property(
 *                 property="parameters",
 *                 type="object",
 *                 @OA\Property(property="type", type="string"),
 *                 @OA\Property(property="properties", type="object"),
 *                 @OA\Property(
 *                     property="required",
 *                     type="array",
 *                     @OA\Items(type="string")
 *                 )
 *             )
 *         ),
 *         description="Khai báo functions"
 *     ),
 *     @OA\Property(
 *         property="tool_config",
 *         type="object",
 *         description="Cấu hình công cụ"
 *     ),
 *     @OA\Property(
 *         property="system_instructions",
 *         type="string",
 *         description="Hướng dẫn hệ thống"
 *     ),
 *     @OA\Property(
 *         property="response_format",
 *         type="string",
 *         description="Định dạng phản hồi"
 *     ),
 *     @OA\Property(
 *         property="stop_sequences",
 *         type="array",
 *         @OA\Items(type="string"),
 *         description="Chuỗi dừng"
 *     ),
 *     @OA\Property(
 *         property="last_used_at",
 *         type="string",
 *         format="date-time",
 *         description="Thời gian sử dụng cuối"
 *     )
 * )
 */
class AiChatConfig extends Model
{
    use HasFactory;

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
        'last_used_at',
        'safety_settings',
        'function_declarations',
        'tool_config',
        'system_instructions',
        'response_format',
        'stop_sequences',
    ];

    protected $casts = [
        'gemini_settings' => 'array',
        'metadata' => 'array',
        'is_active' => 'boolean',
        'temperature' => 'decimal:2',
        'top_p' => 'decimal:2',
        'last_used_at' => 'datetime',
        'safety_settings' => 'array',
        'function_declarations' => 'array',
        'tool_config' => 'array',
        'stop_sequences' => 'array',
    ];

    // Model types available
    const MODEL_TYPES = [
        'gemini-1.5-pro' => 'Gemini 1.5 Pro',
        'gemini-1.5-flash' => 'Gemini 1.5 Flash',
    ];

    // Config types with descriptions
    const TYPES = [
        'general_assistant' => [
            'name' => 'Trợ Lý Tổng Hợp',
            'description' => 'Cấu hình AI có thể xử lý cả text và hình ảnh'
        ],
        'global_api_key' => [
            'name' => 'API Key Chung',
            'description' => 'Cấu hình API key dùng chung cho hệ thống'
        ]
    ];

    // Languages supported
    const LANGUAGES = [
        'vi' => 'Tiếng Việt',
        'en' => 'English',
        'ja' => '日本語'
    ];

    const FUNCTION_DECLARATIONS = [
        [
            'name' => 'search',
            'description' => 'Search for products, services or all items',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'query' => [
                        'type' => 'string',
                        'description' => 'Search query'
                    ],
                    'type' => [
                        'type' => 'string',
                        'enum' => ['all', 'products', 'services'],
                        'description' => 'Type of items to search for'
                    ],
                    'limit' => [
                        'type' => 'integer',
                        'description' => 'Maximum number of results'
                    ]
                ],
                'required' => ['query', 'type']
            ]
        ],
        [
            'name' => 'getProductRecommendations',
            'description' => 'Get product recommendations based on skin concerns and conditions',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'skin_type' => [
                        'type' => 'string',
                        'enum' => ['oily', 'dry', 'combination', 'sensitive'],
                        'description' => 'Customer skin type'
                    ],
                    'concerns' => [
                        'type' => 'array',
                        'items' => [
                            'type' => 'string',
                            'enum' => [
                                'acne',
                                'aging',
                                'pigmentation',
                                'dryness',
                                'sensitivity',
                                'dullness',
                                'pores',
                                'wrinkles'
                            ]
                        ],
                        'description' => 'List of skin concerns'
                    ],
                    'price_range' => [
                        'type' => 'object',
                        'properties' => [
                            'min' => ['type' => 'number'],
                            'max' => ['type' => 'number']
                        ],
                        'description' => 'Price range for products'
                    ],
                    'category_id' => [
                        'type' => 'integer',
                        'description' => 'Specific product category ID'
                    ]
                ],
                'required' => ['skin_type']
            ]
        ],
        [
            'name' => 'getServiceRecommendations',
            'description' => 'Get spa service recommendations based on customer needs',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'treatment_type' => [
                        'type' => 'string',
                        'enum' => ['facial', 'massage', 'body', 'specialized'],
                        'description' => 'Type of treatment needed'
                    ],
                    'concerns' => [
                        'type' => 'array',
                        'items' => [
                            'type' => 'string',
                            'enum' => [
                                'relaxation',
                                'skin_improvement',
                                'anti_aging',
                                'pain_relief',
                                'detox',
                                'slimming'
                            ]
                        ],
                        'description' => 'Customer concerns and goals'
                    ],
                    'duration_preference' => [
                        'type' => 'integer',
                        'description' => 'Preferred treatment duration in minutes'
                    ],
                    'price_range' => [
                        'type' => 'object',
                        'properties' => [
                            'min' => ['type' => 'number'],
                            'max' => ['type' => 'number']
                        ],
                        'description' => 'Budget range for services'
                    ],
                    'category_id' => [
                        'type' => 'integer',
                        'description' => 'Specific service category ID'
                    ]
                ],
                'required' => ['treatment_type']
            ]
        ],
        [
            'name' => 'getProductDetails',
            'description' => 'Get detailed information about specific products',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'product_id' => [
                        'type' => 'integer',
                        'description' => 'ID of the product'
                    ]
                ],
                'required' => ['product_id']
            ]
        ],
        [
            'name' => 'getServiceDetails',
            'description' => 'Get detailed information about specific services',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'service_id' => [
                        'type' => 'integer',
                        'description' => 'ID of the service'
                    ]
                ],
                'required' => ['service_id']
            ]
        ],
        [
            'name' => 'getAvailableTimeSlots',
            'description' => 'Get available time slots for a specific date',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'date' => [
                        'type' => 'string',
                        'description' => 'Date to check availability (YYYY-MM-DD format)'
                    ]
                ],
                'required' => ['date']
            ]
        ],
        [
            'name' => 'createAppointment',
            'description' => 'Create a new appointment',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'service_id' => [
                        'type' => 'integer',
                        'description' => 'ID of the service'
                    ],
                    'appointment_date' => [
                        'type' => 'string',
                        'description' => 'Date of appointment (YYYY-MM-DD format)'
                    ],
                    'time_slot_id' => [
                        'type' => 'integer',
                        'description' => 'ID of the selected time slot'
                    ],
                    'appointment_type' => [
                        'type' => 'string',
                        'enum' => ['consultation', 'treatment', 'follow_up']
                    ],
                    'note' => [
                        'type' => 'string',
                        'description' => 'Additional notes for the appointment'
                    ]
                ],
                'required' => ['service_id', 'appointment_date', 'time_slot_id', 'appointment_type']
            ]
        ],
        [
            'name' => 'getUserVouchers',
            'description' => 'Get all vouchers of current user',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'status' => [
                        'type' => 'string',
                        'enum' => ['active', 'expired', 'all'],
                        'description' => 'Filter vouchers by status'
                    ]
                ]
            ]
        ],
        [
            'name' => 'getUserInvoices',
            'description' => 'Get all invoices of current user',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'status' => [
                        'type' => 'string',
                        'enum' => ['pending', 'paid', 'cancelled', 'all'],
                        'description' => 'Filter invoices by status'
                    ],
                    'from_date' => [
                        'type' => 'string',
                        'description' => 'Start date (YYYY-MM-DD)'
                    ],
                    'to_date' => [
                        'type' => 'string',
                        'description' => 'End date (YYYY-MM-DD)'
                    ]
                ]
            ]
        ],
        [
            'name' => 'getUserFavorites',
            'description' => 'Get all favorites of current user',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'type' => [
                        'type' => 'string',
                        'enum' => ['products', 'services', 'all'],
                        'description' => 'Type of favorites to get'
                    ]
                ],
                'required' => ['type']
            ]
        ]
    ];

    // Safety settings mặc định
    const SAFETY_SETTINGS = [
        [
            'category' => 'HARM_CATEGORY_HARASSMENT',
            'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
        ],
        [
            'category' => 'HARM_CATEGORY_HATE_SPEECH',
            'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
        ],
        [
            'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
            'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
        ],
        [
            'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
            'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
        ]
    ];

    // Tool config mặc định
    const DEFAULT_TOOL_CONFIG = [
        'functionCallingConfig' => [
            'mode' => 'ANY'
        ]
    ];

    // Response formats
    const RESPONSE_FORMATS = [
        'text' => 'text/plain',
        'json' => 'application/json',
        'markdown' => 'text/markdown'
    ];

    // Thêm constant mới
    const GLOBAL_API_KEY_TYPE = 'global_api_key';

    // Thêm scope để lấy global API key
    public static function getGlobalApiKey()
    {
        return self::where('type', self::GLOBAL_API_KEY_TYPE)
            ->where('is_active', true)
            ->first()?->api_key;
    }

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
                'stopSequences' => $this->stop_sequences ?? [],
            ],
            'safetySettings' => $this->safety_settings ?? self::SAFETY_SETTINGS,
            'tools' => [
                [
                    'functionDeclarations' => $this->function_declarations ?? self::FUNCTION_DECLARATIONS
                ]
            ],
            'toolConfig' => $this->tool_config ?? self::DEFAULT_TOOL_CONFIG,
            'systemInstructions' => $this->system_instructions,
            'responseMimeType' => $this->response_format ?? self::RESPONSE_FORMATS['text'],
            'metadata' => $this->metadata ?? []
        ];
    }

    // Helper method để lấy function declarations
    public static function getFunctionDeclarations()
    {
        return self::FUNCTION_DECLARATIONS;
    }

    // Helper method để lấy safety settings
    public static function getSafetySettings()
    {
        return self::SAFETY_SETTINGS;
    }
}
