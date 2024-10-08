<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AppointmentType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['type_name', 'description'];

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function translations()
    {
        return $this->hasMany(AppointmentTypeTranslation::class);
    }
}
