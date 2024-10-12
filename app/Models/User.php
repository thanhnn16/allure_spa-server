<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'phone_number', 'email', 'password', 'role', 'full_name', 'gender',
        'date_of_birth', 'image_id', 'loyalty_points', 'skin_condition',
        'note', 'purchase_count'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_of_birth' => 'date',
    ];

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function staffDetail()
    {
        return $this->hasOne(StaffDetail::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function userTreatmentPackages()
    {
        return $this->hasMany(UserTreatmentPackage::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function fcmTokens()
    {
        return $this->hasMany(FcmToken::class);
    }

    public function image()
    {
        return $this->belongsTo(Image::class);
    }

    public function cart()
    {
        return $this->hasOne(Cart::class);
    }

    public function employeeAttendances()
    {
        return $this->hasMany(EmployeeAttendance::class);
    }

    public function pointRedemptionHistories()
    {
        return $this->hasMany(PointRedemptionHistory::class);
    }

    public function chats()
    {
        return $this->hasMany(Chat::class);
    }

    public function sentChatMessages()
    {
        return $this->hasMany(ChatMessage::class, 'sender_id');
    }

    public function histories()
    {
        return $this->hasMany(History::class);
    }
}
