<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserGroupCondition extends Model
{
    protected $fillable = [
        'user_group_id',
        'field',
        'operator',
        'value'
    ];

    public function userGroup()
    {
        return $this->belongsTo(UserGroup::class);
    }
}