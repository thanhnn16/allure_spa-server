<?php

namespace App\Models;

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
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'staff_user_id', 'total_amount', 'paid_amount',
        'status', 'order_id', 'note', 'created_by_user_id'
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
}
