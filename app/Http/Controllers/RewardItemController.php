<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\RewardItem;
use App\Services\RewardItemService;
use Illuminate\Http\Request;
use App\Exceptions\InsufficientPointsException;
use App\Exceptions\ItemNotAvailableException;

/**
 * @OA\Tag(
 *     name="Reward Items",
 *     description="API Endpoints for managing reward items"
 * )
 */
class RewardItemController extends Controller
{
    protected $rewardItemService;

    public function __construct(RewardItemService $rewardItemService)
    {
        $this->rewardItemService = $rewardItemService;
    }

    /**
     * @OA\Get(
     *     path="/api/admin/reward-items",
     *     summary="List all available rewards",
     *     tags={"Reward Items"},
     *     @OA\Response(
     *         response=200,
     *         description="List of available rewards"
     *     )
     * )
     */
    public function index()
    {
        return $this->rewardItemService->listAvailableRewards();
    }

    /**
     * @OA\Post(
     *     path="/api/admin/reward-items",
     *     summary="Create a new reward item",
     *     tags={"Reward Items"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","item_type","item_id","points_required"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="item_type", type="string", enum={"product","service"}),
     *             @OA\Property(property="item_id", type="integer"),
     *             @OA\Property(property="points_required", type="integer", minimum=1),
     *             @OA\Property(property="quantity_available", type="integer", minimum=0),
     *             @OA\Property(property="start_date", type="string", format="date"),
     *             @OA\Property(property="end_date", type="string", format="date")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Reward item created successfully"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/api/admin/reward-items/{rewardItem}/redeem",
     *     summary="Redeem a reward item",
     *     tags={"Reward Items"},
     *     @OA\Parameter(
     *         name="rewardItem",
     *         in="path",
     *         required=true,
     *         description="ID of reward item",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reward redeemed successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error redeeming reward"
     *     )
     * )
     */
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
