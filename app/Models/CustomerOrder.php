<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;
use MongoDB\Laravel\Relations\HasMany;

class CustomerOrder extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'customer_orders';

    public const STATUSES = [
        'placed', 'verified', 'packed', 'out_for_delivery', 'delivered', 'cancelled',
    ];

    protected $fillable = [
        'user_id', 'order_number', 'status', 'subtotal', 'tax', 'delivery_charge',
        'discount', 'total', 'payment_method', 'payment_status', 'address_snapshot',
        'delivery_instructions', 'prescription_ids', 'requires_pharmacist_review',
        'ai_consultation_id', 'coupon_code', 'estimated_delivery', 'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'tax' => 'decimal:2',
            'delivery_charge' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
            'requires_pharmacist_review' => 'boolean',
            'address_snapshot' => 'array',
            'prescription_ids' => 'array',
            'estimated_delivery' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(CustomerOrderItem::class, 'order_id');
    }
}
