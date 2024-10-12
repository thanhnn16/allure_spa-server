<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TreatmentPriceHistory extends Model
{
    use HasFactory;

    protected $fillable = ['treatment_id', 'price', 'effective_date'];

    protected $casts = [
        'effective_date' => 'datetime',
    ];

    public function treatment()
    {
        return $this->belongsTo(Treatment::class);
    }
}