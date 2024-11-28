<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\RewardItem;
use App\Services\RewardItemService;
use Illuminate\Http\Request;
use App\Exceptions\InsufficientPointsException;
use App\Exceptions\ItemNotAvailableException;

class RewardItemController extends Controller
{
    protected $rewardItemService;

    public function __construct(RewardItemService $rewardItemService)
    {
        $this->rewardItemService = $rewardItemService;
    }

    public function index()
    {
        return $this->rewardItemService->listAvailableRewards();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'item_type' => 'required|in:product,service',
            'item_id' => 'required|integer',
            'points_required' => 'required|integer|min:1',
            'quantity_available' => 'nullable|integer|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
        ]);

        return $this->rewardItemService->createRewardItem($validated);
    }

    public function redeem(Request $request, RewardItem $rewardItem)
    {
        try {
            $redemption = $this->rewardItemService->redeemReward(
                $request->user(),
                $rewardItem
            );
            
            return response()->json([
                'message' => 'Reward redeemed successfully',
                'redemption' => $redemption
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}