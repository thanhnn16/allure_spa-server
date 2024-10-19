<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="PaymentHistory",
 *     title="Payment History",
 *     description="Payment History model",
 *     @OA\Property(property="id", type="integer", format="int64", description="Payment history ID"),
 *     @OA\Property(property="invoice_id", type="integer", format="int64", description="Related invoice ID"),
 *     @OA\Property(property="amount", type="number", format="float", description="Payment amount"),
 *     @OA\Property(property="payment_method", type="string", description="Payment method used"),
 *     @OA\Property(property="transaction_id", type="string", description="Transaction ID"),
 *     @OA\Property(property="status", type="string", description="Payment status"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation date"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Last update date")
 * )
 */
class PaymentHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id', 'amount', 'payment_method', 'transaction_id', 'status'
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}