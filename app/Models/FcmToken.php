<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="FcmToken",
 *     title="FCM Token",
 *     description="FCM Token model",
 *     @OA\Property(property="id", type="integer", format="int64", description="FCM Token ID"),
 *     @OA\Property(property="user_id", type="integer", format="int64", description="User ID"),
 *     @OA\Property(property="token", type="string", description="FCM Token"),
 *     @OA\Property(property="device_type", type="string", description="Device Type"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation Date"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Last Update Date")
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