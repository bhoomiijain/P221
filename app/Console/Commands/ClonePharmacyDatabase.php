<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

/**
 * Copies an existing MongoDB pharmacy database into a new database,
 * then ensures customer/shop collections and indexes exist.
 */
class ClonePharmacyDatabase extends Command
{
    protected $signature = 'pharmacy:clone-database
                            {--from= : Source database (default: MONGODB_SOURCE_DATABASE)}
                            {--to= : Target database (default: MONGODB_DATABASE)}
                            {--fresh : Clear target collections before copying}
                            {--migrate : Run migrations on the target database}
                            {--seed : Seed demo + shop data on the target database}';

    protected $description = 'Copy existing pharmacy MongoDB into a new database with customer/shop collections';

    /** Collections used by pharmacist + supplier modules */
    private array $coreCollections = [
        'users', 'categories', 'medicines', 'batches', 'suppliers',
        'purchases', 'purchase_items', 'sales', 'sale_items',
        'inventory_alerts', 'personal_access_tokens',
        'supplier_medicines', 'supplier_orders',
        'cache', 'jobs', 'failed_jobs', 'migrations',
    ];

    /** Customer online shop collections */
    private array $shopCollections = [
        'customer_addresses', 'customer_carts', 'customer_cart_items',
        'wishlist_items', 'customer_orders', 'customer_order_items',
        'customer_reviews', 'prescriptions', 'customer_payments',
        'customer_notifications', 'ai_consultation_logs', 'refill_reminders',
        'coupons', 'shop_banners', 'health_tips', 'shop_testimonials',
    ];

    public function handle(): int
    {
        $from = $this->option('from') ?: env('MONGODB_SOURCE_DATABASE', 'retail_pharmacy');
        $to = $this->option('to') ?: env('MONGODB_DATABASE', 'retail_pharmacy_shop');
        $uri = env('MONGODB_URI', 'mongodb://127.0.0.1:27017');

        if ($from === $to) {
            $this->error("Source and target must differ (from={$from}, to={$to}).");

            return self::FAILURE;
        }

        $this->info("MongoDB URI: {$uri}");
        $this->info("Copy: [{$from}] → [{$to}]");

        try {
            $client = DB::connection('mongodb')->getClient();
        } catch (\Throwable $e) {
            $this->error('Cannot connect to MongoDB: '.$e->getMessage());

            return self::FAILURE;
        }

        $source = $client->selectDatabase($from);
        $target = $client->selectDatabase($to);

        if ($this->option('fresh')) {
            $this->warn("Dropping all collections in [{$to}]...");
            foreach ($target->listCollections() as $coll) {
                $name = $coll->getName();
                if (! str_starts_with($name, 'system.')) {
                    $target->dropCollection($name);
                }
            }
        }

        $copied = 0;
        $docs = 0;

        foreach ($source->listCollections() as $collInfo) {
            $name = $collInfo->getName();
            if (str_starts_with($name, 'system.')) {
                continue;
            }

            $sourceColl = $source->selectCollection($name);
            $count = $sourceColl->countDocuments();

            if ($this->option('fresh')) {
                $target->dropCollection($name);
            }

            $targetColl = $target->selectCollection($name);

            if ($count === 0) {
                $this->line("  · {$name}: (empty, collection ensured)");
                $copied++;

                continue;
            }

            $batch = [];
            $cursor = $sourceColl->find();
            $inserted = 0;

            foreach ($cursor as $document) {
                $batch[] = $document;
                if (count($batch) >= 500) {
                    $targetColl->insertMany($batch);
                    $inserted += count($batch);
                    $batch = [];
                }
            }

            if ($batch !== []) {
                $targetColl->insertMany($batch);
                $inserted += count($batch);
            }

            $docs += $inserted;
            $copied++;
            $this->line("  ✓ {$name}: {$inserted} documents");
        }

        foreach ($this->shopCollections as $name) {
            if (! $this->collectionExists($target, $name)) {
                $target->createCollection($name);
                $this->line("  + {$name}: created (shop collection)");
            }
        }

        $this->newLine();
        $this->info("Copied {$copied} collections ({$docs} documents).");

        $this->useDatabase($to);

        if ($this->option('migrate')) {
            $this->info('Running migrations on target database...');
            Artisan::call('migrate', ['--force' => true]);
            $this->line(Artisan::output());
        }

        if ($this->option('seed')) {
            $this->info('Seeding target database (pharmacy + shop)...');
            Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\ShopDatabaseSeeder',
                '--force' => true,
            ]);
            $this->line(Artisan::output());
        }

        $this->newLine();
        $this->info('Done. Update your .env:');
        $this->line("  MONGODB_SOURCE_DATABASE={$from}");
        $this->line("  MONGODB_DATABASE={$to}");
        $this->line('Then: php artisan config:clear && php artisan serve');

        return self::SUCCESS;
    }

    private function collectionExists(\MongoDB\Database $db, string $name): bool
    {
        foreach ($db->listCollections(['filter' => ['name' => $name]]) as $coll) {
            return true;
        }

        return false;
    }

    private function useDatabase(string $database): void
    {
        config(['database.connections.mongodb.database' => $database]);
        config(['database.default' => 'mongodb']);
        DB::purge('mongodb');
        DB::reconnect('mongodb');
    }
}
