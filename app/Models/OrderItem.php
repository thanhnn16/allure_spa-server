<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="OrderItem",
 *     title="Order Item",
 *     description="Order item model",
 *     @OA\Property(property="id", type="integer", format="int64", description="Order item ID"),
 *     @OA\Property(property="order_id", type="integer", format="int64", description="Order ID"),
 *     @OA\Property(property="item_type", type="string", enum={"product", "service"}, description="Item type"),
 *     @OA\Property(property="item_id", type="integer", description="Product/Service ID"),
 *     @OA\Property(property="service_type", type="string", nullable=true, enum={"single", "combo_5", "combo_10"}, description="Service type"),
 *     @OA\Property(property="quantity", type="integer", description="Item quantity"),
 *     @OA\Property(property="price", type="number", format="float", description="Item price"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'item_type',
        'item_id',
        'service_type',
        'quantity',
        'price',
        'discount_amount',
        'discount_type'
    ];

    protected $casts = [
        'item_type' => 'string',
        'service_type' => 'string',
        'discount_type' => 'string',
    ];

    // Thêm accessor để lấy tên item
    protected $appends = ['item_name', 'is_rated'];

    public function getItemNameAttribute()
    {
        if ($this->item_type === 'service' && $this->service) {
            return $this->service->service_name;
        }
        
        if ($this->item_type === 'product' && $this->product) {
            return $this->product->name;
        }
        
        return $this->attributes['item_name'] ?? null;
    }

    public function getIsRatedAttribute()
    {
        return $this->rating()
            ->where('user_id', $this->order->user_id)
            ->exists();
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Quan hệ với Service
    public function service()
    {
        return $this->belongsTo(Service::class, 'item_id')->withTrashed();
    }

    // Quan hệ với Product
    public function product()
    {
        return $this->belongsTo(Product::class, 'item_id')->withTrashed();
    }

    // Quan hệ với Rating
    public function rating()
    {
        return $this->hasOne(Rating::class)
            ->latest();
    }

    // Kiểm tra item đã được đánh giá chưa
    public function isRated()
    {
        return $this->rating()->exists();
    }

    // Sửa lại accessor để đảm bảo item_type luôn đúng
    protected function getItemTypeAttribute()
    {
        // Nếu có service relationship được load và có service
        if ($this->relationLoaded('service') && $this->service !== null) {
            return 'service';
        }
        
        // Nếu có product relationship được load và có product
        if ($this->relationLoaded('product') && $this->product !== null) {
            return 'product';
        }
        
        // Nếu không có relationship nào được load, kiểm tra theo item_type từ database
        if ($this->attributes['item_type'] === 'service') {
            return 'service';
        }
        
        if ($this->attributes['item_type'] === 'product') {
            return 'product';
        }
        
        return null;
    }
}
