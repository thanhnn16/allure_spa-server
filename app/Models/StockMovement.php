<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="StockMovement", 
 *     title="Stock Movement",
 *     description="Model representing a stock movement",
 *     @OA\Property(property="id", type="integer", description="The stock movement ID"),
 *     @OA\Property(property="product_id", type="integer", description="The associated product ID"),
 *     @OA\Property(property="quantity", type="integer", description="The quantity of the stock movement"),
 *     @OA\Property(property="type", type="string", enum={"in","out"}, description="The type of stock movement"),
 *     @OA\Property(property="stock_after_movement", type="integer", description="The stock quantity after movement"),
 *     @OA\Property(property="note", type="string", description="Note about the stock movement"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Last update timestamp"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", description="Deletion timestamp")
 * )
 */
class StockMovement extends Model
{
    use HasFactory, SoftDeletes;

    public const TYPE_IN = 'in';
    public const TYPE_OUT = 'out';

    protected $fillable = [
        'product_id',
        'quantity',
        'type',
        'stock_after_movement',
        'note'
    ];

    protected $casts = [
        'type' => 'string',
        'stock_after_movement' => 'integer',
        'quantity' => 'integer'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
