<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasUuids;

    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'phone_number',
        'email',
        'password',
        'role',
        'full_name',
        'gender',
        'date_of_birth',
        'image_id',
        'loyalty_points',
        'skin_condition',
        'note',
        'purchase_count'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function treatmentPackages()
    {
        return $this->hasMany(UserTreatmentPackage::class);
    }

    public function image()
    {
        return $this->belongsTo(Image::class);
    }

    public function chats()
    {
        return $this->hasMany(Chat::class, 'user_id');
    }

    public function staffChats()
    {
        return $this->hasMany(Chat::class, 'staff_id');
    }

    public function sentMessages()
    {
        return $this->hasMany(ChatMessage::class, 'sender_id');
    }
}
