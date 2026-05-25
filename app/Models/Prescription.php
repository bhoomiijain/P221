<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;

class Prescription extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'prescriptions';

    protected $fillable = [
        'user_id', 'file_path', 'file_name', 'mime_type', 'status',
        'pharmacist_notes', 'ocr_data',
    ];

    protected function casts(): array
    {
        return ['ocr_data' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
