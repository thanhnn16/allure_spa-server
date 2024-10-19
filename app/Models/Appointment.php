<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

/**
 * @OA\Schema(
 *     schema="Appointment",
 *     title="Appointment",
 *     description="Appointment model",
 *     @OA\Property(property="id", type="integer", format="int64", description="Appointment ID"),
 *     @OA\Property(property="user_id", type="integer", format="int64", description="User ID"),
 *     @OA\Property(property="treatment_id", type="integer", format="int64", description="Treatment ID"),
 *     @OA\Property(property="staff_user_id", type="integer", format="int64", description="Staff User ID"),
 *     @OA\Property(property="start_time", type="string", format="date-time", description="Appointment start time"),
 *     @OA\Property(property="end_time", type="string", format="date-time", description="Appointment end time"),
 *     @OA\Property(property="actual_start_time", type="string", format="date-time", nullable=true, description="Actual start time"),
 *     @OA\Property(property="actual_end_time", type="string", format="date-time", nullable=true, description="Actual end time"),
 *     @OA\Property(property="appointment_type", type="string", description="Type of appointment"),
 *     @OA\Property(property="status", type="string", description="Appointment status"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation date"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Last update date"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true, description="Deletion date"),
 *     @OA\Property(property="note", type="string", description="Appointment note")
 * )
 */
class Appointment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'treatment_id',
        'staff_user_id',
        'start_time',
        'end_time',
        'actual_start_time',
        'actual_end_time',
        'appointment_type',
        'status',
        'note',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'actual_start_time' => 'datetime',
        'actual_end_time' => 'datetime',
    ];

    protected $appends = ['formatted_start_time', 'formatted_end_time'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function treatment()
    {
        return $this->belongsTo(Treatment::class);
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_user_id');
    }

    protected $attributes = [
        'appointment_type' => 'others',
        'status' => 'pending',
    ];

    public function getAppointmentTypeAttribute($value)
    {
        return ucfirst(str_replace('_', ' ', $value));
    }

    public function setAppointmentTypeAttribute($value)
    {
        $this->attributes['appointment_type'] = strtolower(str_replace(' ', '_', $value));
    }

    public function getStatusAttribute($value)
    {
        return ucfirst($value);
    }

    public function setStatusAttribute($value)
    {
        $this->attributes['status'] = strtolower($value);
    }

    public function getFormattedStartTimeAttribute()
    {
        return $this->start_time->toIso8601String();
    }

    public function getFormattedEndTimeAttribute()
    {
        return $this->end_time->toIso8601String();
    }

    protected function serializeDate(\DateTimeInterface $date)
    {
        return Carbon::instance($date)->toIso8601String();
    }
}
