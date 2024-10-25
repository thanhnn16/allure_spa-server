<?php

namespace App\Services;

use App\Models\Rating;
use Illuminate\Database\Eloquent\Builder;

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
        $query = Rating::query();

        if (isset($params['sort_by']) && $params['sort_by'] === 'stars') {
            $query->orderBy('stars', $params['sort_direction'] ?? 'desc');
        }

        return $query;
    }
}
