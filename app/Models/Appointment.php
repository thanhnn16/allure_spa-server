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
 *     @OA\Property(property="service_id", type="integer", format="int64", description="Service ID"),
 *     @OA\Property(property="staff_user_id", type="integer", format="int64", description="Staff User ID"),
 *     @OA\Property(property="appointment_date", type="string", format="date", description="Appointment date"),
 *     @OA\Property(property="time_slot_id", type="integer", format="int64", description="Time Slot ID"),
 *     @OA\Property(property="appointment_type", type="string", description="Type of appointment"),
 *     @OA\Property(property="status", type="string", description="Appointment status"),
 *     @OA\Property(property="note", type="string", description="Appointment note")
 * )
 */
class Appointment extends Model
{
    use HasFactory, SoftDeletes;

    // Thêm constant cho appointment types
    const APPOINTMENT_TYPES = [
        'facial',
        'massage', 
        'weight_loss',
        'hair_removal',
        'consultation',
        'others'
    ];

    protected $fillable = [
        'user_id',
        'service_id',
        'staff_user_id',
        'appointment_date',
        'time_slot_id',
        'appointment_type',
        'status',
        'note',
        'slots',
        'cancelled_by',
        'cancelled_at',
        'cancellation_note'
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'cancelled_at' => 'datetime',
    ];

    protected $attributes = [
        'appointment_type' => 'others',
        'status' => 'pending',
        'slots' => 1,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_user_id');
    }

    public function timeSlot()
    {
        return $this->belongsTo(TimeSlot::class);
    }

    public function getAppointmentTypeAttribute($value)
    {
        return $value ?? 'others';
    }

    public function setAppointmentTypeAttribute($value)
    {
        $value = strtolower(str_replace(' ', '_', $value));
        $this->attributes['appointment_type'] = in_array($value, self::APPOINTMENT_TYPES) 
            ? $value 
            : 'others';
    }

    public function getStatusAttribute($value)
    {
        return ucfirst($value);
    }

    public function setStatusAttribute($value)
    {
        $this->attributes['status'] = strtolower($value);
    }

    public function getFullScheduleAttribute()
    {
        return [
            'date' => $this->appointment_date,
            'start_time' => $this->timeSlot->start_time,
            'end_time' => $this->timeSlot->end_time
        ];
    }

    /**
     * Get formatted appointment attribute.
     *
     * @return array
     */
    public function getFormattedAppointmentAttribute(): array
    {
        // Ensure relationships are loaded
        $this->loadMissing(['service', 'staff', 'timeSlot']);

        // Combine appointment date with time slot times
        $startDateTime = Carbon::parse($this->appointment_date)
            ->setTimeFromTimeString($this->timeSlot?->start_time ?? '00:00:00')
            ->setTimezone('Asia/Ho_Chi_Minh');

        $endDateTime = Carbon::parse($this->appointment_date)
            ->setTimeFromTimeString($this->timeSlot?->end_time ?? '00:00:00')
            ->setTimezone('Asia/Ho_Chi_Minh');

        return [
            'id' => $this->id,
            'appointment_date' => $this->appointment_date,
            'start' => $startDateTime->format('Y-m-d H:i:s'),
            'end' => $endDateTime->format('Y-m-d H:i:s'),
            'service' => [
                'id' => $this->service?->id,
                'name' => $this->service?->name,
                'price' => $this->service?->price,
                // Add other needed service fields
            ],
            'staff' => $this->staff ? [
                'id' => $this->staff->id,
                'full_name' => $this->staff->full_name,
                // Add other needed staff fields
            ] : null,
            'time_slot' => $this->timeSlot ? [
                'id' => $this->timeSlot->id,
                'start_time' => $this->timeSlot->start_time,
                'end_time' => $this->timeSlot->end_time,
                'max_bookings' => $this->timeSlot->max_bookings,
            ] : null,
            'status' => $this->status,
            'appointment_type' => $this->appointment_type,
            'note' => $this->note,
            'slots' => $this->slots,
            // Add cancellation fields
            'cancelled_by' => $this->cancelled_by,
            'cancelled_at' => $this->cancelled_at ? $this->cancelled_at->format('Y-m-d H:i:s') : null,
            'cancellation_note' => $this->cancellation_note,
        ];
    }

    /**
     * Scope to get appointments with all necessary relations
     */
    public function scopeWithFullDetails($query)
    {
        return $query->with(['user', 'service', 'staff', 'timeSlot', 'cancelledBy']);
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }
}
