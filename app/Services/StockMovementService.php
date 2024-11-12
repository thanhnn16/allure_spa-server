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
        // Validate số lượng
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be greater than 0');
        }

        try {
            DB::beginTransaction();

            // Lock product để tránh race condition
            $product = Product::lockForUpdate()->find($product->id);

            // Calculate new stock quantity
            $newQuantity = $type === StockMovement::TYPE_IN
                ? $product->quantity + $quantity
                : $product->quantity - $quantity;

            // Check if we have enough stock for outbound movement
            if ($type === StockMovement::TYPE_OUT && $newQuantity < 0) {
                throw new \Exception('Insufficient stock available');
            }

            // Update product quantity
            $product->quantity = $newQuantity;
            $product->save();

            // Create stock movement record
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
