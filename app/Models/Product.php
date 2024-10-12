<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'price', 'category_id', 'image_id', 'quantity'
    ];

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function image()
    {
        return $this->belongsTo(Image::class);
    }

    public function productDetail()
    {
        return $this->hasOne(ProductDetail::class);
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

    public function translations()
    {
        return $this->hasMany(ProductTranslation::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }
}
