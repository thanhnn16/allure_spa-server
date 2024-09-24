<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class UserVoucher extends Pivot
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'voucher_id', 'used_at', 'is_used'
    ];
}
