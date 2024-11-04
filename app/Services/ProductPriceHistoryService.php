<?php

namespace App\Services;

use App\Models\ProductPriceHistory;
use Carbon\Carbon;

class ProductPriceHistoryService
{
    public function createPriceHistory($productId, $price)
    {
        return ProductPriceHistory::create([
            'product_id' => $productId,
            'price' => $price,
            'effective_from' => now(),
        ]);
    }

    public function closeCurrentPriceHistory($productId)
    {
        return ProductPriceHistory::where('product_id', $productId)
            ->whereNull('effective_to')
            ->update(['effective_to' => now()]);
    }

    public function getPriceHistory($productId)
    {
        return ProductPriceHistory::where('product_id', $productId)
            ->orderBy('effective_from', 'desc')
            ->get();
    }

    public function getCurrentPrice($productId)
    {
        return ProductPriceHistory::where('product_id', $productId)
            ->whereNull('effective_to')
            ->first();
    }
}
