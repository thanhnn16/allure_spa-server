<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'product_name',
        'product_line',
        'brand_description',
        'description',
        'price',
        'volume',
        'stock_quantity',
        'image_id',
        'usage',
        'benefits',
        'key_ingredients',
        'ingredients',
        'directions',
        'storage_instructions',
        'product_notes'
    ];

    public function category()
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function attributes()
    {
        return $this->belongsToMany(Attribute::class, 'product_attributes')
            ->withPivot('attribute_value');
    }

    public function image()
    {
        return $this->belongsTo(Image::class);
    }

    public function translations()
    {
        return $this->hasMany(ProductTranslation::class);
    }
}
