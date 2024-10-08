<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiChatConfig extends Model
{
    protected $fillable = ['ai_name', 'context', 'language', 'gemini_settings'];

    protected $casts = [
        'gemini_settings' => 'array',
    ];
}
