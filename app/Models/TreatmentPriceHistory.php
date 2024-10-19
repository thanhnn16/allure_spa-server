<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="TreatmentPriceHistory",
 *     title="Treatment Price History",
 *     description="Model representing the price history of a treatment",
 *     @OA\Property(property="id", type="integer", description="The unique identifier of the price history record"),
 *     @OA\Property(property="treatment_id", type="integer", description="The ID of the associated treatment"),
 *     @OA\Property(property="price", type="number", format="float", description="The price of the treatment"),
 *     @OA\Property(property="effective_date", type="string", format="date-time", description="The date and time when the price becomes effective"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp of when the record was created"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Timestamp of when the record was last updated")
 * )
 */
class TreatmentPriceHistory extends Model
{
    use HasFactory;

    protected $fillable = ['treatment_id', 'price', 'effective_date'];

    protected $casts = [
        'effective_date' => 'datetime',
    ];

    public function treatment()
    {
        return $this->belongsTo(Treatment::class);
    }
}