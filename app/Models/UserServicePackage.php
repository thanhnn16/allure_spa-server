<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserServicePackage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'service_id',
        'total_sessions',
        'used_sessions',
        'expiry_date',
        'is_combo',
        'combo_type',
        'order_id'
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'is_combo' => 'boolean'
    ];

    protected $with = [
        'service',
        'nextAppointment',
        'nextAppointment.timeSlot',
        'nextAppointment.staff',
        'order',
        'treatmentSessions',
        'treatmentSessions.staff'
    ];

    protected $appends = [
        'remaining_sessions',
        'status',
        'package_type',
        'service_name',
        'progress_percentage',
        'formatted_expiry_date',
        'next_session_date',
        'next_appointment_details'
    ];

    public function getStatusAttribute(): string
    {
        if ($this->expiry_date && now()->isAfter($this->expiry_date)) {
            return 'expired';
        }
        if ($this->used_sessions >= $this->total_sessions) {
            return 'completed';
        }
        if ($this->used_sessions === 0) {
            return 'pending';
        }
        return 'active';
    }

    public function getRemainingSessionsAttribute(): int
    {
        return $this->total_sessions - $this->used_sessions;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class)->with('orderItems');
    }

    public function usageHistories()
    {
        return $this->hasMany(ServiceUsageHistory::class);
    }

    public function getServiceNameAttribute(): ?string
    {
        return $this->service?->service_name;
    }

    public function getPackageTypeAttribute(): array
    {
        $type = match ($this->combo_type) {
            '5_times' => [
                'name' => 'Combo 5 buổi',
                'sessions' => 5,
                'color' => 'blue'
            ],
            '10_times' => [
                'name' => 'Combo 10 buổi',
                'sessions' => 10,
                'color' => 'purple'
            ],
            default => [
                'name' => 'Đơn lẻ',
                'sessions' => 1,
                'color' => 'gray'
            ]
        };

        return $type;
    }

    public function treatmentSessions()
    {
        return $this->hasMany(ServiceUsageHistory::class)
            ->with('staff')
            ->orderBy('start_time', 'desc');
    }

    public function nextAppointment()
    {
        return $this->hasOne(Appointment::class, 'service_id', 'service_id')
            ->where('user_id', $this->user_id)
            ->where('status', '!=', 'cancelled')
            ->where('status', '!=', 'completed')
            ->where(function ($query) {
                $query->where('appointment_date', '>', now()->format('Y-m-d'))
                    ->orWhere(function ($q) {
                        $q->where('appointment_date', '=', now()->format('Y-m-d'))
                            ->whereHas('timeSlot', function ($q) {
                                $q->where('start_time', '>', now()->format('H:i:s'));
                            });
                    });
            })
            ->orderBy('appointment_date', 'asc')
            ->orderBy('time_slot_id', 'asc');
    }

    public function getProgressPercentageAttribute(): int
    {
        if (!$this->total_sessions) {
            return 0;
        }
        return round(($this->used_sessions / $this->total_sessions) * 100);
    }

    public function getFormattedExpiryDateAttribute(): ?string
    {
        return $this->expiry_date ? $this->expiry_date->format('d/m/Y') : null;
    }

    public function getNextSessionDateAttribute(): ?string
    {
        $nextAppointment = $this->nextAppointment;
        if (!$nextAppointment) {
            return null;
        }

        return $nextAppointment->appointment_date->format('d/m/Y');
    }

    public function getNextAppointmentDetailsAttribute()
    {
        if (!$this->nextAppointment) {
            return null;
        }

        return [
            'date' => $this->nextAppointment->appointment_date->format('d/m/Y'),
            'time' => [
                'start' => $this->nextAppointment->timeSlot->start_time,
                'end' => $this->nextAppointment->timeSlot->end_time
            ],
            'staff' => $this->nextAppointment->staff ? [
                'id' => $this->nextAppointment->staff->id,
                'full_name' => $this->nextAppointment->staff->full_name
            ] : null
        ];
    }
}
