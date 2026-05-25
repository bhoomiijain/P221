<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\HasMany;

class Category extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'categories';

    protected $fillable = ['name', 'type', 'user_id'];

    /**
     * Scope: return master categories + categories owned by the given user.
     */
    public function scopeMasterOrOwned($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('type', 'master')
              ->orWhere(function ($q2) use ($userId) {
                  $q2->where('type', 'user')->where('user_id', $userId);
              });
        });
    }

    public function medicines(): HasMany
    {
        return $this->hasMany(Medicine::class, 'category_id');
    }
}
