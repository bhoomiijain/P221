<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Services\InventoryService;
use Inertia\Inertia;

class InventoryPageController extends Controller
{
    public function __invoke(InventoryService $inventory)
    {
        $search = trim(request()->get('search', ''));
        $user   = auth()->user();

        $query = Batch::query()->where('user_id', $user->getKey())->with('medicine')->orderBy('expiry_date');

        if ($search !== '') {
            // MongoDB regex — case-insensitive search on batch_number
            $pattern = new \MongoDB\BSON\Regex($search, 'i');
            $query->where(function ($q) use ($pattern, $search) {
                $q->where('batch_number', $pattern);
            });

            // We also want medicine name matches — fetch all and filter in PHP
            // because the medicine name lives in a different collection
            $allBatches = Batch::query()->where('user_id', $user->getKey())->with('medicine')->orderBy('expiry_date')->get();
            $batches = $allBatches->filter(function ($b) use ($search) {
                $matchBatch    = preg_match('/' . preg_quote($search, '/') . '/i', $b->batch_number ?? '');
                $matchMedicine = preg_match('/' . preg_quote($search, '/') . '/i', $b->medicine?->name ?? '');
                return $matchBatch || $matchMedicine;
            })->values();
        } else {
            $batches = $query->limit(100)->get();
        }

        return Inertia::render('Inventory/Index', [
            'batches'             => $batches,
            'lowStock'            => $inventory->lowStock(),
            'expiring'            => $inventory->expiringBatches(),
            'filters'             => ['search' => $search],
            'defaultProfitPct'    => (float) ($user->default_profit_pct ?? 0),
        ]);
    }
}
