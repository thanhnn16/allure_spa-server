<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TreatmentTranslation extends Model
{
    use HasFactory;

    protected $fillable = ['treatment_id', 'language', 'name', 'description'];

    public function treatment()
    {
        return $this->belongsTo(Treatment::class);
    }
}