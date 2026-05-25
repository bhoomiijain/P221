<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index()
    {
        return Purchase::query()->with('supplier', 'items.batch.medicine')->latest('purchase_date')->paginate(20);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => ['required', 'string'],
            'purchase_date' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.medicine_id' => ['required', 'string'],
            'items.*.batch_id' => ['nullable', 'string'],
            'items.*.batch_number' => ['required_without:items.*.batch_id', 'string'],
            'items.*.expiry_date' => ['required_without:items.*.batch_id', 'date', 'after:today'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.purchase_price' => ['required', 'numeric', 'min:0'],
            'items.*.selling_price' => ['required_without:items.*.batch_id', 'numeric', 'min:0'],
        ]);

        return DB::connection('mongodb')->transaction(function () use ($data) {
            $purchase = Purchase::create([
                'supplier_id' => $data['supplier_id'],
                'purchase_date' => $data['purchase_date'],
            ]);

            foreach ($data['items'] as $item) {
                $batch = isset($item['batch_id'])
                    ? Batch::query()->findOrFail($item['batch_id'])
                    : Batch::create([
                        'medicine_id' => $item['medicine_id'],
                        'batch_number' => $item['batch_number'],
                        'expiry_date' => $item['expiry_date'],
                        'quantity' => 0,
                        'purchase_price' => $item['purchase_price'],
                        'selling_price' => $item['selling_price'],
                    ]);

                $batch->update([
                    'quantity' => (int) $batch->quantity + (int) $item['quantity'],
                    'purchase_price' => $item['purchase_price'],
                ]);

                PurchaseItem::create([
                    'purchase_id' => $purchase->getKey(),
                    'medicine_id' => $item['medicine_id'],
                    'batch_id' => $batch->getKey(),
                    'quantity' => $item['quantity'],
                    'purchase_price' => $item['purchase_price'],
                ]);
            }

            return response()->json($purchase->load('supplier', 'items.batch.medicine'), 201);
        });
    }

    public function show(Purchase $purchase)
    {
        return $purchase->load('supplier', 'items.batch.medicine');
    }
}
