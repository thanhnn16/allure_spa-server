<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="StockMovement",
 *     title="Stock Movement",
 *     description="Model representing a stock movement",
 *     @OA\Property(property="id", type="integer", description="The stock movement ID"),
 *     @OA\Property(property="product_id", type="integer", description="The associated product ID"),
 *     @OA\Property(property="quantity", type="integer", description="The quantity of the stock movement"),
 *     @OA\Property(property="type", type="string", description="The type of stock movement"),
 *     @OA\Property(property="reason", type="string", description="The reason for the stock movement"),
 *     @OA\Property(property="reference_id", type="integer", description="The ID of the referenced entity"),
 *     @OA\Property(property="reference_type", type="string", description="The type of the referenced entity"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Last update timestamp")
 * )
 */
class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'quantity', 'type', 'reason', 'reference_id', 'reference_type'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }
}