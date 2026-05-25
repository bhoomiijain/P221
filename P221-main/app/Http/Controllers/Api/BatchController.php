<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class BatchController extends Controller
{
    public function index(Request $request)
    {
        return Batch::query()
            ->where('user_id', auth()->id())
            ->with('medicine')
            ->when($request->string('medicine_id')->toString(), fn ($query, $medicineId) => $query->where('medicine_id', $medicineId))
            ->orderBy('expiry_date')
            ->paginate(25);
    }

    public function store(Request $request, InventoryService $inventory)
    {
        $data = $request->validate([
            'medicine_id'    => ['required', 'string'],
            'batch_number'   => ['required', 'string', 'max:120'],
            'expiry_date'    => ['required', 'date', 'after_or_equal:today'],
            'quantity'       => ['required', 'integer', 'min:0'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            // selling_price is now computed from profit_pct — do NOT accept it directly
            'profit_pct'     => ['nullable', 'numeric', 'min:0', 'max:10000'],
        ]);

        $user = auth()->user();

        // Fall back to user's global default if profit_pct not provided
        $profitPct = isset($data['profit_pct'])
            ? (float) $data['profit_pct']
            : (float) ($user->default_profit_pct ?? 0);

        $purchasePrice  = (float) $data['purchase_price'];
        $sellingPrice   = round($purchasePrice * (1 + $profitPct / 100), 2);

        $batch = Batch::create([
            'user_id'        => $user->getKey(),
            'medicine_id'    => $data['medicine_id'],
            'batch_number'   => $data['batch_number'],
            'expiry_date'    => $data['expiry_date'],
            'quantity'       => $data['quantity'],
            'purchase_price' => $purchasePrice,
            'selling_price'  => $sellingPrice,
            'profit_pct'     => $profitPct,
        ]);

        // Generate low-stock alerts for this user after adding stock
        $inventory->generateAlerts($user->getKey());

        return response()->json($batch->load('medicine'), 201);
    }

    public function show(Batch $batch)
    {
        return $batch->load('medicine');
    }

    public function update(Request $request, Batch $batch, InventoryService $inventory)
    {
        $data = $request->validate([
            'batch_number'   => ['sometimes', 'required', 'string', 'max:120'],
            'expiry_date'    => ['sometimes', 'required', 'date'],
            'quantity'       => ['sometimes', 'required', 'integer', 'min:0'],
            'purchase_price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'profit_pct'     => ['sometimes', 'required', 'numeric', 'min:0', 'max:10000'],
        ]);

        // Recompute selling_price if purchase_price or profit_pct changed
        $purchasePrice = isset($data['purchase_price'])
            ? (float) $data['purchase_price']
            : (float) $batch->purchase_price;

        $profitPct = isset($data['profit_pct'])
            ? (float) $data['profit_pct']
            : (float) ($batch->profit_pct ?? 0);

        $data['profit_pct']    = $profitPct;
        $data['selling_price'] = round($purchasePrice * (1 + $profitPct / 100), 2);

        $batch->update($data);

        // Re-check alerts after any stock change
        $inventory->generateAlerts($batch->user_id);

        return $batch->fresh('medicine');
    }

    public function destroy(Batch $batch)
    {
        abort_if($batch->saleItems()->exists(), 422, 'Cannot delete a batch used by sales.');
        $batch->delete();

        return response()->noContent();
    }
}
