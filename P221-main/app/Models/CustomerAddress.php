<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;

class CustomerAddress extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'customer_addresses';

    protected $fillable = [
        'user_id', 'label', 'name', 'phone', 'address_line',
        'city', 'state', 'pincode', 'is_default',
    ];

    protected function casts(): array
    {
        return ['is_default' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
