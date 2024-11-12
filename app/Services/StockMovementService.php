<?php

namespace App\Services;

use App\Models\StockMovement;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Models\User;

class StockMovementService
{
    /**
     * Create a new stock movement
     */
    public function createMovement(Product $product, int $quantity, string $type, string $note = null)
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be greater than 0');
        }

        try {
            DB::beginTransaction();

            $product = Product::lockForUpdate()->find($product->id);

            $newQuantity = $type === 'in'
                ? $product->quantity + $quantity
                : $product->quantity - $quantity;

            if ($type === 'out' && $newQuantity < 0) {
                throw new \Exception('Insufficient stock available');
            }

            $product->quantity = $newQuantity;
            $product->save();

            $movement = StockMovement::create([
                'product_id' => $product->id,
                'quantity' => $quantity,
                'type' => $type,
                'stock_after_movement' => $newQuantity,
                'note' => $note
            ]);

            DB::commit();
            return $movement;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Check if product has sufficient stock
     */
    public function hasSufficientStock(Product $product, int $requestedQuantity): bool
    {
        return $product->quantity >= $requestedQuantity;
    }

    public function recalculateProductStock(Product $product)
    {
        $lastValidMovement = $product->stockMovements()
            ->orderBy('created_at', 'desc')
            ->first();

        if ($lastValidMovement) {
            $product->quantity = $lastValidMovement->stock_after_movement;
            $product->save();
        }
    }
}
