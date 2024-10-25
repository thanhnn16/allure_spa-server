<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Favorite",
 *     title="Favorite",
 *     description="Model đại diện cho mục yêu thích của người dùng",
 *     @OA\Property(property="id", type="integer", description="ID của mục yêu thích"),
 *     @OA\Property(property="user_id", type="integer", description="ID của người dùng"),
 *     @OA\Property(property="product_id", type="integer", description="ID của sản phẩm (nếu có)"),
 *     @OA\Property(property="service_id", type="integer", description="ID của dịch vụ (nếu có)"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Thời gian tạo"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Thời gian cập nhật cuối cùng")
 * )
 */
class Favorite extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'product_id', 'service_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}