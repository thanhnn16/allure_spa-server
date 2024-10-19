<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="ProductTranslation",
 *     title="Product Translation",
 *     description="Model đại diện cho bản dịch của sản phẩm",
 *     @OA\Property(property="id", type="integer", description="ID của bản dịch"),
 *     @OA\Property(property="product_id", type="integer", description="ID của sản phẩm liên quan"),
 *     @OA\Property(property="language", type="string", description="Mã ngôn ngữ của bản dịch"),
 *     @OA\Property(property="name", type="string", description="Tên đã dịch của sản phẩm"),
 *     @OA\Property(property="description", type="string", description="Mô tả đã dịch của sản phẩm"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Thời gian tạo"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Thời gian cập nhật cuối cùng")
 * )
 */
class ProductTranslation extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'language', 'name', 'description'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}