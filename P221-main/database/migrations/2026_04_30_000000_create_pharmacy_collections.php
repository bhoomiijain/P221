<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $collections = [
        'users',
        'categories',
        'medicines',
        'batches',
        'suppliers',
        'purchases',
        'purchase_items',
        'sales',
        'sale_items',
        'inventory_alerts',
        'personal_access_tokens',
        'cache',
        'jobs',
        'failed_jobs',
        'supplier_medicines',
        'supplier_orders',
    ];

    public function up(): void
    {
        foreach ($this->collections as $collection) {
            if (! Schema::connection('mongodb')->hasCollection($collection)) {
                Schema::connection('mongodb')->create($collection);
            }
        }

        $db = DB::connection('mongodb');

        $db->getCollection('users')->createIndex(['email' => 1], ['unique' => true]);
        $db->getCollection('users')->createIndex(['role' => 1]);

        $db->getCollection('categories')->createIndex(['name' => 1], ['unique' => true]);

        $db->getCollection('medicines')->createIndex(['category_id' => 1]);
        $db->getCollection('medicines')->createIndex(['search_key' => 1]);
        $db->getCollection('medicines')->createIndex(['name' => 'text']);

        $db->getCollection('batches')->createIndex(['medicine_id' => 1]);
        $db->getCollection('batches')->createIndex(['expiry_date' => 1]);
        $db->getCollection('batches')->createIndex(['batch_number' => 1]);
        $db->getCollection('batches')->createIndex(['medicine_id' => 1, 'expiry_date' => 1, 'quantity' => 1]);
        $db->getCollection('batches')->createIndex(['medicine_id' => 1, 'batch_number' => 1], ['unique' => true]);

        $db->getCollection('suppliers')->createIndex(['search_key' => 1]);
        $db->getCollection('purchases')->createIndex(['supplier_id' => 1, 'purchase_date' => -1]);
        $db->getCollection('purchase_items')->createIndex(['purchase_id' => 1]);
        $db->getCollection('purchase_items')->createIndex(['batch_id' => 1]);
        $db->getCollection('sales')->createIndex(['user_id' => 1, 'created_at' => -1]);
        $db->getCollection('sale_items')->createIndex(['sale_id' => 1]);
        $db->getCollection('sale_items')->createIndex(['batch_id' => 1]);
        $db->getCollection('inventory_alerts')->createIndex(['type' => 1, 'resolved_at' => 1]);
        $db->getCollection('personal_access_tokens')->createIndex(['token' => 1], ['unique' => true]);
        $db->getCollection('personal_access_tokens')->createIndex(['tokenable_type' => 1, 'tokenable_id' => 1]);

        $db->getCollection('supplier_medicines')->createIndex(['supplier_id' => 1]);
        $db->getCollection('supplier_orders')->createIndex(['supplier_id' => 1]);
        $db->getCollection('supplier_orders')->createIndex(['pharmacist_id' => 1]);
    }

    public function down(): void
    {
        foreach (array_reverse($this->collections) as $collection) {
            Schema::connection('mongodb')->dropIfExists($collection);
        }
    }
};
