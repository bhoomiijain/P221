<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Services\BillingService;
use App\Services\InventoryService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function checkout(Request $request, BillingService $billing, InventoryService $inventory)
    {
        $data = $request->validate([
            'items'           => ['required', 'array', 'min:1'],
            'items.*.medicine_id' => ['required', 'string'],
            'items.*.quantity'    => ['required', 'integer', 'min:1'],
            'discount'        => ['nullable', 'numeric', 'min:0'],
            'tax'             => ['nullable', 'numeric', 'min:0'],
            'customer_name'   => ['nullable', 'string', 'max:120'],
            'customer_phone'  => ['nullable', 'string', 'max:20'],
            'payment_method'  => ['nullable', 'string', 'in:cash,upi,card,insurance'],
        ]);

        $sale = $billing->checkout(
            $request->user()->getKey(),
            $data['items'],
            (float) ($data['discount'] ?? 0),
            (float) ($data['tax'] ?? 0),
            $data['customer_name'] ?? null,
            $data['customer_phone'] ?? null,
            $data['payment_method'] ?? 'cash',
        );

        // Generate low-stock alerts after deducting stock
        $inventory->generateAlerts($request->user()->getKey());

        return response()->json($sale, 201);
    }

    public function invoice(Sale $sale)
    {
        $sale->load('items.batch.medicine', 'user');

        return Pdf::loadView('pdf.invoice', ['sale' => $sale])
            ->setPaper('a4')
            ->stream("bill-{$sale->getKey()}.pdf"); // stream = opens in browser tab
    }

    public function receipt(Sale $sale)
    {
        $sale->load('items.batch.medicine', 'user');

        return Pdf::loadView('pdf.receipt', ['sale' => $sale])
            ->setPaper([0, 0, 226.77, 600], 'portrait') // 80mm width
            ->download("receipt-{$sale->getKey()}.pdf");
    }

    public function history(Request $request)
    {
        $query = Sale::query()
            ->with('user', 'items.batch.medicine')
            ->latest();

        if ($request->has('date_from')) {
            $query->where('created_at', '>=', $request->date('date_from')->startOfDay());
        }
        if ($request->has('date_to')) {
            $query->where('created_at', '<=', $request->date('date_to')->endOfDay());
        }
        if ($request->has('payment_method')) {
            $query->where('payment_method', $request->input('payment_method'));
        }

        return $query->paginate(20);
    }
}
