<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Order",
 *     title="Đơn hàng",
 *     description="Mô hình đơn hàng",
 *     @OA\Property(property="id", type="integer", format="int64", description="ID đơn hàng"),
 *     @OA\Property(property="user_id", type="string", format="uuid", description="ID người dùng"),
 *     @OA\Property(property="total_amount", type="number", format="float", description="Tổng số tiền của đơn hàng"),
 *     @OA\Property(property="shipping_address_id", type="integer", format="int64", description="ID địa chỉ giao hàng"),
 *     @OA\Property(property="payment_method_id", type="integer", format="int64", description="ID phương thức thanh toán"),
 *     @OA\Property(property="voucher_id", type="integer", format="int64", description="ID voucher"),
 *     @OA\Property(property="discount_amount", type="number", format="float", description="Số tiền giảm giá"),
 *     @OA\Property(property="status", type="string", description="Trạng thái của đơn hàng"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Thời gian tạo"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Thời gian cập nhật cuối cùng")
 * )
 */
class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'total_amount', 'shipping_address_id', 'payment_method_id', 'voucher_id', 'discount_amount', 'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
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
