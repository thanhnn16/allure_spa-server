<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TreatmentUsageHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_treatment_package_id', 'treatment_date', 'staff_id', 'notes'
    ];

    public function userTreatmentPackage()
    {
        return $this->belongsTo(UserTreatmentPackage::class, 'user_treatment_package_id');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }
}
