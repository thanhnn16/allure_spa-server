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

    public function getRatingsByTreatment($treatmentId, array $params = [])
    {
        return $this->getRatingsQuery($params)
            ->where('rating_type', 'treatment')
            ->where('item_id', $treatmentId)
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
