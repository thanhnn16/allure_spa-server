<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointRedemptionHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'reward_item_id', 'points_used', 'redemption_date'
    ];

    protected $casts = [
        'redemption_date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rewardItem()
    {
        return $this->belongsTo(RewardItem::class);
    }
}