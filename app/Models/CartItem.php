<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    const TYPE_TREATMENT = 'treatment';
    const TYPE_PRODUCT = 'product';

    protected $fillable = [
        'cart_id',
        'item_type',
        'item_id',
        'quantity',
        'price'
    ];

    protected $casts = [
        'item_type' => 'string',
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function item()
    {
        return $this->morphTo();
    }

    public function getTotalPriceAttribute()
    {
        return $this->quantity * $this->price;
    }

    public function scopeTreatments($query)
    {
        return $query->where('item_type', self::TYPE_TREATMENT);
    }

    public function scopeProducts($query)
    {
        return $query->where('item_type', self::TYPE_PRODUCT);
    }
}