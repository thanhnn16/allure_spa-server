<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="CartItem",
 *     title="Cart Item",
 *     description="Cart Item model",
 *     @OA\Property(property="id", type="integer", format="int64", description="Cart Item ID"),
 *     @OA\Property(property="cart_id", type="integer", format="int64", description="Cart ID"),
 *     @OA\Property(property="product_id", type="integer", format="int64", description="Product ID"),
 *     @OA\Property(property="quantity", type="integer", description="Quantity of the product in the cart"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation date"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Last update date")
 * )
 */
class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id', 'product_id', 'quantity'
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}