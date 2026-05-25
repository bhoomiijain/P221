<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Medicine;
use App\Models\Supplier;
use App\Models\SupplierMedicine;
use App\Models\SupplierOrder;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SupplierOrderController extends Controller
{
    /**
     * Supplier: view all incoming orders for them.
     */
    public function index()
    {
        $user = auth()->user();

        if (! $user->isSupplier()) {
            abort(403, 'Only suppliers can view this page.');
        }

        $orders = SupplierOrder::query()
            ->where('supplier_id', $user->getKey())
            ->with(['pharmacist', 'medicine'])
            ->latest()
            ->get()
            ->map(function ($order) {
                // Enrich items array with medicine names if using new multi-item format
                if (!empty($order->items)) {
                    $medicineIds = collect($order->items)->pluck('medicine_id')->filter()->unique();
                    $medicines   = Medicine::whereIn('_id', $medicineIds->values()->all())->get()->keyBy(fn($m) => (string)$m->getKey());
                    $order->items = collect($order->items)->map(function ($item) use ($medicines) {
                        $item['medicine_name'] = $medicines[(string)($item['medicine_id'] ?? '')]?->name ?? '—';
                        return $item;
                    })->all();
                }
                return $order;
            });

        return Inertia::render('Suppliers/Orders', [
            'orders' => $orders,
        ]);
    }

    /**
     * Pharmacist: show full-page ordering cart for a specific supplier.
     */
    public function orderPage(string $supplierId)
    {
        $user = auth()->user();

        if (! $user->isPharmacist()) {
            abort(403, 'Only pharmacists can place orders.');
        }

        $supplierUser = User::findOrFail($supplierId);

        // Get the Supplier profile record
        $supplier = Supplier::where('user_id', $supplierId)->first();

        // Get their stocked medicines
        $supplierMedicines = SupplierMedicine::with('medicine')
            ->where('supplier_id', $supplierId)
            ->where('quantity', '>', 0)
            ->get()
            ->map(fn($sm) => [
                'medicine_id'   => (string) $sm->medicine_id,
                'medicine_name' => $sm->medicine?->name ?? '—',
                'description'   => $sm->medicine?->description ?? '',
                'image'         => $sm->medicine?->image ?? null,
                'available_qty' => $sm->quantity,
                'unit_price'    => (float) $sm->price,
            ]);

        return Inertia::render('Suppliers/Order', [
            'supplier'         => [
                'id'          => (string) $supplierUser->getKey(),
                'name'        => $supplierUser->name,
                'business'    => $supplierUser->business_name ?? $supplier?->name ?? $supplierUser->name,
                'avatar_url'  => $supplierUser->avatar_url ?? null,
                'phone'       => $supplierUser->phone ?? $supplier?->phone ?? null,
                'email'       => $supplierUser->email,
                'address'     => $supplierUser->address ?? $supplier?->address ?? null,
                'city'        => $supplierUser->city ?? $supplier?->city ?? null,
            ],
            'supplierMedicines' => $supplierMedicines,
        ]);
    }

    /**
     * Pharmacist: submit a multi-item order.
     */
    public function store(Request $request)
    {
        $request->validate([
            'supplier_id'            => 'required|string',
            'items'                  => 'required|array|min:1',
            'items.*.medicine_id'    => 'required|string',
            'items.*.quantity'       => 'required|integer|min:1',
        ]);

        $supplierId = $request->supplier_id;
        $orderItems = [];
        $totalPrice = 0;

        foreach ($request->items as $item) {
            $sm = SupplierMedicine::query()
                ->where('supplier_id', $supplierId)
                ->where('medicine_id', $item['medicine_id'])
                ->first();

            if (!$sm) {
                return back()->withErrors(['items' => "Medicine not found in supplier's inventory."]);
            }

            if ($sm->quantity < $item['quantity']) {
                $med = Medicine::find($item['medicine_id']);
                return back()->withErrors(['items' => "Not enough stock for {$med?->name}. Available: {$sm->quantity}"]);
            }

            $lineTotal    = $item['quantity'] * (float)$sm->price;
            $totalPrice  += $lineTotal;

            $orderItems[] = [
                'medicine_id' => $item['medicine_id'],
                'quantity'    => (int)$item['quantity'],
                'unit_price'  => (float)$sm->price,
                'line_total'  => $lineTotal,
            ];
        }

        SupplierOrder::create([
            'pharmacist_id'  => auth()->id(),
            'supplier_id'    => $supplierId,
            'items'          => $orderItems,
            'total_price'    => $totalPrice,
            'payment_method' => 'cash',
            'status'         => 'pending',
        ]);

        return redirect('/suppliers')->with('success', 'Order request sent to supplier!');
    }

    /**
     * Supplier: approve an order → deduct stock → add batch to pharmacist inventory.
     */
    public function approve(Request $request, SupplierOrder $order)
    {
        if ((string) $order->supplier_id !== (string) auth()->id()) {
            abort(403);
        }

        if ($order->status !== 'pending') {
            return back()->withErrors(['status' => 'Order is not pending.']);
        }

        // Multi-item format
        if (!empty($order->items)) {
            foreach ($order->items as $item) {
                $sm = SupplierMedicine::query()
                    ->where('supplier_id', $order->supplier_id)
                    ->where('medicine_id', $item['medicine_id'])
                    ->firstOrFail();

                if ($sm->quantity < $item['quantity']) {
                    return back()->withErrors(['quantity' => 'Not enough stock to fulfill this order.']);
                }

                $sm->decrement('quantity', $item['quantity']);

                Batch::create([
                    'user_id'        => $order->pharmacist_id,
                    'medicine_id'    => $item['medicine_id'],
                    'batch_number'   => 'SUP-' . strtoupper(substr(uniqid(), -6)),
                    'expiry_date'    => now()->addYear(),
                    'quantity'       => $item['quantity'],
                    'purchase_price' => $item['unit_price'],
                    'selling_price'  => $item['unit_price'],
                    'profit_pct'     => 0,
                ]);
            }
        } else {
            // Legacy single-item fallback
            $sm = SupplierMedicine::query()
                ->where('supplier_id', $order->supplier_id)
                ->where('medicine_id', $order->medicine_id)
                ->firstOrFail();

            if ($sm->quantity < $order->quantity) {
                return back()->withErrors(['quantity' => 'Not enough stock.']);
            }

            $sm->decrement('quantity', $order->quantity);

            Batch::create([
                'user_id'        => $order->pharmacist_id,
                'medicine_id'    => $order->medicine_id,
                'batch_number'   => 'SUP-' . strtoupper(substr(uniqid(), -6)),
                'expiry_date'    => now()->addYear(),
                'quantity'       => $order->quantity,
                'purchase_price' => $sm->price,
                'selling_price'  => $sm->price,
                'profit_pct'     => 0,
            ]);
        }

        $order->update(['status' => 'payment_received']);

        return redirect()->back()->with('success', 'Order approved and stock dispatched.');
    }
}
