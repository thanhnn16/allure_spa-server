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
 *     @OA\Property(property="start_time", type="string", format="date-time", description="The start time when the service was used"),
 *     @OA\Property(property="end_time", type="string", format="date-time", description="The end time when the service was used"),
 *     @OA\Property(property="staff_user_id", type="string", description="The ID of the staff user who performed the service"),
 *     @OA\Property(property="result", type="string", description="Result of the service usage"),
 *     @OA\Property(property="notes", type="string", description="Additional notes about the service usage"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp of when the record was created"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Timestamp of when the record was last updated")
 * )
 */
class ServiceUsageHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_service_package_id',
        'start_time',
        'end_time',
        'staff_user_id',
        'result',
        'notes',
        'appointment_id'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    protected $with = ['staff'];

    public function userServicePackage()
    {
        return $this->belongsTo(UserServicePackage::class);
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_user_id')
            ->select(['id', 'full_name', 'phone_number', 'email']);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}
