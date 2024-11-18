<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="Rating",
 *     title="Đánh giá",
 *     description="Model đại diện cho đánh giá",
 *     @OA\Property(property="id", type="integer", description="ID của đánh giá"),
 *     @OA\Property(property="user_id", type="string", description="ID của người dùng đánh giá"),
 *     @OA\Property(property="order_item_id", type="integer", description="ID của item trong đơn hàng"),
 *     @OA\Property(property="rating_type", type="string", enum={"service", "product"}, description="Loại đánh giá"),
 *     @OA\Property(property="item_id", type="integer", description="ID của mục được đánh giá"),
 *     @OA\Property(property="stars", type="integer", description="Số sao đánh giá"),
 *     @OA\Property(property="comment", type="string", description="Bình luận đánh giá"),
 *     @OA\Property(property="status", type="string", enum={"pending", "approved", "rejected"}, description="Trạng thái đánh giá"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Thời gian tạo"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Thời gian cập nhật cuối cùng"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true, description="Thời gian xóa mềm")
 * )
 */
class Rating extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'order_item_id',
        'rating_type',
        'item_id', 
        'stars',
        'comment',
        'status'
    ];

    protected $with = ['media'];
    protected $appends = ['media_urls'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function item()
    {
        return $this->morphTo(__FUNCTION__, 'rating_type', 'item_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'item_id')
            ->where('rating_type', 'product');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'item_id')
            ->where('rating_type', 'service');
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

    public function media()
    {
        return $this->morphMany(Media::class, 'mediable')
            ->orderBy('position');
    }

    public function getMediaUrlsAttribute()
    {
        return $this->media->map(function($media) {
            return [
                'id' => $media->id,
                'type' => $media->type,
                'url' => $media->full_url
            ];
        });
    }

    public function attachMedia($mediaIds)
    {
        $position = 0;
        foreach ($mediaIds as $mediaId) {
            Media::where('id', $mediaId)->update([
                'mediable_type' => 'rating',
                'mediable_id' => $this->id,
                'position' => $position++
            ]);
        }
    }
}
