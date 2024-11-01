<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
 *     schema="TimeSlot",
 *     title="TimeSlot",
 *     description="TimeSlot model",
 *     @OA\Property(property="id", type="integer", format="int64", description="TimeSlot ID"),
 *     @OA\Property(property="start_time", type="string", format="time", description="Start time"),
 *     @OA\Property(property="end_time", type="string", format="time", description="End time"),
 *     @OA\Property(property="max_bookings", type="integer", description="Maximum bookings allowed"),
 *     @OA\Property(property="is_active", type="boolean", description="Slot availability status")
 * )
 */
class TimeSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'start_time',
        'end_time',
        'max_bookings',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'max_bookings' => 'integer'
    ];

    protected $appends = ['current_bookings'];

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function getCurrentBookingsAttribute()
    {
        return $this->appointments()
            ->whereDate('appointment_date', request('date'))
            ->count();
    }

    public function isAvailable($date)
    {
        $bookings = $this->appointments()
            ->whereDate('appointment_date', $date)
            ->count();

        return $bookings < $this->max_bookings;
    }
}
