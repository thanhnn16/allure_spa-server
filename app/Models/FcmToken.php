<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="FcmToken", 
 *     title="Token FCM",
 *     description="Model Token FCM",
 *     @OA\Property(property="id", type="integer", format="int64", description="ID của Token FCM"),
 *     @OA\Property(property="user_id", type="integer", format="int64", description="ID của người dùng"),
 *     @OA\Property(property="token", type="string", description="Token FCM"),
 *     @OA\Property(property="device_type", type="string", description="Loại thiết bị"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Ngày tạo"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Ngày cập nhật cuối cùng")
 * )
 */
class FcmToken extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'token', 'device_type'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
