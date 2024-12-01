<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Service;

class SearchService
{
    public function search($query, $params = [])
    {
        if (is_string($params)) {
            $params = [
                'type' => $params,
                'limit' => func_get_arg(2) ?? 10
            ];
        }

        $type = $params['type'] ?? 'all';
        $limit = $params['limit'] ?? 10;
        $sortBy = $params['sort_by'] ?? null;
        $minPrice = $params['min_price'] ?? null;
        $maxPrice = $params['max_price'] ?? null;
        $categoryId = $params['category_id'] ?? null;

        $results = [];

        if ($type === 'all' || $type === 'products') {
            $products = Product::query()
                ->with(['media', 'category'])
                ->when($query, function ($q) use ($query) {
                    $q->where('name', 'LIKE', "%{$query}%");
                })
                ->when($categoryId, function ($q) use ($categoryId) {
                    $q->where('category_id', $categoryId);
                })
                ->when($minPrice, function ($q) use ($minPrice) {
                    $q->where('price', '>=', $minPrice);
                })
                ->when($maxPrice, function ($q) use ($maxPrice) {
                    $q->where('price', '<=', $maxPrice);
                })
                ->when($sortBy, function ($q) use ($sortBy) {
                    switch ($sortBy) {
                        case 'name_asc':
                            $q->orderBy('name', 'asc');
                            break;
                        case 'name_desc':
                            $q->orderBy('name', 'desc');
                            break;
                        case 'price_asc':
                            $q->orderBy('price', 'asc');
                            break;
                        case 'price_desc':
                            $q->orderBy('price', 'desc');
                            break;
                        case 'rating':
                            $q->orderBy('average_rating', 'desc');
                            break;
                    }
                })
                ->take($limit)
                ->get();

            $results['products'] = $products;
        }

        if ($type === 'all' || $type === 'services') {
            $services = Service::query()
                ->with(['media', 'category'])
                ->when($query, function ($q) use ($query) {
                    $q->where('service_name', 'LIKE', "%{$query}%");
                })
                ->when($categoryId, function ($q) use ($categoryId) {
                    $q->where('category_id', $categoryId);
                })
                ->when($minPrice, function ($q) use ($minPrice) {
                    $q->where('single_price', '>=', $minPrice);
                })
                ->when($maxPrice, function ($q) use ($maxPrice) {
                    $q->where('single_price', '<=', $maxPrice);
                })
                ->when($sortBy, function ($q) use ($sortBy) {
                    switch ($sortBy) {
                        case 'name_asc':
                            $q->orderBy('service_name', 'asc');
                            break;
                        case 'name_desc':
                            $q->orderBy('service_name', 'desc');
                            break;
                        case 'price_asc':
                            $q->orderBy('single_price', 'asc');
                            break;
                        case 'price_desc':
                            $q->orderBy('single_price', 'desc');
                            break;
                        case 'rating':
                            $q->orderBy('average_rating', 'desc');
                            break;
                    }
                })
                ->take($limit)
                ->get();

            $results['services'] = $services;
        }

        return $results;
    }
}
