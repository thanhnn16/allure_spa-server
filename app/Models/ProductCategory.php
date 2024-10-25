<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="ProductCategory",
 *     title="Danh mục sản phẩm",
 *     description="Mô hình danh mục sản phẩm",
 *     @OA\Property(property="id", type="integer", format="int64", description="Định danh duy nhất"),
 *     @OA\Property(property="category_name", type="string", description="Tên của danh mục"),
 *     @OA\Property(property="parent_id", type="integer", format="int64", nullable=true, description="ID của danh mục cha"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Thời gian tạo"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Thời gian cập nhật cuối cùng"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true, description="Thời gian xóa")
 * )
 */
class ProductCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['category_name', 'parent_id'];

    public function parent()
    {
        return $this->belongsTo(ProductCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ProductCategory::class, 'parent_id');
    }

    public function mediable()
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }
}