<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;

class CustomerOrderItem extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'customer_order_items';

    protected $fillable = [
        'order_id', 'medicine_id', 'name', 'quantity', 'unit_price', 'line_total',
        'prescription_required',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'line_total' => 'decimal:2',
            'prescription_required' => 'boolean',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(CustomerOrder::class, 'order_id');
    }
}
