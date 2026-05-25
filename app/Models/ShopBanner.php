<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class ShopBanner extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'shop_banners';

    protected $fillable = [
        'title', 'subtitle', 'code', 'color', 'image', 'link', 'active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
