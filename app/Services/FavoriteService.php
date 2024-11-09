<?php

namespace App\Services;

use App\Models\Favorite;
use Illuminate\Support\Facades\Auth;

class FavoriteService
{
    public function toggleFavorite($type, $itemId)
    {
        $userId = Auth::id();

        // Check if favorite exists
        $favorite = Favorite::where('user_id', $userId)
            ->where('favorite_type', $type)
            ->where('item_id', $itemId)
            ->first();

        if ($favorite) {
            $favorite->delete();
            return [
                'status' => 'removed',
                'message' => 'Item removed from favorites'
            ];
        }

        // Create new favorite
        Favorite::create([
            'user_id' => $userId,
            'favorite_type' => $type,
            'item_id' => $itemId
        ]);

        return [
            'status' => 'added',
            'message' => 'Item added to favorites'
        ];
    }

    public function getUserFavorites()
    {
        return Favorite::where('user_id', Auth::id())
            ->with(['product', 'service'])
            ->get()
            ->map(function ($favorite) {
                // Thêm thông tin chi tiết của product hoặc service
                if ($favorite->favorite_type === 'product') {
                    $favorite->item_details = $favorite->product;
                } else {
                    $favorite->item_details = $favorite->service;
                }
                return $favorite;
            });
    }

    public function getFavoritesByType($type)
    {
        $favorites = Favorite::where('user_id', Auth::id())
            ->where('favorite_type', $type)
            ->with($type === 'product' ? 'product' : 'service')
            ->get();

        return $favorites;
    }
}
