<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="RewardItem",
 *     title="Reward Item",
 *     description="Model representing a reward item",
 *     @OA\Property(property="id", type="integer", description="The unique identifier of the reward item"),
 *     @OA\Property(property="name", type="string", description="The name of the reward item"),
 *     @OA\Property(property="description", type="string", description="The description of the reward item"),
 *     @OA\Property(property="points_required", type="integer", description="The number of points required to redeem this item"),
 *     @OA\Property(property="quantity_available", type="integer", description="The quantity of this item available for redemption"),
 *     @OA\Property(property="image_id", type="integer", description="The ID of the associated image"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="The creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="The last update timestamp")
 * )
 */
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