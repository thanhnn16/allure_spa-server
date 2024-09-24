<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTreatmentPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'treatment_combo_id', 'purchase_date', 'total_sessions', 
        'remaining_sessions', 'expiry_date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function treatmentCombo()
    {
        return $this->belongsTo(TreatmentCombo::class, 'treatment_combo_id');
    }
}
