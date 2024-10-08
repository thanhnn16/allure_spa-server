<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TreatmentUsageHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_treatment_package_id',
        'used_date',
        'staff_id',
        'note'
    ];

    public function userTreatmentPackage()
    {
        return $this->belongsTo(UserTreatmentPackage::class);
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
}
