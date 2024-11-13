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
 *     @OA\Property(property="description", type="string", description="Voucher description"),
 *     @OA\Property(property="discount_type", type="string", description="Voucher discount type"),
 *     @OA\Property(property="discount_value", type="number", format="float", description="Voucher discount value"),
 *     @OA\Property(property="min_order_value", type="number", format="float", description="Minimum order value"),
 *     @OA\Property(property="max_discount_amount", type="number", format="float", description="Maximum discount amount"),
 *     @OA\Property(property="usage_limit", type="integer", description="Usage limit of the voucher"),
 *     @OA\Property(property="used_times", type="integer", description="Number of times the voucher has been used"),
 *     @OA\Property(property="status", type="string", description="Voucher status"),
 *     @OA\Property(property="start_date", type="string", format="date", description="Start date of the voucher"),
 *     @OA\Property(property="end_date", type="string", format="date", description="End date of the voucher"),
 *     @OA\Property(property="is_unlimited", type="boolean", description="Whether the voucher is unlimited or not"),
 *     @OA\Property(property="uses_per_user", type="integer", description="Number of times the voucher can be used per user"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Last update timestamp"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true, description="Deletion timestamp")
 * )
 */
class Voucher extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'description',
        'discount_type',
        'discount_value',
        'min_order_value',
        'max_discount_amount',
        'usage_limit',
        'used_times',
        'status',
        'start_date',
        'end_date',
        'is_unlimited',
        'uses_per_user'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_unlimited' => 'boolean',
        'discount_value' => 'decimal:2',
        'min_order_value' => 'decimal:2',
        'max_discount_amount' => 'decimal:2'
    ];

    protected $appends = ['is_active', 'formatted_discount'];

    public function userVouchers()
    {
        return $this->hasMany(UserVoucher::class);
    }

    public function getIsActiveAttribute()
    {
        $now = now();
        return $this->status === 'active' 
            && $now->between($this->start_date, $this->end_date)
            && ($this->is_unlimited || $this->used_times < $this->usage_limit);
    }

    public function getFormattedDiscountAttribute()
    {
        if ($this->discount_type === 'percentage') {
            return $this->discount_value . '%';
        }
        return number_format($this->discount_value, 0, ',', '.') . ' ₫';
    }

    public function getMinOrderValueFormattedAttribute()
    {
        return number_format($this->min_order_value, 0, ',', '.') . ' ₫';
    }

    public function getMaxDiscountAmountFormattedAttribute()
    {
        return number_format($this->max_discount_amount, 0, ',', '.') . ' ₫';
    }

    public function getRemainingUsesAttribute()
    {
        if ($this->is_unlimited) {
            return PHP_INT_MAX;
        }
        return max(0, $this->usage_limit - $this->used_times);
    }

    public function getStartDateFormattedAttribute()
    {
        return $this->start_date ? $this->start_date->format('d/m/Y') : null;
    }

    public function getEndDateFormattedAttribute()
    {
        return $this->end_date ? $this->end_date->format('d/m/Y') : null;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->where(function ($q) {
                $q->where('is_unlimited', true)
                    ->orWhere(function ($q) {
                        $q->where('is_unlimited', false)
                            ->whereRaw('used_times < usage_limit');
                    });
            });
    }

    public function getUserRemainingUses($userId)
    {
        $userVoucher = $this->userVouchers()
            ->where('user_id', $userId)
            ->first();

        return $userVoucher ? $userVoucher->remaining_uses : 0;
    }
}
