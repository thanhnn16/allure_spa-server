<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'language',
        'product_name',
        'description',
        'usage',
        'benefits',
        'key_ingredients',
        'ingredients',
        'directions',
        'storage_instructions',
        'product_notes'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
