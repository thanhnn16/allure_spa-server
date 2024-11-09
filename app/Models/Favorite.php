<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Response(
 *     response="FavoriteResponse",
 *     description="Favorite model",
 *     @OA\JsonContent(
 *         type="object",
 *         @OA\Property(property="id", type="integer", description="ID của mục yêu thích"),
 *         @OA\Property(property="favorite_type", type="string", enum={"product","service"}, description="Loại mục yêu thích"),
 *         @OA\Property(property="item_id", type="integer", description="ID của sản phẩm hoặc dịch vụ"),
 *         @OA\Property(property="user_id", type="string", description="ID của người dùng"),
 *         @OA\Property(property="created_at", type="string", format="date-time", description="Thời gian tạo"),
 *         @OA\Property(property="updated_at", type="string", format="date-time", description="Thời gian cập nhật cuối cùng")
 *     )
 * )
 */
class Favorite extends Model
{
    use HasFactory;

    protected $fillable = [
        'favorite_type',
        'item_id',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'item_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'item_id');
    }
}
