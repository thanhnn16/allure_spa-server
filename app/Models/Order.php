<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Order",
 *     title="Order",
 *     description="Order model",
 *     @OA\Property(property="id", type="integer", format="int64", description="Order ID"),
 *     @OA\Property(property="user_id", type="string", format="uuid", description="User ID"),
 *     @OA\Property(property="total_amount", type="number", format="float", description="Total order amount"),
 *     @OA\Property(property="shipping_address_id", type="integer", description="Shipping address ID"),
 *     @OA\Property(property="payment_method_id", type="integer", description="Payment method ID"),
 *     @OA\Property(property="voucher_id", type="integer", nullable=true, description="Voucher ID"),
 *     @OA\Property(property="discount_amount", type="number", format="float", description="Discount amount"),
 *     @OA\Property(property="status", type="string", enum={"pending", "confirmed", "shipping", "completed", "cancelled"}, description="Order status"),
 *     @OA\Property(property="note", type="string", nullable=true, description="Order note"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Created timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Last updated timestamp"),
 *     @OA\Property(
 *         property="order_items",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/OrderItem")
 *     ),
 *     @OA\Property(property="user", ref="#/components/schemas/User"),
 *     @OA\Property(property="invoice", ref="#/components/schemas/Invoice")
 * )
 */
class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_amount',
        'shipping_address_id',
        'payment_method_id',
        'voucher_id',
        'discount_amount',
        'status',
        'note'
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function order_items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    public function shippingAddress()
    {
        return $this->belongsTo(Address::class, 'shipping_address_id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }
}
