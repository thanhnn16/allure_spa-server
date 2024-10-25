<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class Media extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'type',
        'file_path',
        'mediable_type',
        'mediable_id',
    ];

    protected $casts = [
        'type' => 'string',
        'mediable_type' => 'string',
        'mediable_id' => 'integer',
    ];

    public function mediable()
    {
        return $this->morphTo();
    }

    public function getFullUrlAttribute()
    {
        if (Storage::disk('public')->exists($this->file_path)) {
            return asset('storage/' . $this->file_path);
        }
        return null;
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
