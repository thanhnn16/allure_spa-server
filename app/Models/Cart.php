<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function treatments()
    {
        return $this->cartItems()->treatments();
    }

    public function products()
    {
        return $this->cartItems()->products();
    }

    public function getTotalItemsAttribute()
    {
        return $this->cartItems->sum('quantity');
    }

    public function getTotalPriceAttribute()
    {
        return $this->cartItems->sum('total_price');
    }
}
