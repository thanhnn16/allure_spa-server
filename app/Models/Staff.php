<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_name', 'position', 'hire_date', 'is_active'
    ];

    public function treatmentUsageHistories()
    {
        return $this->hasMany(TreatmentUsageHistory::class, 'staff_id');
    }

    public function employeeAttendances()
    {
        return $this->hasMany(EmployeeAttendance::class, 'staff_id');
    }
}
