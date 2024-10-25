<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="UserServicePackage",
 *     title="User Service Package",
 *     description="Model representing a user's service package",
 *     @OA\Property(property="id", type="integer", description="The unique identifier of the package"),
 *     @OA\Property(property="user_id", type="integer", description="The ID of the user who owns this package"),
 *     @OA\Property(property="service_id", type="integer", description="The ID of the service associated with this package"),
 *     @OA\Property(property="total_sessions", type="integer", description="The total number of sessions in this package"),
 *     @OA\Property(property="remaining_sessions", type="integer", description="The number of remaining sessions in this package"),
 *     @OA\Property(property="expiry_date", type="string", format="date", description="The expiration date of the package"),
 *     @OA\Property(property="purchase_date", type="string", format="date", description="The date when the package was purchased"),
 *     @OA\Property(property="price", type="number", format="float", description="The price of the package")
 * )
 */
class UserServicePackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'service_id', 'total_sessions', 'remaining_sessions',
        'expiry_date', 'purchase_date', 'price'
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'purchase_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function usageHistories()
    {
        return $this->hasMany(ServiceUsageHistory::class);
    }
}
