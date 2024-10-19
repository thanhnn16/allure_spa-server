<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="History",
 *     title="History",
 *     description="Model đại diện cho lịch sử hoạt động",
 *     @OA\Property(property="id", type="integer", format="int64", description="ID của bản ghi lịch sử"),
 *     @OA\Property(property="user_id", type="integer", format="int64", description="ID của người dùng thực hiện hành động"),
 *     @OA\Property(property="action", type="string", description="Hành động được thực hiện"),
 *     @OA\Property(property="description", type="string", description="Mô tả chi tiết về hành động"),
 *     @OA\Property(property="model_type", type="string", description="Loại model liên quan đến hành động"),
 *     @OA\Property(property="model_id", type="integer", format="int64", description="ID của model liên quan"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Thời gian tạo bản ghi"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Thời gian cập nhật bản ghi")
 * )
 */
class History extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'action', 'description', 'model_type', 'model_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function model()
    {
        return $this->morphTo();
    }
}