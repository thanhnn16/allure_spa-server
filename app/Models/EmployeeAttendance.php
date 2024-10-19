<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="EmployeeAttendance",
 *     title="Employee Attendance",
 *     description="Employee attendance model",
 *     @OA\Property(property="id", type="integer", format="int64", description="Unique identifier"),
 *     @OA\Property(property="user_id", type="integer", format="int64", description="User ID"),
 *     @OA\Property(property="check_in", type="string", format="date-time", description="Check-in time"),
 *     @OA\Property(property="check_out", type="string", format="date-time", description="Check-out time"),
 *     @OA\Property(property="status", type="string", description="Attendance status"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Last update timestamp")
 * )
 */
class EmployeeAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'check_in', 'check_out', 'status'
    ];

    protected $casts = [
        'check_in' => 'datetime',
        'check_out' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}