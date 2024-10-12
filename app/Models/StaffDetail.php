<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'position', 'hire_date', 'salary', 'bank_account_number',
        'bank_name', 'emergency_contact_name', 'emergency_contact_phone'
    ];

    protected $casts = [
        'hire_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}