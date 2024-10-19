<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="UserVoucher",
 *     title="User Voucher",
 *     description="User Voucher model",
 *     @OA\Property(property="id", type="integer", format="int64", description="User Voucher ID"),
 *     @OA\Property(property="user_id", type="integer", format="int64", description="User ID"),
 *     @OA\Property(property="voucher_id", type="integer", format="int64", description="Voucher ID"),
 *     @OA\Property(property="is_used", type="boolean", description="Whether the voucher is used or not"),
 *     @OA\Property(property="used_date", type="string", format="date-time", description="Date when the voucher was used"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation date"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Last update date")
 * )
 */
class UserVoucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'voucher_id', 'is_used', 'used_date'
    ];

    protected $casts = [
        'is_used' => 'boolean',
        'used_date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }
}