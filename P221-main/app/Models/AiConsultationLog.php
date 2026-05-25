<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;

class AiConsultationLog extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'ai_consultation_logs';

    protected $fillable = [
        'user_id', 'order_id', 'responses', 'risk_level', 'risk_flags',
        'approved', 'messages',
    ];

    protected function casts(): array
    {
        return [
            'responses' => 'array',
            'risk_flags' => 'array',
            'messages' => 'array',
            'approved' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
