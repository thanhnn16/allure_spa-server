<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RewardItemTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'reward_item_id',
        'language',
        'item_name',
        'description'
    ];

    public function rewardItem()
    {
        return $this->belongsTo(RewardItem::class);
    }
}
