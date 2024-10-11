<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_id',
        'voucher_id',
        'staff_id',
        'payment_method_id',
        'created_by',
        'invoice_number',
        'total_amount',
        'discount_amount',
        'payment_status',
        'note',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(Staff::class, 'created_by');
    }
}
