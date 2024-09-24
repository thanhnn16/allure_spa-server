<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_type_id', 'item_id', 'quantity', 'price', 'discount_amount'
    ];

    public function itemType()
    {
        return $this->belongsTo(CartItemType::class, 'item_type_id');
    }
}
