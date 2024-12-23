<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Schema(
 *     schema="Media",
 *     title="Media",
 *     description="Mô hình Media",
 *     @OA\Property(property="id", type="integer", format="int64", description="ID media"),
 *     @OA\Property(property="type", type="string", description="Loại media"),
 *     @OA\Property(property="file_path", type="string", description="Đường dẫn file"),
 *     @OA\Property(property="mediable_type", type="string", description="Loại đối tượng liên kết"),
 *     @OA\Property(property="mediable_id", type="string", description="ID đối tượng liên kết"),
 *     @OA\Property(property="full_url", type="string", description="URL đầy đủ của file"),
 *     @OA\Property(property="position", type="integer", format="int64", description="Vị trí của media"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Ngày tạo"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Ngày cập nhật cuối cùng"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true, description="Ngày xóa")
 * )
 */
class Media extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'type',
        'file_path',
        'mediable_type',
        'mediable_id',
        'position'
    ];

    protected $casts = [
        'type' => 'string',
        'mediable_type' => 'string',
        'mediable_id' => 'string',
        'position' => 'integer'
    ];

    protected $appends = ['full_url'];

    public function mediable()
    {
        return $this->morphTo();
    }

    public function getFullUrlAttribute()
    {
        // Kiểm tra file tồn tại trong storage public
        if (Storage::disk('public')->exists($this->file_path)) {
            return asset('storage' . $this->file_path);
        }

        // Nếu file path bắt đầu bằng / thì bỏ / đi để tránh double slash
        $path = ltrim($this->file_path, '/');

        // Kiểm tra file tồn tại trong storage public với path đã được chuẩn hóa
        if (Storage::disk('public')->exists($path)) {
            return asset('storage/' . $path);
        }
        return asset($this->file_path);
    }

    public function fileExists()
    {
        return Storage::exists($this->file_path);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function banners()
    {
        return $this->hasMany(Banner::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
