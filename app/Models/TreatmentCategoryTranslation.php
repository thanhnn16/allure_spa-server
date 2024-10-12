<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TreatmentCategoryTranslation extends Model
{
    use HasFactory;

    protected $fillable = ['treatment_category_id', 'language', 'name'];

    public function treatmentCategory()
    {
        return $this->belongsTo(TreatmentCategory::class);
    }
}