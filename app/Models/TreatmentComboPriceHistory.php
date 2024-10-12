<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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