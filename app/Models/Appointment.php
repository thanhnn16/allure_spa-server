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
        'appointment_date',
        'start_time',
        'end_time',
        'status',
        'note'
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
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

    public function treatmentCombo()
    {
        return $this->belongsTo(TreatmentCombo::class);
    }
}
