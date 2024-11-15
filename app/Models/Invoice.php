<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="Invoice",
 *     title="Hóa đơn",
 *     description="Mô hình hóa đơn",
 *     @OA\Property(property="id", type="string", format="uuid", description="ID hóa đơn"),
 *     @OA\Property(property="user_id", type="string", format="uuid", description="ID người dùng"),
 *     @OA\Property(property="staff_user_id", type="string", format="uuid", nullable=true, description="ID nhân viên"),
 *     @OA\Property(property="total_amount", type="number", format="float", description="Tổng số tiền"),
 *     @OA\Property(property="paid_amount", type="number", format="float", description="Số tiền đã thanh toán"),
 *     @OA\Property(property="remaining_amount", type="number", format="float", description="Số tiền còn lại"),
 *     @OA\Property(property="status", type="string", enum={"pending", "partial", "paid", "cancelled"}, description="Trạng thái hóa đơn"),
 *     @OA\Property(property="order_id", type="integer", format="int64", nullable=true, description="ID đơn hàng"),
 *     @OA\Property(property="note", type="string", nullable=true, description="Ghi chú"),
 *     @OA\Property(property="created_by_user_id", type="string", format="uuid", nullable=true, description="ID người tạo"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Ngày tạo"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Ngày cập nhật cuối cùng"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true, description="Ngày xóa")
 * )
 */
class Invoice extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $keyType = 'string';

    const STATUS_PENDING = 'pending';           // Chờ thanh toán
    const STATUS_PAID = 'paid';                 // Đã thanh toán
    const STATUS_PARTIALLY_PAID = 'partial';    // Thanh toán một phần
    const STATUS_CANCELLED = 'cancelled';       // Đã hủy
    const STATUS_FAILED = 'failed';             // Thanh toán thất bại

    const PAYMENT_CASH = 'cash';                // Tiền mặt
    const PAYMENT_TRANSFER = 'transfer';        // Chuyển khoản

    protected $fillable = [
        'user_id',
        'staff_user_id',
        'total_amount',
        'paid_amount',
        'status',
        'order_id',
        'note',
        'created_by_user_id'
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

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function paymentHistories()
    {
        return $this->hasMany(PaymentHistory::class);
    }

    // Helper methods để kiểm tra trạng thái
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isPaid()
    {
        return $this->status === self::STATUS_PAID;
    }

    public function isPartiallyPaid()
    {
        return $this->status === self::STATUS_PARTIALLY_PAID;
    }

    public function isCancelled()
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isFailed()
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function calculateRemainingAmount()
    {
        return $this->total_amount - $this->paid_amount;
    }
}
