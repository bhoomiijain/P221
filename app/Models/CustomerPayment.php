<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;

class CustomerPayment extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'customer_payments';

    protected $fillable = [
        'order_id', 'user_id', 'method', 'amount', 'status', 'transaction_ref',
    ];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(CustomerOrder::class, 'order_id');
    }
}
