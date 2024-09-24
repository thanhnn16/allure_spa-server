<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'usage', 'benefits', 'key_ingredients', 'ingredients', 
        'directions', 'storage_instructions', 'product_notes'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
