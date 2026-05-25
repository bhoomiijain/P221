<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class InventoryAlert extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'inventory_alerts';

    protected $fillable = ['user_id', 'type', 'medicine_id', 'batch_id', 'message', 'level', 'resolved_at'];

    protected $casts = ['resolved_at' => 'datetime'];
}
