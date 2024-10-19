<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="ProductPriceHistory",
 *     title="Lịch sử giá sản phẩm",
 *     description="Model đại diện cho lịch sử giá của một sản phẩm",
 *     @OA\Property(property="id", type="integer", description="ID của bản ghi lịch sử giá"),
 *     @OA\Property(property="product_id", type="integer", description="ID của sản phẩm"),
 *     @OA\Property(property="price", type="number", format="float", description="Giá của sản phẩm"),
 *     @OA\Property(property="effective_date", type="string", format="date-time", description="Ngày hiệu lực của giá"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Thời gian tạo bản ghi"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Thời gian cập nhật cuối cùng")
 * )
 */
class ProductPriceHistory extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'price', 'effective_date'];

    protected $casts = [
        'effective_date' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}