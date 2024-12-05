<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class VerificationToken extends Model
{
    protected $table = 'verification_tokens';

    protected $fillable = [
        'user_id',
        'token',
        'type',
        'expires_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'user_id' => 'string'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
