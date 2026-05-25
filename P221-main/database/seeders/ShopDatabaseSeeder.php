<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Full setup for retail_pharmacy_shop: pharmacist/supplier + customer shop data.
 */
class ShopDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DatabaseSeeder::class,
            ShopContentSeeder::class,
        ]);
    }
}
