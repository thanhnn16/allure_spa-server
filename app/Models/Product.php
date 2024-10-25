<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="Product",
 *     required={"name", "price", "category_id", "quantity"},
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="price", type="number", format="float"),
 *     @OA\Property(property="category_id", type="integer"),
 *     @OA\Property(property="quantity", type="integer"),
 *     @OA\Property(property="brand_description", type="string", nullable=true),
 *     @OA\Property(property="usage", type="string", nullable=true),
 *     @OA\Property(property="benefits", type="string", nullable=true),
 *     @OA\Property(property="key_ingredients", type="string", nullable=true),
 *     @OA\Property(property="ingredients", type="string", nullable=true),
 *     @OA\Property(property="directions", type="string", nullable=true),
 *     @OA\Property(property="storage_instructions", type="string", nullable=true),
 *     @OA\Property(property="product_notes", type="string", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true)
 * )
 */
class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'price', 'category_id', 'quantity', 'brand_description', 'usage', 'benefits',
        'key_ingredients', 'ingredients', 'directions', 'storage_instructions', 'product_notes'
    ];

    protected $morphClass = 'product';

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function media()
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    public function priceHistory()
    {
        return $this->hasMany(ProductPriceHistory::class);
    }

    public function attributes()
    {
        return $this->belongsToMany(Attribute::class, 'product_attributes')
            ->withPivot('attribute_value');
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function orderItems()
    {
        return $this->morphMany(OrderItem::class, 'item');
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }
}
