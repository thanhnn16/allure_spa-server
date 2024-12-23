<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="RewardItem",
 *     title="Reward Item",
 *     description="Model representing a reward item",
 *     @OA\Property(property="id", type="integer", description="The unique identifier of the reward item"),
 *     @OA\Property(property="name", type="string", description="The name of the reward item"),
 *     @OA\Property(property="description", type="string", description="The description of the reward item"),
 *     @OA\Property(property="points_required", type="integer", description="The number of points required to redeem this item"),
 *     @OA\Property(property="quantity_available", type="integer", description="The quantity of this item available for redemption"),
 *     @OA\Property(property="image_id", type="integer", description="The ID of the associated image"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="The creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="The last update timestamp")
 * )
 */
class RewardItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'item_type',
        'item_id',
        'points_required',
        'quantity_available',
        'start_date',
        'end_date'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime'
    ];

    public function item()
    {
        return $this->morphTo(__FUNCTION__, 'item_type', 'item_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'item_id')
            ->when($this->item_type === 'product', function ($query) {
                return $query;
            });
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'item_id')
            ->when($this->item_type === 'service', function ($query) {
                return $query;
            });
    }

    public function redemptions()
    {
        return $this->hasMany(PointRedemptionHistory::class);
    }

    public function isAvailable()
    {
        $now = now();
        
        // Kiểm tra ngày bắt đầu
        if ($this->start_date && $now < $this->start_date) {
            return false;
        }
        
        // Kiểm tra ngày kết thúc
        if ($this->end_date && $now > $this->end_date) {
            return false;
        }
        
        // Kiểm tra số lượng còn lại
        if ($this->quantity_available !== null && $this->quantity_available <= 0) {
            return false;
        }
        
        return true;
    }

    public function getItemAttribute()
    {
        return $this->item_type === 'product' 
            ? $this->product 
            : $this->service;
    }
}
