<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="Address",
 *     title="Address", 
 *     description="Address model",
 *     @OA\Property(property="id", type="integer", format="int64", description="Address ID"),
 *     @OA\Property(property="user_id", type="string", format="uuid", description="User ID"),
 *     @OA\Property(property="province", type="string", description="Province"),
 *     @OA\Property(property="district", type="string", description="District"),
 *     @OA\Property(property="ward", type="string", description="Ward"),
 *     @OA\Property(property="address", type="string", description="Address"),
 *     @OA\Property(property="address_type", type="string", enum={"home","work","shipping","others"}, description="Address type"),
 *     @OA\Property(property="is_default", type="boolean", description="Is default address"),
 *     @OA\Property(property="is_temporary", type="boolean", description="Is temporary address"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation date"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Last update date"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true, description="Deletion date")
 * )
 */
class Address extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'province',
        'district',
        'ward',
        'address',
        'address_type',
        'is_default',
        'is_temporary'
    ];

    protected $casts = [
        'is_temporary' => 'boolean',
        'is_default' => 'boolean',
        'address_type' => 'string'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
