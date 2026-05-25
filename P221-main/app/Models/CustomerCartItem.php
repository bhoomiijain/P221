<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;

class CustomerCartItem extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'customer_cart_items';

    protected $fillable = [
        'cart_id', 'medicine_id', 'quantity', 'saved_for_later',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'saved_for_later' => 'boolean',
        ];
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(CustomerCart::class, 'cart_id');
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class, 'medicine_id');
    }
}
