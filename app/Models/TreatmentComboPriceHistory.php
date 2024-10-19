<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="TreatmentComboPriceHistory",
 *     title="Treatment Combo Price History",
 *     description="Model representing the price history of a treatment combo",
 *     @OA\Property(property="id", type="integer", description="The ID of the price history record"),
 *     @OA\Property(property="treatment_combo_id", type="integer", description="The ID of the associated treatment combo"),
 *     @OA\Property(property="price", type="number", format="float", description="The price of the treatment combo"),
 *     @OA\Property(property="effective_date", type="string", format="date-time", description="The date and time when the price becomes effective"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="The creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="The last update timestamp")
 * )
 */
class TreatmentComboPriceHistory extends Model
{
    use HasFactory;

    protected $fillable = ['treatment_combo_id', 'price', 'effective_date'];

    protected $casts = [
        'effective_date' => 'datetime',
    ];

    public function treatmentCombo()
    {
        return $this->belongsTo(TreatmentCombo::class);
    }
}