<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\SupplierMedicine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SupplierInventoryController extends Controller
{
    public function index()
    {
        $supplierId = Auth::id(); // The user_id of the supplier

        $medicines = Medicine::all();
        $supplierMedicines = SupplierMedicine::where('supplier_id', $supplierId)->get();

        return Inertia::render('Suppliers/Inventory', [
            'medicines' => $medicines,
            'supplierMedicines' => $supplierMedicines,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'medicine_id' => ['required', 'string'],
            'quantity'    => ['required', 'integer', 'min:0'],
            'price'       => ['required', 'numeric', 'min:0'],
        ]);

        $supplierId = Auth::id();

        SupplierMedicine::updateOrCreate(
            [
                'supplier_id' => $supplierId,
                'medicine_id' => $request->medicine_id,
            ],
            [
                'quantity' => $request->quantity,
                'price'    => $request->price,
            ]
        );

        return back()->with('success', 'Inventory updated successfully.');
    }
}
