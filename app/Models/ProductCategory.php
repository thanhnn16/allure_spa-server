<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="ProductCategory",
 *     title="Product Category",
 *     description="Product Category model",
 *     @OA\Property(property="id", type="integer", format="int64", description="Unique identifier"),
 *     @OA\Property(property="name", type="string", description="Name of the category"),
 *     @OA\Property(property="parent_id", type="integer", format="int64", nullable=true, description="ID of the parent category"),
 *     @OA\Property(property="image_id", type="integer", format="int64", nullable=true, description="ID of the associated image"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Last update timestamp"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true, description="Deletion timestamp")
 * )
 */
class ProductCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'parent_id', 'image_id'];

    public function parent()
    {
        return $this->belongsTo(ProductCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ProductCategory::class, 'parent_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    public function image()
    {
        return $this->belongsTo(Image::class);
    }

    public function translations()
    {
        return $this->hasMany(ProductCategoryTranslation::class);
    }
}