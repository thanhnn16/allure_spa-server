<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TreatmentCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['category_name', 'parent_id'];

    public function parent()
    {
        return $this->belongsTo(TreatmentCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(TreatmentCategory::class, 'parent_id');
    }

    public function treatments()
    {
        return $this->hasMany(Treatment::class, 'category_id');
    }

    public function translations()
    {
        return $this->hasMany(TreatmentCategoryTranslation::class);
    }
}
