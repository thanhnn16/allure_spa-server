<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Treatment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'price', 'description', 'duration', 'image_id'
    ];

    public function image()
    {
        return $this->belongsTo(Image::class);
    }

    public function priceHistory()
    {
        return $this->hasMany(TreatmentPriceHistory::class);
    }

    public function combos()
    {
        return $this->hasMany(TreatmentCombo::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function translations()
    {
        return $this->hasMany(TreatmentTranslation::class);
    }

    public function category()
    {
        return $this->belongsTo(TreatmentCategory::class, 'category_id');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function userTreatmentPackages()
    {
        return $this->hasMany(UserTreatmentPackage::class);
    }
}
