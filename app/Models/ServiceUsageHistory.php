<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="ServiceUsageHistory",
 *     title="Service Usage History",
 *     description="Model representing a service usage history",
 *     @OA\Property(property="id", type="integer", description="The unique identifier of the service usage history"),
 *     @OA\Property(property="user_service_package_id", type="integer", description="The ID of the associated user service package"),
 *     @OA\Property(property="used_date", type="string", format="date-time", description="The date and time when the service was used"),
 *     @OA\Property(property="staff_user_id", type="integer", description="The ID of the staff user who performed the service"),
 *     @OA\Property(property="note", type="string", description="Additional notes about the service usage"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp of when the record was created"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Timestamp of when the record was last updated")
 * )
 */
class ServiceUsageHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_service_package_id', 'used_date', 'staff_user_id', 'note'
    ];

    protected $casts = [
        'used_date' => 'datetime',
    ];

    public function userServicePackage()
    {
        return $this->belongsTo(UserServicePackage::class);
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_user_id');
    }
}