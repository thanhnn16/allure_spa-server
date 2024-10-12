<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TreatmentCombo extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'price', 'description', 'image_id'];

    public function treatments()
    {
        return $this->belongsToMany(Treatment::class, 'treatment_combo_items')
            ->withPivot('quantity');
    }

    public function priceHistory()
    {
        return $this->hasMany(TreatmentComboPriceHistory::class);
    }

    public function image()
    {
        return $this->belongsTo(Image::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
