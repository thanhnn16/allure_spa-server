<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="RewardItemTranslation",
 *     title="Reward Item Translation",
 *     description="Model representing a translation of a reward item",
 *     @OA\Property(property="id", type="integer", description="The ID of the translation"),
 *     @OA\Property(property="reward_item_id", type="integer", description="The ID of the related reward item"),
 *     @OA\Property(property="language", type="string", description="The language code of the translation"),
 *     @OA\Property(property="name", type="string", description="The translated name of the reward item"),
 *     @OA\Property(property="description", type="string", description="The translated description of the reward item"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp of creation"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Timestamp of last update")
 * )
 */
class RewardItemTranslation extends Model
{
    use HasFactory;

    protected $fillable = ['reward_item_id', 'language', 'name', 'description'];

    public function rewardItem()
    {
        return $this->belongsTo(RewardItem::class);
    }
}