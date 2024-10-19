<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="Invoice",
 *     title="Invoice",
 *     description="Invoice model",
 *     @OA\Property(property="id", type="integer", format="int64", description="Invoice ID"),
 *     @OA\Property(property="user_id", type="integer", format="int64", description="User ID"),
 *     @OA\Property(property="staff_user_id", type="integer", format="int64", description="Staff User ID"),
 *     @OA\Property(property="total_amount", type="number", format="float", description="Total amount"),
 *     @OA\Property(property="paid_amount", type="number", format="float", description="Paid amount"),
 *     @OA\Property(property="status", type="string", description="Invoice status"),
 *     @OA\Property(property="payment_method", type="string", description="Payment method"),
 *     @OA\Property(property="note", type="string", description="Invoice note"),
 *     @OA\Property(property="created_by_user_id", type="integer", format="int64", description="Created by User ID"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation date"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Last update date"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true, description="Deletion date")
 * )
 */
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
