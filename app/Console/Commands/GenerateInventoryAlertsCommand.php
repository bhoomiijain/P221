<?php

namespace App\Console\Commands;

use App\Jobs\GenerateInventoryAlerts;
use Illuminate\Console\Command;

class GenerateInventoryAlertsCommand extends Command
{
    protected $signature = 'pharmacy:alerts';
    protected $description = 'Generate low-stock and 30-day expiry alerts.';

    public function handle(): int
    {
        GenerateInventoryAlerts::dispatch();
        $this->info('Inventory alert generation queued.');

        return self::SUCCESS;
    }
}
