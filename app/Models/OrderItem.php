<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="OrderItem",
 *     title="Order Item",
 *     description="Order Item model",
 *     @OA\Property(property="id", type="integer", format="int64", description="Order Item ID"),
 *     @OA\Property(property="order_id", type="integer", format="int64", description="Order ID"),
 *     @OA\Property(property="product_id", type="integer", format="int64", description="Product ID"),
 *     @OA\Property(property="quantity", type="integer", description="Quantity of the product"),
 *     @OA\Property(property="price", type="number", format="float", description="Price of the product"),
 *     @OA\Property(property="total", type="number", format="float", description="Total price for this item"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation date"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Last update date")
 * )
 */
class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'product_id', 'quantity', 'price', 'total'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}