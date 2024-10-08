<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'rating_type',
        'rating_id',
        'rating_value',
        'comment'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ratable()
    {
        return $this->morphTo();
    }
}
