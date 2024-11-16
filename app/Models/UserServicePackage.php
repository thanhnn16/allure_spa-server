<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserServicePackage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'service_id',
        'total_sessions',
        'used_sessions',
        'expiry_date',
        'is_combo',
        'combo_type',
        'order_id'
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'is_combo' => 'boolean'
    ];

    protected $appends = ['remaining_sessions', 'status'];

    public function getStatusAttribute(): string
    {
        if ($this->used_sessions >= $this->total_sessions) {
            return 'completed';
        }
        if ($this->used_sessions === 0) {
            return 'pending';
        }
        return 'active';
    }

    public function getRemainingSessionsAttribute(): int
    {
        return $this->total_sessions - $this->used_sessions;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function usageHistories()
    {
        return $this->hasMany(ServiceUsageHistory::class);
    }
}
