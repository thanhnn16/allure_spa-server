<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'movement_type', 'quantity', 'unit_price', 'note'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
