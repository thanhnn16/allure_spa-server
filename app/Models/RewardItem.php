<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RewardItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'item_name',
        'description',
        'points_required',
        'quantity',
        'image_id',
        'status'
    ];

    public function image()
    {
        return $this->belongsTo(Image::class);
    }

    public function translations()
    {
        return $this->hasMany(RewardItemTranslation::class);
    }

    public function redemptions()
    {
        return $this->hasMany(PointRedemptionHistory::class);
    }
}
