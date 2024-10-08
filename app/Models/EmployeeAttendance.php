<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_id',
        'check_in',
        'check_out',
        'status'
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
}
