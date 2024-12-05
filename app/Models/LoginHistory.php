<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="LoginHistory",
 *     title="Lịch sử đăng nhập",
 *     description="Model lưu trữ lịch sử đăng nhập của người dùng",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="ID của bản ghi lịch sử đăng nhập"
 *     ),
 *     @OA\Property(
 *         property="user_id", 
 *         type="integer",
 *         description="ID của người dùng"
 *     ),
 *     @OA\Property(
 *         property="ip_address",
 *         type="string",
 *         description="Địa chỉ IP đăng nhập"
 *     ),
 *     @OA\Property(
 *         property="user_agent",
 *         type="string", 
 *         description="Thông tin trình duyệt/thiết bị"
 *     ),
 *     @OA\Property(
 *         property="login_at",
 *         type="string",
 *         format="date-time",
 *         description="Thời gian đăng nhập"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         description="Trạng thái đăng nhập"
 *     ),
 *     @OA\Property(
 *         property="device_type",
 *         type="string",
 *         description="Loại thiết bị đăng nhập"
 *     ),
 *     @OA\Property(
 *         property="location",
 *         type="string",
 *         description="Vị trí đăng nhập"
 *     )
 * )
 */
class LoginHistory extends Model
{
    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'login_at',
        'status',
        'device_type',
        'location'
    ];

    protected $casts = [
        'login_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
