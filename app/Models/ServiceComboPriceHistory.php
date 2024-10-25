<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="ServiceComboPriceHistory",
 *     title="Service Combo Price History",
 *     description="Model representing the price history of a service combo",
 *     @OA\Property(property="id", type="integer", description="The ID of the price history record"),
 *     @OA\Property(property="service_combo_id", type="integer", description="The ID of the associated service combo"),
 *     @OA\Property(property="price", type="number", format="float", description="The price of the service combo"),
 *     @OA\Property(property="effective_date", type="string", format="date-time", description="The date and time when the price becomes effective"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="The creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="The last update timestamp")
 * )
 */
class ServiceComboPriceHistory extends Model
{
    use HasFactory;

    protected $fillable = ['service_combo_id', 'price', 'effective_date'];

    protected $casts = [
        'effective_date' => 'datetime',
    ];

    public function serviceCombo()
    {
        return $this->belongsTo(ServiceCombo::class);
    }
}