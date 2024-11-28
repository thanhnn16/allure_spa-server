<?php

namespace App\Services;

use App\Models\RewardItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RewardItemService
{
    public function createRewardItem(array $data)
    {
        return RewardItem::create($data);
    }

    public function updateRewardItem(RewardItem $rewardItem, array $data)
    {
        $rewardItem->update($data);
        return $rewardItem;
    }

    public function listAvailableRewards()
    {
        return RewardItem::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            })
            ->where(function ($query) {
                $query->whereNull('quantity_available')
                    ->orWhere('quantity_available', '>', 0);
            })
            ->with(['product', 'service'])
            ->get();
    }

    public function redeemReward(User $user, RewardItem $rewardItem)
    {
        if (!$rewardItem->isAvailable()) {
            throw new \Exception('This reward is not available.');
        }

        if ($user->loyalty_points < $rewardItem->points_required) {
            throw new \Exception('Insufficient points to redeem this reward.');
        }

        return DB::transaction(function () use ($user, $rewardItem) {
            // Deduct points
            $user->loyalty_points -= $rewardItem->points_required;
            $user->save();

            // Update quantity if applicable
            if ($rewardItem->quantity_available !== null) {
                $rewardItem->decrement('quantity_available');
            }

            // Record redemption
            return $user->pointRedemptions()->create([
                'reward_item_id' => $rewardItem->id,
                'points_used' => $rewardItem->points_required
            ]);
        });
    }
}
