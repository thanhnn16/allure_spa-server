<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasTranslations;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true, description="Ngày xóa"),
 *     @OA\Property(
 *         property="media",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Media"),
 *         description="Danh sách media liên quan đến dịch vụ"
 *     ),
 *     @OA\Property(
 *         property="category",
 *         ref="#/components/schemas/ServiceCategory",
 *         description="Danh mục của dịch vụ"
 *     ),
 *     @OA\Property(
 *         property="ratings",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Rating"),
 *         description="Danh sách đánh giá của dịch vụ"
 *     ),
 *     @OA\Property(
 *         property="appointments",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Appointment"),
 *         description="Danh sách lịch hẹn của dịch vụ"
 *     )
 * )
 */
class Service extends Model
{
    use HasFactory, SoftDeletes, HasTranslations;

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

    // Định nghĩa các trường có thể dịch
    protected $translatable = [
        'service_name',
        'description'
    ];

    protected $appends = ['rating_summary', 'is_favorite'];

    // Thêm property để lưu user_id tạm thời
    public $current_user_id = null;

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
        return $this->morphMany(Rating::class, 'item', 'rating_type', 'item_id')
            ->with(['user', 'media'])
            ->latest();
    }

    public function approvedRatings()
    {
        return $this->ratings()
            ->where('status', 'approved');
    }

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'category_id');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'item_id')
            ->where('favorite_type', 'service');
    }

    public function userServicePackages()
    {
        return $this->hasMany(UserServicePackage::class);
    }

    public function orderItems()
    {
        return $this->morphMany(OrderItem::class, 'item');
    }

    public function getRatingSummaryAttribute()
    {
        $approvedRatings = $this->ratings()
            ->where('status', 'approved');

        return [
            'average_rating' => round($this->average_rating ?? 0, 1),
            'total_ratings' => $this->total_ratings ?? 0,
            'rating_distribution' => [
                5 => (clone $approvedRatings)->where('stars', 5)->count(),
                4 => (clone $approvedRatings)->where('stars', 4)->count(),
                3 => (clone $approvedRatings)->where('stars', 3)->count(),
                2 => (clone $approvedRatings)->where('stars', 2)->count(),
                1 => (clone $approvedRatings)->where('stars', 1)->count(),
            ]
        ];
    }

    public function getIsFavoriteAttribute()
    {
        return Favorite::where('user_id', $this->current_user_id)
            ->where('item_id', $this->id)
            ->where('favorite_type', 'service')
            ->exists();
    }
}
