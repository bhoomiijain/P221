<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\InventoryAlert;
use App\Models\Medicine;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(InventoryService $inventory)
    {
        $userId = auth()->id();

        return Medicine::query()
            ->with('category')
            ->orderBy('name')
            ->get()
            ->map(fn (Medicine $medicine) => [
                'id'             => $medicine->getKey(),
                'name'           => $medicine->name,
                'category'       => $medicine->category?->name,
                'total_stock'    => (int) Batch::where('user_id', $userId)
                                        ->where('medicine_id', $medicine->getKey())
                                        ->sum('quantity'),
                'sellable_stock' => $inventory->availableStock($medicine->getKey()),
            ]);
    }

    public function alerts()
    {
        if (auth()->check() && auth()->user()->isSupplier()) {
            return response()->json([]);
        }

        return InventoryAlert::query()
            ->where('user_id', auth()->id())
            ->whereNull('resolved_at')
            ->latest()
            ->get()
            ->map(fn ($alert) => [
                'id'          => $alert->getKey(),
                'type'        => $alert->type,
                'level'       => $alert->level,
                'message'     => $alert->message,
                'medicine_id' => $alert->medicine_id,
                'batch_id'    => $alert->batch_id,
                'created_at'  => $alert->created_at,
            ]);
    }

    public function resolveAlert(string $id)
    {
        $alert = InventoryAlert::findOrFail($id);
        $alert->update(['resolved_at' => now()]);
        return response()->json(['message' => 'Alert resolved']);
    }

    /**
     * Update the authenticated pharmacist's global default profit percentage.
     */
    public function updateProfitSettings(Request $request)
    {
        $data = $request->validate([
            'default_profit_pct' => ['required', 'numeric', 'min:0', 'max:10000'],
        ]);

        $user = auth()->user();
        $user->update(['default_profit_pct' => (float) $data['default_profit_pct']]);

        return response()->json([
            'default_profit_pct' => (float) $user->fresh()->default_profit_pct,
            'message'            => 'Global profit percentage updated.',
        ]);
    }

    public function search(Request $request, InventoryService $inventory)
    {
        // Normalise: trim edges, collapse repeated spaces, lowercase
        $raw = trim(strtolower($request->string('q')->toString()));
        $raw = preg_replace('/\s+/', ' ', $raw);

        if ($raw === '') {
            return response()->json([]);
        }

        $userId = auth()->id();

        // Split into individual keyword tokens so "para 500mg" matches
        // medicines whose name contains *all* tokens in any position.
        $tokens = array_filter(explode(' ', $raw));

        $query = Medicine::query();

        foreach ($tokens as $token) {
            // MongoDB regex: case-insensitive, matches token anywhere in the name
            $query->where('name', 'regex', new \MongoDB\BSON\Regex($token, 'i'));
        }

        $medicines = $query
            ->orderBy('name')
            ->limit(15)
            ->get()
            ->map(function (Medicine $medicine) use ($inventory, $userId) {
                $stock = $inventory->availableStock($medicine->getKey());

                // Get selling price from the earliest non-expired batch (FIFO order)
                $nextBatch = Batch::query()
                    ->where('user_id', $userId)
                    ->where('medicine_id', $medicine->getKey())
                    ->where('quantity', '>', 0)
                    ->where('expiry_date', '>=', today())
                    ->orderBy('expiry_date')
                    ->orderBy('created_at')
                    ->first();

                return [
                    'id'            => $medicine->getKey(),
                    'name'          => $medicine->name,
                    'stock'         => $stock,
                    'selling_price' => $nextBatch ? (float) $nextBatch->selling_price : 0,
                ];
            })
            // Sort: in-stock first, then out-of-stock at the bottom
            ->sortByDesc('stock')
            ->values();

        return response()->json($medicines);
    }
}

