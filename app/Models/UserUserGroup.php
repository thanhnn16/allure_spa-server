<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserUserGroup extends Model
{
    protected $fillable = [
        'user_id',
        'user_group_id'
    ];

    protected $casts = [
        'user_id' => 'string',
        'user_group_id' => 'integer'
    ];
}
