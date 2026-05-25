<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SupplierPageController extends Controller
{
    public function __invoke()
    {
        // Load all suppliers with their linked user (for avatar, email)
        $suppliers = Supplier::query()
            ->orderBy('name')
            ->get()
            ->map(function ($sup) {
                // Attach the linked user's avatar and email
                if ($sup->user_id) {
                    $user = User::find($sup->user_id);
                    if ($user) {
                        $sup->user         = $user->only(['name', 'email', 'avatar_url', 'phone', 'city', 'state']);
                        $sup->email        ??= $user->email;
                        $sup->phone        ??= $user->phone;
                        $sup->city         ??= $user->city;
                        $sup->state        ??= $user->state;
                        $sup->avatar_url   ??= $user->avatar_url;
                    }
                }
                return $sup;
            });

        $supplierMedicines = \App\Models\SupplierMedicine::with('medicine')->get();
        $medicines         = \App\Models\Medicine::all();

        return Inertia::render('Suppliers/Index', [
            'suppliers'         => $suppliers,
            'supplierMedicines' => $supplierMedicines,
            'medicines'         => $medicines,
        ]);
    }
}
