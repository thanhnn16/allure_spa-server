<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    protected $fillable = [
        'rating_type_id', 'item_id', 'user_id', 'comment', 'image_id', 'stars'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function ratingType()
    {
        return $this->belongsTo(RatingType::class, 'rating_type_id');
    }
}
