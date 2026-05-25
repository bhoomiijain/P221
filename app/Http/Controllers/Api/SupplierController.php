<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        return Supplier::query()
            ->when($request->string('search')->toString(), fn ($query, $search) => $query->where('search_key', 'like', strtolower($search).'%'))
            ->orderBy('name')
            ->paginate(25);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => ['required', 'string', 'max:180'],
            'contact_info' => ['nullable', 'string', 'max:1000'],
            'phone'        => ['nullable', 'string', 'max:30'],
            'email'        => ['nullable', 'email', 'max:120'],
            'address'      => ['nullable', 'string', 'max:500'],
        ]);
        $data['search_key'] = strtolower($data['name']);

        return response()->json(Supplier::create($data), 201);
    }

    public function show(Supplier $supplier)
    {
        return $supplier->load('purchases');
    }

    public function update(Request $request, Supplier $supplier)
    {
        $data = $request->validate([
            'name'         => ['sometimes', 'required', 'string', 'max:180'],
            'contact_info' => ['nullable', 'string', 'max:1000'],
            'phone'        => ['nullable', 'string', 'max:30'],
            'email'        => ['nullable', 'email', 'max:120'],
            'address'      => ['nullable', 'string', 'max:500'],
        ]);
        if (isset($data['name'])) {
            $data['search_key'] = strtolower($data['name']);
        }

        $supplier->update($data);

        return $supplier->fresh();
    }

    public function destroy(Supplier $supplier)
    {
        abort_if($supplier->purchases()->exists(), 422, 'Cannot delete a supplier with purchases.');
        $supplier->delete();

        return response()->noContent();
    }
}
