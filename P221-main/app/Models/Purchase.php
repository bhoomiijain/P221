<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;
use MongoDB\Laravel\Relations\HasMany;

class Purchase extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'purchases';

    protected $fillable = ['supplier_id', 'purchase_date'];

    protected $casts = ['purchase_date' => 'date'];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class, 'purchase_id');
    }
}
