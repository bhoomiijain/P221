<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;

class SaleItem extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'sale_items';

    protected $fillable = ['sale_id', 'batch_id', 'medicine_id', 'quantity', 'selling_price', 'line_total'];

    protected $casts = [
        'quantity' => 'integer',
        'selling_price' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class, 'batch_id');
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class, 'medicine_id');
    }
}
