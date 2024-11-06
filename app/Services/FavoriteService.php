<?php

namespace App\Services;

use App\Models\Favorite;
use Illuminate\Support\Facades\Auth;

class FavoriteService
{
    public function toggleFavorite($type, $itemId)
    {
        $userId = Auth::id();
        $field = $type === 'product' ? 'product_id' : 'service_id';
        
        $favorite = Favorite::where('user_id', $userId)
            ->where($field, $itemId)
            ->first();

        if ($favorite) {
            $favorite->delete();
            return [
                'status' => 'removed',
                'message' => 'Item removed from favorites'
            ];
        }

        Favorite::create([
            'user_id' => $userId,
            $field => $itemId
        ]);

        return [
            'status' => 'added',
            'message' => 'Item added to favorites'
        ];
    }

    public function getUserFavorites()
    {
        return Favorite::with(['product', 'service'])
            ->where('user_id', Auth::id())
            ->get();
    }
}
