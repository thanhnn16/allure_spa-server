<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'description', 'discount_type', 'is_unlimited', 'status', 
        'used_times', 'discount_value', 'start_date', 'end_date'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_vouchers')
                    ->withPivot('used_at', 'is_used')
                    ->withTimestamps();
    }
}
