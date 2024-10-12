<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Voucher extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'type', 'value', 'start_date', 'end_date',
        'min_purchase_amount', 'max_discount_amount', 'usage_limit',
        'used_count', 'is_active'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function userVouchers()
    {
        return $this->hasMany(UserVoucher::class);
    }

    public function translations()
    {
        return $this->hasMany(VoucherTranslation::class);
    }
}