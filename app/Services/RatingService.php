<?php

namespace App\Services;

use App\Models\Rating;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Order;

class RatingService
{
    public function getAllRatings(array $params = [])
    {
        return $this->getRatingsQuery($params)->paginate($params['per_page'] ?? 15);
    }

    public function getRatingsByProduct($productId, array $params = [])
    {
        return $this->getRatingsQuery($params)
            ->where('rating_type', 'product')
            ->where('item_id', $productId)
            ->paginate($params['per_page'] ?? 15);
    }

    public function getRatingsByService($serviceId, array $params = [])
    {
        return $this->getRatingsQuery($params)
            ->where('rating_type', 'service')
            ->where('item_id', $serviceId)
            ->paginate($params['per_page'] ?? 15);
    }

    public function createRating(array $data)
    {
        return Rating::create($data);
    }

    protected function getRatingsQuery(array $params = []): Builder
    {
        $query = Rating::query()->with(['user', 'media']);

        if (isset($params['status'])) {
            $query->where('status', $params['status']);
        }

        if (isset($params['sort_by']) && $params['sort_by'] === 'stars') {
            $query->orderBy('stars', $params['sort_direction'] ?? 'desc');
        } else {
            $query->latest();
        }

        return $query;
    }

    public function createRatingFromOrder(array $data, $userId)
    {
        // Check if user has purchased the item
        $order = Order::whereHas('items', function ($query) use ($data) {
            $query->where('item_type', $data['rating_type'])
                ->where('item_id', $data['item_id']);
        })
        ->where('user_id', $userId)
        ->where('status', 'completed')
        ->first();

        if (!$order) {
            throw new \Exception('Bạn chỉ có thể đánh giá sản phẩm/dịch vụ đã mua', 403);
        }

        // Check if user has already rated this item
        $existingRating = Rating::where([
            'user_id' => $userId,
            'rating_type' => $data['rating_type'],
            'item_id' => $data['item_id']
        ])->first();

        if ($existingRating) {
            throw new \Exception('Bạn đã đánh giá sản phẩm/dịch vụ này rồi', 403);
        }

        return Rating::create(array_merge($data, [
            'user_id' => $userId,
            'status' => 'pending'
        ]));
    }

    public function updateRating(Rating $rating, array $data)
    {
        $rating->update(array_merge($data, [
            'is_edited' => true
        ]));
        return $rating->fresh();
    }
}
