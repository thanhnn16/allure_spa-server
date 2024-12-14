<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class UserGroup extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_active'
    ];

    protected $appends = ['users_count', 'last_sync_at'];
    protected $casts = [
        'is_active' => 'boolean',
        'last_sync_at' => 'datetime'
    ];

    /**
     * Điều kiện của nhóm
     */
    public function conditions(): HasMany
    {
        return $this->hasMany(UserGroupCondition::class);
    }

    /**
     * Relationship với users
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_user_group');
    }

    /**
     * Đếm số lượng users dựa trên điều kiện
     */
    public function getUsersCountAttribute()
    {
        $query = User::query();
        
        foreach ($this->conditions as $condition) {
            switch ($condition->field) {
                case 'loyalty_points':
                case 'purchase_count':
                    if ($condition->operator === 'between') {
                        $values = explode(',', $condition->value);
                        $query->whereBetween($condition->field, $values);
                    } else {
                        $query->where($condition->field, $condition->operator, $condition->value);
                    }
                    break;

                case 'last_visit':
                    $days = (int) $condition->value;
                    if ($condition->operator === 'within') {
                        $query->where('last_visit_at', '>=', now()->subDays($days));
                    } else {
                        $query->where('last_visit_at', '<', now()->subDays($days));
                    }
                    break;

                case 'gender':
                    $query->where('gender', $condition->value);
                    break;

                case 'age':
                    if ($condition->operator === 'between') {
                        $values = explode(',', $condition->value);
                        $query->whereRaw('TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) BETWEEN ? AND ?', $values);
                    } else {
                        $query->whereRaw('TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) ' . $condition->operator . ' ?', [$condition->value]);
                    }
                    break;
            }
        }

        return $query->count();
    }

    /**
     * Lấy danh sách users theo điều kiện
     */
    public function getFilteredUsers()
    {
        $query = User::query();
        
        foreach ($this->conditions as $condition) {
            switch ($condition->field) {
                case 'loyalty_points':
                case 'purchase_count':
                    if ($condition->operator === 'between') {
                        $values = explode(',', $condition->value);
                        $query->whereBetween($condition->field, $values);
                    } else {
                        $query->where($condition->field, $condition->operator, $condition->value);
                    }
                    break;

                case 'last_visit':
                    $days = (int) $condition->value;
                    $query->whereHas('histories', function($q) use ($days, $condition) {
                        if ($condition->operator === 'within') {
                            $q->where('created_at', '>=', now()->subDays($days));
                        } else {
                            $q->where('created_at', '<', now()->subDays($days));
                        }
                    });
                    break;

                case 'gender':
                    $query->where('gender', $condition->value);
                    break;

                case 'age':
                    if ($condition->operator === 'between') {
                        $values = explode(',', $condition->value);
                        $query->whereRaw('TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) BETWEEN ? AND ?', $values);
                    } else {
                        $query->whereRaw('TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) ' . $condition->operator . ' ?', [$condition->value]);
                    }
                    break;
            }
        }

        return $query->get();
    }

    public function syncUsers()
    {
        $users = $this->getFilteredUsers();
        $this->users()->sync($users->pluck('id'));
        $this->touch();
        return $users->count();
    }

    public function getLastSyncAtAttribute()
    {
        return $this->updated_at;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
} 