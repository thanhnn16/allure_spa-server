<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Staff extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'full_name',
        'phone_number',
        'email',
        'password',
        'role',
        'gender',
        'date_of_birth',
        'image_id',
        'status'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    public function image()
    {
        return $this->belongsTo(Image::class);
    }

    public function attendances()
    {
        return $this->hasMany(EmployeeAttendance::class);
    }
}
