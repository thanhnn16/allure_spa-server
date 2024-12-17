<?php

namespace App\Http\Controllers;

use App\Models\RewardItem;
use App\Models\PointRedemptionHistory;
use App\Models\User;
use App\Services\RewardItemService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Product;
use App\Models\Service;

class RewardManagementController extends Controller
{
    protected $rewardItemService;

    public function __construct(RewardItemService $rewardItemService)
    {
        $this->rewardItemService = $rewardItemService;
    }

    public function index()
    {
        return Inertia::render('Rewards/Index', [
            'products' => Product::all(['id', 'name']),
            'services' => Service::all(['id', 'service_name'])
        ]);
    }

    public function getRewards()
    {
        $rewards = RewardItem::with(['product', 'service'])
            ->withCount('redemptions')
            ->get();

        return response()->json($rewards);
    }

    public function getRedemptionHistory(Request $request)
    {
        $history = PointRedemptionHistory::with(['user', 'rewardItem'])
            ->when($request->search, function ($query, $search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10);

        return response()->json($history);
    }

    public function getUserPoints($userId)
    {
        $user = User::findOrFail($userId);
        return response()->json([
            'points' => $user->loyalty_points
        ]);
    }
}
