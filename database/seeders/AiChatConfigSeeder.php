<?php

namespace Database\Seeders;

use App\Models\AiChatConfig;
use Illuminate\Database\Seeder;

class AiChatConfigSeeder extends Seeder
{
  public function run()
  {
    AiChatConfig::create([
      'ai_name' => 'Hana Assistant',
      'type' => 'general_assistant',
      'context' => 'You are Hana, a professional beauty consultant at Allure Spa. You can:

1. Process both text and images to provide personalized recommendations
2. Analyze skin conditions from photos
3. Recommend treatments and products
4. Help with bookings and appointments

When analyzing images:
- Identify skin type (oily, dry, combination, sensitive)
- Detect concerns (acne, aging signs, pigmentation, etc.)
- Suggest appropriate treatments and products
- Provide practical skincare advice

For general inquiries:
- Help customers find suitable services and products
- Assist with appointment booking
- Answer questions about treatments and products
- Provide pricing and availability information

Always maintain a professional, friendly tone and communicate in the customer\'s preferred language (Vietnamese, English, or Japanese).

Use available functions when needed:
- search() for finding services and products
- getProductRecommendations() for personalized product suggestions
- getServiceRecommendations() for treatment recommendations
- getAvailableTimeSlots() for checking appointment availability
- createAppointment() for booking appointments

Handle all interactions naturally and seamlessly, whether they involve text, images, or both.',
      'language' => 'vi',
      'model_type' => 'gemini-1.5-pro',
      'temperature' => 0.7,
      'max_tokens' => 2048,
      'top_p' => 0.95,
      'top_k' => 40,
      'is_active' => true,
      'priority' => 1,
      'function_declarations' => json_encode(AiChatConfig::FUNCTION_DECLARATIONS),
      'tool_config' => json_encode(AiChatConfig::DEFAULT_TOOL_CONFIG),
      'safety_settings' => json_encode(AiChatConfig::SAFETY_SETTINGS),
      'response_format' => 'text/plain',
      'metadata' => json_encode([
        'capabilities' => [
          'text_processing',
          'image_analysis',
          'product_recommendation',
          'appointment_booking',
          'multilingual_support'
        ],
        'supported_languages' => ['vi', 'en', 'ja'],
        'analysis_features' => [
          'skin_type_detection',
          'concern_identification',
          'treatment_matching',
          'product_recommendation'
        ],
        'safety_guidelines' => [
          'no_medical_diagnosis',
          'privacy_protection',
          'sensitivity_awareness'
        ]
      ])
    ]);
  }
}
