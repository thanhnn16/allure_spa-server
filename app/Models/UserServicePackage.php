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

    protected $with = ['service'];

    protected $appends = [
        'remaining_sessions',
        'status',
        'package_type',
        'service_name',
        'progress_percentage',
        'formatted_expiry_date',
        'next_session_date'
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
            ->orderBy('start_time', 'desc')
            ->with('staff');
    }

    public function nextAppointment()
    {
        return $this->hasOne(Appointment::class, 'service_id', 'service_id')
            ->where('user_id', $this->user_id)
            ->where('appointment_date', '>=', now())
            ->where('status', '!=', 'cancelled')
            ->orderBy('appointment_date', 'asc')
            ->orderBy('time_slot_id', 'asc');
    }

    public function getProgressPercentageAttribute(): int
    {
        return $this->total_sessions > 0
            ? round(($this->used_sessions / $this->total_sessions) * 100)
            : 0;
    }

    public function getFormattedExpiryDateAttribute(): ?string
    {
        return $this->expiry_date ? $this->expiry_date->format('d/m/Y') : null;
    }

    public function getNextSessionDateAttribute(): ?string
    {
        $nextAppointment = $this->nextAppointment;
        return $nextAppointment ? $nextAppointment->appointment_date->format('d/m/Y') : null;
    }
}
