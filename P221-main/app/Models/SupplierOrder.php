<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;

class SupplierOrder extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'supplier_orders';

    protected $fillable = [
        'pharmacist_id',
        'supplier_id',
        // Legacy single-item fields (kept for backward compat)
        'medicine_id',
        'quantity',
        'total_price',
        // New multi-item field
        'items',       // array of { medicine_id, quantity, unit_price, line_total }
        'payment_method',
        'status',      // pending | payment_received | rejected
        'notes',
    ];

    protected $casts = [
        'quantity'    => 'integer',
        'total_price' => 'decimal:2',
        'items'       => 'array',
    ];

    public function pharmacist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pharmacist_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supplier_id');
    }

    // Legacy single-medicine relation (kept for old orders)
    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class, 'medicine_id');
    }
}
