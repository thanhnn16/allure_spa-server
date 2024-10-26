<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="OrderItem",
 *     title="Mục đơn hàng",
 *     description="Mô hình mục đơn hàng",
 *     @OA\Property(property="id", type="integer", format="int64", description="ID mục đơn hàng"),
 *     @OA\Property(property="order_id", type="integer", format="int64", description="ID đơn hàng"),
 *     @OA\Property(property="item_type", type="string", enum={"product", "service"}, description="Loại mục (sản phẩm hoặc dịch vụ)"),
 *     @OA\Property(property="item_id", type="integer", format="int64", description="ID của sản phẩm hoặc dịch vụ"),
 *     @OA\Property(property="service_type", type="string", enum={"single", "combo_5", "combo_10"}, nullable=true, description="Loại dịch vụ (đơn lẻ, combo 5, combo 10)"),
 *     @OA\Property(property="quantity", type="integer", description="Số lượng"),
 *     @OA\Property(property="price", type="number", format="float", description="Giá"),
 *     @OA\Property(property="discount_amount", type="number", format="float", description="Số tiền giảm giá"),
 *     @OA\Property(property="discount_type", type="string", enum={"percentage", "fixed_amount"}, description="Loại giảm giá (phần trăm hoặc số tiền cố định)"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Ngày tạo"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Ngày cập nhật cuối cùng")
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

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Quan hệ polymorphic chung
    public function item()
    {
        return $this->morphTo();
    }

    // Quan hệ với Service khi item_type là 'service'
    public function service()
    {
        return $this->belongsTo(Service::class, 'item_id')
            ->whereHas('orderItems', function($query) {
                $query->where('item_type', 'service');
            });
    }

    // Quan hệ với Product khi item_type là 'product'
    public function product()
    {
        return $this->belongsTo(Product::class, 'item_id')
            ->whereHas('orderItems', function($query) {
                $query->where('item_type', 'product');
            });
    }
}
