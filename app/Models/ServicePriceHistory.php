<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="ServicePriceHistory",
 *     title="Service Price History",
 *     description="Model representing the price history of a service",
 *     @OA\Property(property="id", type="integer", description="The unique identifier of the price history record"),
 *     @OA\Property(property="service_id", type="integer", description="The ID of the associated service"),
 *     @OA\Property(property="price", type="number", format="float", description="The price of the service"),
 *     @OA\Property(property="effective_date", type="string", format="date-time", description="The date and time when the price becomes effective"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp of when the record was created"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Timestamp of when the record was last updated")
 * )
 */
class ServicePriceHistory extends Model
{
    use HasFactory;

    protected $fillable = ['service_id', 'price', 'effective_date'];

    protected $casts = [
        'effective_date' => 'datetime',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}