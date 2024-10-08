<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItemType extends Model
{
    use HasFactory;

    protected $fillable = ['type_name'];

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
