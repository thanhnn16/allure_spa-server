<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="TreatmentCategoryTranslation",
 *     title="Treatment Category Translation",
 *     description="Model representing a translation of a treatment category",
 *     @OA\Property(property="id", type="integer", description="The ID of the translation"),
 *     @OA\Property(property="treatment_category_id", type="integer", description="The ID of the related treatment category"),
 *     @OA\Property(property="language", type="string", description="The language code of the translation"),
 *     @OA\Property(property="name", type="string", description="The translated name of the treatment category"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp of creation"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Timestamp of last update")
 * )
 */
class TreatmentCategoryTranslation extends Model
{
    use HasFactory;

    protected $fillable = ['treatment_category_id', 'language', 'name'];

    public function treatmentCategory()
    {
        return $this->belongsTo(TreatmentCategory::class);
    }
}