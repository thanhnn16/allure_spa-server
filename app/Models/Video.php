<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
/**
 * @OA\Schema(
 *     schema="Video",
 *     title="Video",
 *     description="Mô hình Video",
 *     @OA\Property(property="id", type="integer", format="int64", description="ID của video"),
 *     @OA\Property(property="video_path", type="string", description="Đường dẫn của video"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Ngày tạo"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Ngày cập nhật"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true, description="Ngày xóa")
 * )
 */
class Video extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'video_path'
    ];

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function treatments()
    {
        return $this->hasMany(Treatment::class);
    }
    
}
