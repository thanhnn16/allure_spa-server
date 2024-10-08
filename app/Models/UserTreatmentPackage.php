<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTreatmentPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'treatment_id',
        'combo_type',
        'purchase_date',
        'total_sessions',
        'remaining_sessions',
        'expiry_date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function treatment()
    {
        return $this->belongsTo(Treatment::class);
    }

    public function usageHistory()
    {
        return $this->hasMany(TreatmentUsageHistory::class);
    }
}
