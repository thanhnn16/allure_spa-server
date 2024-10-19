<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Address",
 *     title="Address",
 *     description="Address model",
 *     @OA\Property(property="id", type="integer", format="int64", description="Address ID"),
 *     @OA\Property(property="user_id", type="integer", format="int64", description="User ID"),
 *     @OA\Property(property="address_line1", type="string", description="Address line 1"),
 *     @OA\Property(property="address_line2", type="string", nullable=true, description="Address line 2"),
 *     @OA\Property(property="city", type="string", description="City"),
 *     @OA\Property(property="state", type="string", description="State"),
 *     @OA\Property(property="postal_code", type="string", description="Postal code"),
 *     @OA\Property(property="country", type="string", description="Country"),
 *     @OA\Property(property="is_default", type="boolean", description="Is default address"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation date"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Last update date")
 * )
 */
class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'address_line1', 'address_line2', 'city', 'state',
        'postal_code', 'country', 'is_default'
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}