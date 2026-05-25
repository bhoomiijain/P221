<?php

namespace App\Services;

use App\Jobs\GenerateInventoryAlerts;
use App\Models\Sale;
use App\Models\SaleItem;

class BillingService
{
    public function __construct(private readonly InventoryService $inventory)
    {
    }

    /**
     * @param array<int, array{medicine_id: string, quantity: int}> $items
     *
     * NOTE: DB transactions removed — standalone MongoDB does not support
     * multi-document transactions (requires replica set). Operations are
     * executed sequentially; FIFO allocation validation happens first so
     * stock errors are caught before any writes occur.
     */
    public function checkout(
        string $userId,
        array $items,
        float $discount = 0,
        float $tax = 0,
        ?string $customerName = null,
        ?string $customerPhone = null,
        string $paymentMethod = 'cash'
    ): Sale {
        // ── Phase 1: Validate & compute allocations (no writes yet) ──────────
        $allocations = [];
        $subtotal    = 0.0;

        foreach ($items as $item) {
            $medicineAllocations = $this->inventory->reserveFifo(
                $item['medicine_id'],
                (int) $item['quantity']
            );

            foreach ($medicineAllocations as $allocation) {
                $lineTotal     = $allocation['quantity'] * $allocation['selling_price'];
                $subtotal     += $lineTotal;
                $allocations[] = $allocation + [
                    'medicine_id' => $item['medicine_id'],
                    'line_total'  => $lineTotal,
                ];
            }
        }

        $total = max(0, $subtotal - $discount + $tax);

        // ── Phase 2: Persist Sale header ─────────────────────────────────────
        $sale = Sale::create([
            'user_id'        => $userId,
            'subtotal'       => round($subtotal, 2),
            'discount'       => round($discount, 2),
            'tax'            => round($tax, 2),
            'total_amount'   => round($total, 2),
            'customer_name'  => $customerName,
            'customer_phone' => $customerPhone,
            'payment_method' => $paymentMethod,
        ]);

        // ── Phase 3: Persist SaleItems & deduct stock ─────────────────────────
        foreach ($allocations as $allocation) {
            $batch = $allocation['batch'];

            SaleItem::create([
                'sale_id'       => $sale->getKey(),
                'batch_id'      => $batch->getKey(),
                'medicine_id'   => $allocation['medicine_id'],
                'quantity'      => $allocation['quantity'],
                'selling_price' => $allocation['selling_price'],
                'line_total'    => round($allocation['line_total'], 2),
            ]);

            $this->inventory->deduct($batch, $allocation['quantity']);
        }

        GenerateInventoryAlerts::dispatchAfterResponse();

        return $sale->load('items.batch.medicine', 'user');
    }
}
