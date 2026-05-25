<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;
use MongoDB\Laravel\Relations\HasMany;

class Medicine extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'medicines';

    protected $fillable = [
        'user_id', 'name', 'category_id', 'description', 'search_key', 'image',
        'manufacturer', 'brand', 'mrp', 'discount_percent', 'prescription_required',
        'rating_avg', 'review_count', 'popularity', 'ingredients', 'dosage',
        'side_effects', 'warnings', 'is_featured', 'is_trending', 'form', 'pack_size',
    ];

    protected function casts(): array
    {
        return [
            'mrp' => 'decimal:2',
            'discount_percent' => 'integer',
            'prescription_required' => 'boolean',
            'rating_avg' => 'decimal:1',
            'review_count' => 'integer',
            'popularity' => 'integer',
            'is_featured' => 'boolean',
            'is_trending' => 'boolean',
        ];
    }

    public function sellingPrice(): float
    {
        $mrp = (float) ($this->mrp ?? 0);
        $discount = (int) ($this->discount_percent ?? 0);

        return round($mrp * (1 - $discount / 100), 2);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class, 'medicine_id');
    }
}
