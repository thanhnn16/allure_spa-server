<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'staff_user_id', 'total_amount', 'paid_amount',
        'status', 'payment_method', 'note', 'created_by_user_id'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_user_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function payments()
    {
        return $this->hasMany(InvoicePayment::class);
    }

    public function paymentHistories()
    {
        return $this->hasMany(PaymentHistory::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
