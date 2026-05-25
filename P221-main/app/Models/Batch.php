<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;
use MongoDB\Laravel\Relations\HasMany;

class Batch extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'batches';

    protected $fillable = [
        'user_id',
        'medicine_id',
        'batch_number',
        'expiry_date',
        'quantity',
        'purchase_price',
        'selling_price',
        'profit_pct',
    ];

    protected $casts = [
        'expiry_date'    => 'date',
        'quantity'       => 'integer',
        'purchase_price' => 'decimal:2',
        'selling_price'  => 'decimal:2',
        'profit_pct'     => 'decimal:2',
    ];

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class, 'medicine_id');
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class, 'batch_id');
    }

    public function isExpired(): bool
    {
        return Carbon::parse($this->expiry_date)->isBefore(today());
    }
}
