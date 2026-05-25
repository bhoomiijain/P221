<?php

namespace App\Jobs;

use App\Models\InventoryAlert;
use App\Services\InventoryService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateInventoryAlerts implements ShouldQueue
{
    use Queueable;

    public function handle(InventoryService $inventory): void
    {
        $pharmacists = \App\Models\User::where('role', 'pharmacist')->get();

        foreach ($pharmacists as $pharmacist) {
            \Illuminate\Support\Facades\Auth::setUser($pharmacist);

            foreach ($inventory->lowStock() as $row) {
                InventoryAlert::query()->updateOrCreate(
                    ['user_id' => $pharmacist->id, 'type' => 'low_stock', 'medicine_id' => $row['id'], 'resolved_at' => null],
                    ['message' => "{$row['name']} is low on stock ({$row['stock']} units).", 'level' => 'warning']
                );
            }

            foreach ($inventory->expiringBatches() as $batch) {
                InventoryAlert::query()->updateOrCreate(
                    ['user_id' => $pharmacist->id, 'type' => 'expiry', 'batch_id' => $batch->getKey(), 'resolved_at' => null],
                    [
                        'medicine_id' => $batch->medicine_id,
                        'message' => "{$batch->medicine?->name} batch {$batch->batch_number} expires on {$batch->expiry_date->format('Y-m-d')}.",
                        'level' => 'critical',
                    ]
                );
            }
        }
    }
}
