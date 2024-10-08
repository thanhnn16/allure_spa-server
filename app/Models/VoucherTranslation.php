<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoucherTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_id',
        'language',
        'description'
    ];

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }
}
