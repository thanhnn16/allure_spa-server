<?php

namespace App\Services;

use App\Models\StockMovement;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class StockMovementService
{
    /**
     * Create a new stock movement
     *
     * @throws \InvalidArgumentException|\Exception
     */
    public function createMovement(Product $product, int $quantity, string $type, ?string $note = null): Model
    {
        $this->validateQuantity($quantity);

        try {
            DB::beginTransaction();

            $product = $this->lockAndLoadProduct($product);
            $newQuantity = $this->calculateNewQuantity($product, $quantity, $type);

            $this->validateStockAvailability($type, $newQuantity);

            $product->quantity = $newQuantity;
            $product->save();

            $movement = $this->createStockMovement($product, $quantity, $type, $newQuantity, $note);

            DB::commit();
            return $movement;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Validate quantity is positive
     */
    private function validateQuantity(int $quantity): void
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be greater than 0');
        }
    }

    /**
     * Lock and load product for update
     */
    private function lockAndLoadProduct(Product $product): Product
    {
        return Product::lockForUpdate()->find($product->id);
    }

    /**
     * Calculate new quantity based on movement type
     */
    private function calculateNewQuantity(Product $product, int $quantity, string $type): int
    {
        return $type === StockMovement::TYPE_IN
            ? $product->quantity + $quantity
            : $product->quantity - $quantity;
    }

    /**
     * Validate stock availability for outgoing movements
     */
    private function validateStockAvailability(string $type, int $newQuantity): void
    {
        if ($type === StockMovement::TYPE_OUT && $newQuantity < 0) {
            throw new \Exception('Insufficient stock available');
        }
    }

    /**
     * Create stock movement record
     */
    private function createStockMovement(
        Product $product,
        int $quantity,
        string $type,
        int $newQuantity,
        ?string $note
    ): Model {
        return StockMovement::create([
            'product_id' => $product->id,
            'quantity' => $quantity,
            'type' => $type,
            'stock_after_movement' => $newQuantity,
            'note' => $note
        ]);
    }

    /**
     * Check if product has sufficient stock
     */
    public function hasSufficientStock(Product $product, int $requestedQuantity): bool
    {
        return $product->quantity >= $requestedQuantity;
    }

    /**
     * Recalculate product stock based on last movement
     */
    public function recalculateProductStock(Product $product): void
    {
        $lastValidMovement = $product->stockMovements()
            ->latest()
            ->first();

        if ($lastValidMovement) {
            $product->quantity = $lastValidMovement->stock_after_movement;
            $product->save();
        }
    }
}
