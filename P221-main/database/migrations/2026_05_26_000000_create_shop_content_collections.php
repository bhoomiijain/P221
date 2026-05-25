<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Shop landing-page content + any extra customer indexes.
 */
return new class extends Migration
{
    private array $collections = [
        'shop_banners',
        'health_tips',
        'shop_testimonials',
    ];

    public function up(): void
    {
        foreach ($this->collections as $collection) {
            if (! Schema::connection('mongodb')->hasCollection($collection)) {
                Schema::connection('mongodb')->create($collection);
            }
        }

        $db = DB::connection('mongodb');

        $db->getCollection('shop_banners')->createIndex(['active' => 1, 'sort_order' => 1]);
        $db->getCollection('health_tips')->createIndex(['active' => 1, 'sort_order' => 1]);
        $db->getCollection('shop_testimonials')->createIndex(['active' => 1]);
    }

    public function down(): void
    {
        foreach (array_reverse($this->collections) as $collection) {
            Schema::connection('mongodb')->dropIfExists($collection);
        }
    }
};
