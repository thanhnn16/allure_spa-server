<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Treatment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'treatment_name',
        'description',
        'duration',
        'price',
        'image_id',
        'combo_type',
        'combo_price',
        'is_default',
        'validity_period'
    ];

    public function category()
    {
        return $this->belongsTo(TreatmentCategory::class);
    }

    public function image()
    {
        return $this->belongsTo(Image::class);
    }

    public function translations()
    {
        return $this->hasMany(TreatmentTranslation::class);
    }
}
