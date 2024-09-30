<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Treatment extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'treatment_name',
        'description',
        'duration',
        'price',
        'image_id'
    ];

    public function category()
    {
        return $this->belongsTo(TreatmentCategory::class, 'category_id');
    }

    public function combos()
    {
        return $this->hasMany(TreatmentCombo::class, 'treatment_id');
    }

}
