<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerTreatmentHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'treatment_id',
        'staff_id',
        'treatment_date',
        'note',
        'result'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function treatment()
    {
        return $this->belongsTo(Treatment::class);
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
}
