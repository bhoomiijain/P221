<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;
use MongoDB\Laravel\Relations\HasMany;

class CustomerCart extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'customer_carts';

    protected $fillable = ['user_id', 'coupon_code', 'coupon_discount'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(CustomerCartItem::class, 'cart_id');
    }
}
