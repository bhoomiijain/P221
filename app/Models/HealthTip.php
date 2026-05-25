<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class HealthTip extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'health_tips';

    protected $fillable = ['title', 'body', 'icon', 'active', 'sort_order'];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
