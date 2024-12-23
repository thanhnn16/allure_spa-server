<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="PaymentHistory",
 *     title="Lịch sử thanh toán",
 *     description="Mô hình lịch sử thanh toán",
 *     @OA\Property(property="id", type="integer", format="int64", description="ID lịch sử thanh toán"),
 *     @OA\Property(property="invoice_id", type="string", format="uuid", description="ID hóa đơn liên quan"),
 *     @OA\Property(property="old_payment_status", type="string", description="Trạng thái thanh toán cũ"),
 *     @OA\Property(property="new_payment_status", type="string", description="Trạng thái thanh toán mới"),
 *     @OA\Property(property="payment_amount", type="number", format="float", description="Số tiền thanh toán"),
 *     @OA\Property(property="payment_method", type="string", description="Phương thức thanh toán"),
 *     @OA\Property(property="payment_proof", type="string", nullable=true, description="Bằng chứng thanh toán"),
 *     @OA\Property(property="created_by_user_id", type="string", format="uuid", nullable=true, description="ID người tạo"),
 *     @OA\Property(property="note", type="string", nullable=true, description="Ghi chú"),
 *     @OA\Property(property="created_at", type="string", format="date-time", nullable=true, description="Ngày tạo"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", nullable=true, description="Ngày cập nhật")
 * )
 */
class PaymentHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'old_payment_status',
        'new_payment_status',
        'payment_amount',
        'payment_method',
        'payment_proof',
        'created_by_user_id',
        'note',
        'transaction_code',
        'payment_details'
    ];

    protected $attributes = [
        'old_payment_status' => 'pending',
        'new_payment_status' => 'pending',
    ];

    protected $casts = [
        'payment_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
