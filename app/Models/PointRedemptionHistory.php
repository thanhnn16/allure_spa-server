<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="PointRedemptionHistory",
 *     title="Point Redemption History",
 *     description="Model representing a point redemption history",
 *     @OA\Property(property="id", type="integer", description="The point redemption history ID"),
 *     @OA\Property(property="user_id", type="integer", description="The associated user ID"),
 *     @OA\Property(property="reward_item_id", type="integer", description="The associated reward item ID"),
 *     @OA\Property(property="points_used", type="integer", description="The number of points used for redemption"),
 *     @OA\Property(property="redemption_date", type="string", format="date-time", description="The date and time of redemption"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Last update timestamp")
 * )
 */
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