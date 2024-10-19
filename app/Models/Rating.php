<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="Rating",
 *     title="Đánh giá",
 *     description="Model đại diện cho đánh giá",
 *     @OA\Property(property="id", type="integer", description="ID của đánh giá"),
 *     @OA\Property(property="user_id", type="string", description="ID của người dùng đánh giá"),
 *     @OA\Property(property="rating_type", type="string", enum={"treatment", "product"}, description="Loại đánh giá"),
 *     @OA\Property(property="item_id", type="integer", description="ID của mục được đánh giá"),
 *     @OA\Property(property="stars", type="integer", description="Số sao đánh giá"),
 *     @OA\Property(property="comment", type="string", description="Bình luận đánh giá"),
 *     @OA\Property(property="image_id", type="integer", description="ID của hình ảnh đính kèm"),
 *     @OA\Property(property="video_id", type="integer", description="ID của video đính kèm"),
 *     @OA\Property(property="status", type="string", enum={"pending", "approved", "rejected"}, description="Trạng thái đánh giá"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Thời gian tạo"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Thời gian cập nhật cuối cùng"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true, description="Thời gian xóa mềm")
 * )
 */
class Rating extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'rating_type',
        'item_id',
        'stars',
        'comment',
        'image_id',
        'video_id',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function treatment()
    {
        return $this->belongsTo(Treatment::class);
    }

    public function image()
    {
        return $this->belongsTo(Image::class);
    }

    public function video()
    {
        return $this->belongsTo(Video::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}
