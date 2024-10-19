<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="ProductAttribute",
 *     title="Product Attribute",
 *     description="Model representing a product attribute",
 *     @OA\Property(property="id", type="integer", description="The product attribute ID"),
 *     @OA\Property(property="product_id", type="integer", description="The associated product ID"),
 *     @OA\Property(property="attribute_id", type="integer", description="The associated attribute ID"),
 *     @OA\Property(property="attribute_value", type="string", description="The value of the attribute"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Last update timestamp")
 * )
 */
class ProductAttribute extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'attribute_id', 'attribute_value'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }
}
