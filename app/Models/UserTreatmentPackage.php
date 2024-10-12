<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTreatmentPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'treatment_id', 'total_sessions', 'remaining_sessions',
        'expiry_date', 'purchase_date', 'price'
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'purchase_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function treatment()
    {
        return $this->belongsTo(Treatment::class);
    }

    public function usageHistories()
    {
        return $this->hasMany(TreatmentUsageHistory::class);
    }
}