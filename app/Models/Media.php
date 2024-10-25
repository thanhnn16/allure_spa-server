<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
