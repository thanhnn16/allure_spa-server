<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="Voucher",
 *     title="Voucher",
 *     description="Voucher model",
 *     @OA\Property(property="id", type="integer", format="int64", description="Voucher ID"),
 *     @OA\Property(property="code", type="string", description="Voucher code"),
 *     @OA\Property(property="type", type="string", description="Voucher type"),
 *     @OA\Property(property="value", type="number", format="float", description="Voucher value"),
 *     @OA\Property(property="start_date", type="string", format="date", description="Start date of the voucher"),
 *     @OA\Property(property="end_date", type="string", format="date", description="End date of the voucher"),
 *     @OA\Property(property="min_purchase_amount", type="number", format="float", description="Minimum purchase amount"),
 *     @OA\Property(property="max_discount_amount", type="number", format="float", description="Maximum discount amount"),
 *     @OA\Property(property="usage_limit", type="integer", description="Usage limit of the voucher"),
 *     @OA\Property(property="used_count", type="integer", description="Number of times the voucher has been used"),
 *     @OA\Property(property="is_active", type="boolean", description="Whether the voucher is active or not"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Last update timestamp"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true, description="Deletion timestamp")
 * )
 */
class Voucher extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'type', 'value', 'start_date', 'end_date',
        'min_purchase_amount', 'max_discount_amount', 'usage_limit',
        'used_count', 'is_active'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function userVouchers()
    {
        return $this->hasMany(UserVoucher::class);
    }

    public function translations()
    {
        return $this->hasMany(VoucherTranslation::class);
    }
}