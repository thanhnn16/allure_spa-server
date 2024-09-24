<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'table_name', 'row_id', 'action', 'changes', 'action_timestamp'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
