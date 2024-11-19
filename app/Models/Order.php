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
 *     @OA\Property(property="cancelled_by_user_id", type="string", format="uuid", nullable=true, description="ID of user who cancelled the order"),
 *     @OA\Property(property="cancelled_at", type="string", format="date-time", nullable=true, description="Cancellation timestamp"),
 *     @OA\Property(property="cancel_reason", type="string", nullable=true, description="Reason for cancellation"),
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
        'note',
        'cancelled_by_user_id',
        'cancelled_at',
        'cancel_reason'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'cancelled_at'
    ];

    const STATUS_PENDING = 'pending';     // Chờ xử lý
    const STATUS_CONFIRMED = 'confirmed'; // Đã xác nhận
    const STATUS_SHIPPING = 'shipping';   // Đang giao hàng
    const STATUS_COMPLETED = 'completed'; // Hoàn thành
    const STATUS_CANCELLED = 'cancelled'; // Đã hủy

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class)->with(['product', 'service']);
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

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by_user_id');
    }

    // Helper methods
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isConfirmed()
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    public function isShipping()
    {
        return $this->status === self::STATUS_SHIPPING;
    }

    public function isCompleted()
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancelled()
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    // Thêm accessor để tính tổng số lượng items
    public function getTotalItemsAttribute()
    {
        return $this->orderItems->sum('quantity');
    }

    // Thêm accessor để tính tổng tiền trước giảm giá
    public function getSubtotalAttribute()
    {
        return $this->orderItems->sum(function ($item) {
            return $item->price * $item->quantity;
        });
    }

    // Thêm accessor để tính tổng tiền sau giảm giá
    public function getFinalTotalAttribute()
    {
        return $this->subtotal - ($this->discount_amount ?? 0);
    }

    public function recalculateTotal()
    {
        $subtotal = $this->orderItems->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        $this->total_amount = $subtotal;
        $this->save();

        return $this;
    }

    public function canBeCancelled()
    {
        return in_array($this->status, ['pending', 'confirmed']) &&
            (!$this->invoice || $this->invoice->status !== Invoice::STATUS_PAID);
    }

    public function canBeCompleted()
    {
        // Kiểm tra điều kiện cơ bản
        if (!$this->invoice || $this->invoice->status !== Invoice::STATUS_PAID) {
            return false;
        }

        // Kiểm tra có service combo không
        $hasServiceCombo = $this->orderItems()
            ->where('item_type', 'service')
            ->whereIn('service_type', ['combo_5', 'combo_10'])
            ->exists();

        // Nếu không có service combo, return true
        if (!$hasServiceCombo) {
            return true;
        }

        // Kiểm tra đã có service package chưa bằng query builder
        $hasServicePackages = UserServicePackage::where('order_id', $this->id)->exists();

        // Trả về true nếu chưa có service package
        return !$hasServicePackages;
    }

    public function getUnratedItems()
    {
        return $this->orderItems()->whereDoesntHave('ratings', function ($query) {
            $query->where('user_id', $this->user_id);
        })->get();
    }
}
