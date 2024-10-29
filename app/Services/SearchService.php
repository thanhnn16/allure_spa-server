<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Service;

class SearchService
{
    public function search($query, $type = 'all', $limit = 10)
    {
        $results = [];

        if ($type === 'all' || $type === 'products') {
            $products = Product::where('name', 'LIKE', "%{$query}%")
                ->with(['media', 'category'])
                ->take($limit)
                ->get();
            $results['products'] = $products;
        }

        if ($type === 'all' || $type === 'services') {
            $services = Service::where('service_name', 'LIKE', "%{$query}%")
                ->with(['media', 'category'])
                ->take($limit)
                ->get();
            $results['services'] = $services;
        }

        return $results;
    }
}
