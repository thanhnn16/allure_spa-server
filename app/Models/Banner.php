<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Banner",
 *     title="Banner",
 *     description="Banner model",
 *     @OA\Property(property="id", type="integer", format="int64", description="Banner ID"),
 *     @OA\Property(property="title", type="string", description="Banner title"),
 *     @OA\Property(property="description", type="string", description="Banner description"),
 *     @OA\Property(property="image_url", type="string", format="url", description="URL of the banner image"),
 *     @OA\Property(property="link_url", type="string", format="url", description="URL that the banner links to"),
 *     @OA\Property(property="start_date", type="string", format="date", description="Start date of the banner"),
 *     @OA\Property(property="end_date", type="string", format="date", description="End date of the banner"),
 *     @OA\Property(property="is_active", type="boolean", description="Whether the banner is active or not"),
 *     @OA\Property(property="order", type="integer", description="Display order of the banner"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp of creation"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Timestamp of last update")
 * )
 */
class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description', 
        'image_url',
        'link_url',
        'start_date',
        'end_date',
        'is_active',
        'order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date'
    ];

    // Thêm accessor để lấy full URL của hình ảnh
    protected $appends = ['full_image_url'];

    public function getFullImageUrlAttribute()
    {
        if (!$this->image_url) {
            return null;
        }

        // Nếu image_url đã là URL đầy đủ
        if (filter_var($this->image_url, FILTER_VALIDATE_URL)) {
            return $this->image_url;
        }

        // Nếu không, tạo URL đầy đủ từ storage
        return asset('storage/' . $this->image_url);
    }
}