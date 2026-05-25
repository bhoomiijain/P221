<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Customer e-commerce collections for online pharmacy ordering.
 */
return new class extends Migration
{
    private array $collections = [
        'customer_addresses',
        'customer_carts',
        'customer_cart_items',
        'wishlist_items',
        'customer_orders',
        'customer_order_items',
        'customer_reviews',
        'prescriptions',
        'customer_payments',
        'customer_notifications',
        'ai_consultation_logs',
        'refill_reminders',
        'coupons',
    ];

    public function up(): void
    {
        foreach ($this->collections as $collection) {
            if (! Schema::connection('mongodb')->hasCollection($collection)) {
                Schema::connection('mongodb')->create($collection);
            }
        }

        $db = DB::connection('mongodb');

        $db->getCollection('customer_addresses')->createIndex(['user_id' => 1]);
        $db->getCollection('customer_carts')->createIndex(['user_id' => 1], ['unique' => true]);
        $db->getCollection('customer_cart_items')->createIndex(['cart_id' => 1, 'medicine_id' => 1]);
        $db->getCollection('wishlist_items')->createIndex(['user_id' => 1, 'medicine_id' => 1], ['unique' => true]);
        $db->getCollection('customer_orders')->createIndex(['user_id' => 1, 'created_at' => -1]);
        $db->getCollection('customer_orders')->createIndex(['order_number' => 1], ['unique' => true]);
        $db->getCollection('customer_order_items')->createIndex(['order_id' => 1]);
        $db->getCollection('customer_reviews')->createIndex(['medicine_id' => 1]);
        $db->getCollection('customer_reviews')->createIndex(['user_id' => 1, 'medicine_id' => 1]);
        $db->getCollection('prescriptions')->createIndex(['user_id' => 1]);
        $db->getCollection('customer_payments')->createIndex(['order_id' => 1]);
        $db->getCollection('customer_notifications')->createIndex(['user_id' => 1, 'read_at' => 1]);
        $db->getCollection('ai_consultation_logs')->createIndex(['user_id' => 1, 'order_id' => 1]);
        $db->getCollection('refill_reminders')->createIndex(['user_id' => 1, 'next_reminder_at' => 1]);
        $db->getCollection('coupons')->createIndex(['code' => 1], ['unique' => true]);

        // E-commerce catalog indexes on medicines
        $db->getCollection('medicines')->createIndex(['is_featured' => 1]);
        $db->getCollection('medicines')->createIndex(['is_trending' => 1]);
        $db->getCollection('medicines')->createIndex(['brand' => 1]);
        $db->getCollection('medicines')->createIndex(['prescription_required' => 1]);
    }

    public function down(): void
    {
        foreach (array_reverse($this->collections) as $collection) {
            Schema::connection('mongodb')->dropIfExists($collection);
        }
    }
};
