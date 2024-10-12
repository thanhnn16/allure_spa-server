<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RewardItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'points_required', 'quantity_available', 'image_id'
    ];

    public function image()
    {
        return $this->belongsTo(Image::class);
    }

    public function pointRedemptionHistories()
    {
        return $this->hasMany(PointRedemptionHistory::class);
    }

    public function translations()
    {
        return $this->hasMany(RewardItemTranslation::class);
    }
}