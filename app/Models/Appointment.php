<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'appointment_type',
        'staff_id',
        'order_item_id',
        'start_date',
        'end_date',
        'is_all_day',
        'status',
        'note'
    ];

    protected $casts = [
        'user_id' => 'string',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_all_day' => 'boolean',
        'appointment_type' => 'string',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }
}
