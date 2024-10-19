<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="PaymentMethod",
 *     title="Payment Method",
 *     description="Payment Method model",
 *     @OA\Property(property="id", type="integer", format="int64", description="Payment method ID"),
 *     @OA\Property(property="name", type="string", description="Name of the payment method"),
 *     @OA\Property(property="is_active", type="boolean", description="Whether the payment method is active"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Last update timestamp")
 * )
 */
class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function invoicePayments()
    {
        return $this->hasMany(InvoicePayment::class);
    }
}