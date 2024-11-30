<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class VerificationToken extends Model
{
    use HasUuids;

    protected $table = 'verification_tokens';

    protected $fillable = [
        'user_id',
        'token',
        'type',
        'expires_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
