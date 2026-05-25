<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;

class RefillReminder extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'refill_reminders';

    protected $fillable = [
        'user_id', 'medicine_id', 'quantity_days', 'next_reminder_at',
        'subscription_enabled', 'active',
    ];

    protected function casts(): array
    {
        return [
            'subscription_enabled' => 'boolean',
            'active' => 'boolean',
            'next_reminder_at' => 'datetime',
        ];
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class, 'medicine_id');
    }
}
