<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="TreatmentTranslation",
 *     title="Treatment Translation",
 *     description="Model for treatment translations",
 *     @OA\Property(property="id", type="integer", format="int64", description="Unique identifier"),
 *     @OA\Property(property="treatment_id", type="integer", format="int64", description="ID of the related treatment"),
 *     @OA\Property(property="language", type="string", description="Language code of the translation"),
 *     @OA\Property(property="name", type="string", description="Translated name of the treatment"),
 *     @OA\Property(property="description", type="string", description="Translated description of the treatment"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Last update timestamp")
 * )
 */
class TreatmentTranslation extends Model
{
    use HasFactory;

    protected $fillable = ['treatment_id', 'language', 'name', 'description'];

    public function treatment()
    {
        return $this->belongsTo(Treatment::class);
    }
}