<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="Service",
 *     title="Dịch vụ",
 *     description="Mô hình dịch vụ",
 *     @OA\Property(property="id", type="integer", format="int64", description="ID dịch vụ"),
 *     @OA\Property(property="service_name", type="string", description="Tên dịch vụ"),
 *     @OA\Property(property="description", type="string", description="Mô tả dịch vụ"),
 *     @OA\Property(property="duration", type="integer", description="Thời gian dịch vụ tính bằng phút"),
 *     @OA\Property(property="category_id", type="integer", format="int64", description="ID danh mục"),
 *     @OA\Property(property="single_price", type="number", format="float", description="Giá dịch vụ đơn lẻ"),
 *     @OA\Property(property="combo_5_price", type="number", format="float", description="Giá combo 5 lần"),
 *     @OA\Property(property="combo_10_price", type="number", format="float", description="Giá combo 10 lần"),
 *     @OA\Property(property="validity_period", type="integer", description="Thời hạn sử dụng tính bằng ngày"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Ngày tạo"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Ngày cập nhật cuối cùng"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true, description="Ngày xóa")
 * )
 */
class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'service_name',
        'description',
        'duration',
        'category_id',
        'single_price',
        'combo_5_price',
        'combo_10_price',
        'validity_period'
    ];

    protected $morphClass = 'service';
    protected $casts = [
        'single_price' => 'float',
        'combo_5_price' => 'float',
        'combo_10_price' => 'float',
        'duration' => 'integer',
        'validity_period' => 'integer',
    ];

    public function media()
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    public function priceHistory()
    {
        return $this->hasMany(ServicePriceHistory::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'category_id');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function userServicePackages()
    {
        return $this->hasMany(UserServicePackage::class);
    }
}
