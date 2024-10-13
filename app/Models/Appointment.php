<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'actual_start_time' => 'datetime',
        'actual_end_time' => 'datetime',
    ];

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
}
