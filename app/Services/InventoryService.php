<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\Medicine;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    public function availableStock(string $medicineId): int
    {
        return (int) Batch::query()
            ->where('user_id', auth()->id())
            ->where('medicine_id', $medicineId)
            ->where('expiry_date', '>=', today())
            ->sum('quantity');
    }

    /**
     * @return array<int, array{batch: Batch, quantity: int, selling_price: float}>
     */
    public function reserveFifo(string $medicineId, int $quantity): array
    {
        if ($quantity < 1) {
            throw ValidationException::withMessages(['items' => 'Sale quantity must be at least 1.']);
        }

        $available = $this->availableStock($medicineId);

        if ($available < $quantity) {
            $medicine = Medicine::find($medicineId);
            $name = $medicine?->name ?? 'Selected medicine';

            throw ValidationException::withMessages([
                'items' => "{$name} has only {$available} sellable units available.",
            ]);
        }

        $remaining = $quantity;
        $allocations = [];

        $batches = Batch::query()
            ->where('user_id', auth()->id())
            ->where('medicine_id', $medicineId)
            ->where('quantity', '>', 0)
            ->where('expiry_date', '>=', today())
            ->orderBy('expiry_date')
            ->orderBy('created_at')
            ->get();

        foreach ($batches as $batch) {
            if ($remaining === 0) {
                break;
            }

            if (Carbon::parse($batch->expiry_date)->isBefore(today())) {
                continue;
            }

            $take = min($remaining, (int) $batch->quantity);
            $allocations[] = [
                'batch' => $batch,
                'quantity' => $take,
                'selling_price' => (float) $batch->selling_price,
            ];

            $remaining -= $take;
        }

        if ($remaining > 0) {
            throw ValidationException::withMessages(['items' => 'Unable to allocate stock without using expired batches.']);
        }

        return $allocations;
    }

    public function deduct(Batch $batch, int $quantity): void
    {
        $freshBatch = Batch::query()->findOrFail($batch->getKey());
        $nextQuantity = (int) $freshBatch->quantity - $quantity;

        if ($freshBatch->isExpired()) {
            throw ValidationException::withMessages(['items' => "Batch {$freshBatch->batch_number} is expired."]);
        }

        if ($nextQuantity < 0) {
            throw ValidationException::withMessages(['items' => "Batch {$freshBatch->batch_number} would go negative."]);
        }

        $freshBatch->update(['quantity' => $nextQuantity]);
    }

    public function lowStock(int $threshold = 10)
    {
        return Medicine::query()
            ->orderBy('name')
            ->get()
            ->filter(function ($medicine) {
                // If it doesn't belong to the user, check if they have batches for it
                if ($medicine->user_id === auth()->id()) return true;
                return Batch::where('user_id', auth()->id())->where('medicine_id', $medicine->getKey())->exists();
            })
            ->map(fn (Medicine $medicine) => [
                'id' => $medicine->getKey(),
                'name' => $medicine->name,
                'stock' => $this->availableStock($medicine->getKey()),
            ])
            ->filter(fn (array $row) => $row['stock'] <= $threshold)
            ->values();
    }

    /**
     * Generate (or resolve) low-stock alerts for a specific user.
     * Call this after any stock change (batch creation, sale checkout).
     */
    public function generateAlerts(string $userId, int $threshold = 10): void
    {
        // Collect all medicine_ids this user has batches for
        $medicineIds = Batch::where('user_id', $userId)
            ->pluck('medicine_id')
            ->unique()
            ->filter()
            ->values()
            ->all();

        foreach ($medicineIds as $medicineId) {
            $stock = (int) Batch::query()
                ->where('user_id', $userId)
                ->where('medicine_id', $medicineId)
                ->where('expiry_date', '>=', today())
                ->sum('quantity');

            $medicine = Medicine::find($medicineId);
            if (! $medicine) continue;

            $existingAlert = \App\Models\InventoryAlert::where('user_id', $userId)
                ->where('medicine_id', $medicineId)
                ->where('type', 'low_stock')
                ->whereNull('resolved_at')
                ->first();

            if ($stock <= $threshold) {
                // Create alert if none exists
                if (! $existingAlert) {
                    $level   = $stock === 0 ? 'critical' : 'warning';
                    $message = $stock === 0
                        ? "{$medicine->name} is out of stock!"
                        : "{$medicine->name} is low on stock ({$stock} units left).";

                    \App\Models\InventoryAlert::create([
                        'user_id'     => $userId,
                        'medicine_id' => $medicineId,
                        'type'        => 'low_stock',
                        'level'       => $level,
                        'message'     => $message,
                    ]);
                } else {
                    // Update level & message in case stock changed further
                    $level   = $stock === 0 ? 'critical' : 'warning';
                    $message = $stock === 0
                        ? "{$medicine->name} is out of stock!"
                        : "{$medicine->name} is low on stock ({$stock} units left).";
                    $existingAlert->update(['level' => $level, 'message' => $message]);
                }
            } else {
                // Stock is healthy — resolve existing alert if any
                if ($existingAlert) {
                    $existingAlert->update(['resolved_at' => now()]);
                }
            }
        }
    }

    public function expiringBatches(int $days = 30)
    {
        return Batch::query()
            ->where('user_id', auth()->id())
            ->with('medicine')
            ->where('quantity', '>', 0)
            ->where('expiry_date', '>=', today())
            ->where('expiry_date', '<=', today()->addDays($days))
            ->orderBy('expiry_date')
            ->get();
    }
}
