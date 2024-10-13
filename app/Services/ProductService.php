<?php

namespace App\Services;

use App\Models\Product;

class ProductService
{
    public function getProducts($request)
    {
        return Product::paginate($request->input('per_page', 15));
    }

    public function searchProducts($query)
    {
        return Product::where('name', 'like', "%{$query}%")->get();
    }

    // Implement other methods as needed...
}