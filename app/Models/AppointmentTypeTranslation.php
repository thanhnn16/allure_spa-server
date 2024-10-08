<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppointmentTypeTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_type_id',
        'language',
        'type_name',
        'description'
    ];

    public function appointmentType()
    {
        return $this->belongsTo(AppointmentType::class);
    }
}
