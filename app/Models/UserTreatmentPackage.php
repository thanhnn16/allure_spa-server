<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="UserTreatmentPackage",
 *     title="User Treatment Package",
 *     description="Model representing a user's treatment package",
 *     @OA\Property(property="id", type="integer", description="The unique identifier of the package"),
 *     @OA\Property(property="user_id", type="integer", description="The ID of the user who owns this package"),
 *     @OA\Property(property="treatment_id", type="integer", description="The ID of the treatment associated with this package"),
 *     @OA\Property(property="total_sessions", type="integer", description="The total number of sessions in this package"),
 *     @OA\Property(property="remaining_sessions", type="integer", description="The number of remaining sessions in this package"),
 *     @OA\Property(property="expiry_date", type="string", format="date", description="The expiration date of the package"),
 *     @OA\Property(property="purchase_date", type="string", format="date", description="The date when the package was purchased"),
 *     @OA\Property(property="price", type="number", format="float", description="The price of the package")
 * )
 */
class UserTreatmentPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'treatment_id', 'total_sessions', 'remaining_sessions',
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

    public function treatment()
    {
        return $this->belongsTo(Treatment::class);
    }

    public function usageHistories()
    {
        return $this->hasMany(TreatmentUsageHistory::class);
    }
}
