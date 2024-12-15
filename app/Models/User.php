<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="User",
 *     title="User",
 *     description="User model",
 *     @OA\Property(property="id", type="string", format="uuid", description="User ID"),
 *     @OA\Property(property="phone_number", type="string", description="User's phone number"),
 *     @OA\Property(property="email", type="string", format="email", description="User's email address"),
 *     @OA\Property(property="role", type="string", description="User's role"),
 *     @OA\Property(property="full_name", type="string", description="User's full name"),
 *     @OA\Property(property="gender", type="string", description="User's gender"),
 *     @OA\Property(property="date_of_birth", type="string", format="date", description="User's date of birth"),
 *     @OA\Property(property="media_id", type="integer", description="ID of the user's profile image"),
 *     @OA\Property(property="loyalty_points", type="integer", description="User's loyalty points"),
 *     @OA\Property(property="skin_condition", type="string", description="User's skin condition"),
 *     @OA\Property(property="note", type="string", description="Additional notes about the user"),
 *     @OA\Property(property="purchase_count", type="integer", description="Number of purchases made by the user"),
 *     @OA\Property(property="email_verified_at", type="string", format="date-time", description="Timestamp of email verification"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp of user creation"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Timestamp of last update"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true, description="Timestamp of soft deletion")
 * )
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasUlids;

    protected $keyType = 'string';
    protected $primaryKey = 'id';
    public $incrementing = false;

    protected $fillable = [
        'phone_number',
        'phone_verified_at',
        'email',
        'email_verified_at', 
        'password',
        'role',
        'remember_token',
        'full_name',
        'gender',
        'date_of_birth',
        'media_id',
        'loyalty_points',
        'skin_condition', 
        'note',
        'purchase_count',
        'zalo_id',
        'zalo_access_token',
        'zalo_token_expires_at',
        'provider',
        'refresh_token_expires_at'
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'zalo_access_token',
        'zalo_refresh_token'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'date_of_birth' => 'datetime',
        'loyalty_points' => 'integer',
        'purchase_count' => 'integer',
        'gender' => 'string',
        'zalo_token_expires_at' => 'datetime',
        'refresh_token_expires_at' => 'datetime'
    ];

    protected $appends = ['avatar_url'];

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

    public function userServicePackages()
    {
        return $this->hasMany(UserServicePackage::class);
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

    public function media()
    {
        return $this->belongsTo(Media::class, 'media_id');
    }

    public function getAvatarUrlAttribute()
    {
        if ($this->media) {
            return $this->media->full_url;
        }
        // Return default avatar URL
        return asset('images/default-avatar.png');
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

    public function vouchers()
    {
        return $this->belongsToMany(Voucher::class, 'user_vouchers')
            ->withPivot('remaining_uses', 'total_uses')
            ->withTimestamps();
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole($role)
    {
        return $this->role === $role;
    }

    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is staff
     */
    public function isStaff()
    {
        return $this->role === 'staff';
    }

    /**
     * Check if user is customer
     */
    public function isCustomer()
    {
        return $this->role === 'user';
    }

    public function cancelledAppointments()
    {
        return $this->hasMany(Appointment::class, 'cancelled_by');
    }

    public function loginHistories()
    {
        return $this->hasMany(LoginHistory::class);
    }
}
