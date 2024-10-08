<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Voucher extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'description',
        'discount_type',
        'is_unlimited',
        'status',
        'used_times',
        'discount_value',
        'start_date',
        'end_date'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_vouchers')
            ->withPivot('used_at', 'is_used');
    }

    public function translations()
    {
        return $this->hasMany(VoucherTranslation::class);
    }
}
