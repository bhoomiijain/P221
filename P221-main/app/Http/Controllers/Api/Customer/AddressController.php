<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index()
    {
        return response()->json([
            'addresses' => CustomerAddress::where('user_id', auth()->id())->latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'label' => 'nullable|string|max:50',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address_line' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'pincode' => 'required|string|max:10',
            'is_default' => 'boolean',
        ]);

        if ($request->boolean('is_default')) {
            CustomerAddress::where('user_id', auth()->id())->update(['is_default' => false]);
        }

        $address = CustomerAddress::create([...$data, 'user_id' => auth()->id()]);

        return response()->json(['address' => $address], 201);
    }

    public function update(Request $request, string $id)
    {
        $address = CustomerAddress::where('user_id', auth()->id())->findOrFail($id);
        $data = $request->validate([
            'label' => 'nullable|string|max:50',
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'address_line' => 'sometimes|string|max:500',
            'city' => 'sometimes|string|max:100',
            'state' => 'sometimes|string|max:100',
            'pincode' => 'sometimes|string|max:10',
            'is_default' => 'boolean',
        ]);

        if ($request->boolean('is_default')) {
            CustomerAddress::where('user_id', auth()->id())->update(['is_default' => false]);
        }

        $address->update($data);

        return response()->json(['address' => $address->fresh()]);
    }

    public function destroy(string $id)
    {
        CustomerAddress::where('user_id', auth()->id())->findOrFail($id)->delete();

        return response()->json(['message' => 'Address deleted']);
    }
}
