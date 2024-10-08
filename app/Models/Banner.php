<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = ['image_url', 'link', 'is_active', 'order'];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
