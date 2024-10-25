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
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Ngày cập nhật")
 * )
 */
class PaymentHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id', 'old_payment_status', 'new_payment_status'
    ];

    public $timestamps = false;

    protected $casts = [
        'updated_at' => 'datetime',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}