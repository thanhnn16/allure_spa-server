<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="InvoicePayment",
 *     title="Invoice Payment",
 *     description="Model representing a payment for an invoice",
 *     @OA\Property(property="id", type="integer", description="The unique identifier of the invoice payment"),
 *     @OA\Property(property="invoice_id", type="integer", description="The ID of the associated invoice"),
 *     @OA\Property(property="payment_method_id", type="integer", description="The ID of the payment method used"),
 *     @OA\Property(property="amount", type="number", format="float", description="The amount of the payment"),
 *     @OA\Property(property="payment_date", type="string", format="date-time", description="The date and time of the payment"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="The creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="The last update timestamp")
 * )
 */
class InvoicePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id', 'payment_method_id', 'amount', 'payment_date'
    ];

    protected $casts = [
        'payment_date' => 'datetime',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }
}