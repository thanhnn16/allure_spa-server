<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'item_type_id',
        'item_id',
        'quantity',
        'price',
        'discount_amount',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function itemType()
    {
        return $this->belongsTo(CartItemType::class, 'item_type_id');
    }

    public function item()
    {
        return $this->morphTo();
    }
}
