<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class ShopTestimonial extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'shop_testimonials';

    protected $fillable = ['name', 'text', 'rating', 'active', 'sort_order'];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
