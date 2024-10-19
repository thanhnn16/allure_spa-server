<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="ProductDetail",
 *     title="Product Detail",
 *     description="Product Detail model",
 *     @OA\Property(property="id", type="integer", format="int64", description="Product Detail ID"),
 *     @OA\Property(property="product_id", type="integer", format="int64", description="ID of the associated product"),
 *     @OA\Property(property="description", type="string", description="Detailed description of the product"),
 *     @OA\Property(property="ingredients", type="string", description="List of ingredients"),
 *     @OA\Property(property="usage_instructions", type="string", description="Instructions for using the product"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp of creation"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Timestamp of last update")
 * )
 */
class ProductDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'description', 'ingredients', 'usage_instructions'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}