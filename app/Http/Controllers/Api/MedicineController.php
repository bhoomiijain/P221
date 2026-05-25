<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use Illuminate\Http\Request;

class MedicineController extends Controller
{
    public function index(Request $request)
    {
        return Medicine::query()
            ->with('category')
            ->when($request->string('search')->toString(), fn ($query, $search) => $query->where('search_key', 'like', strtolower($search).'%'))
            ->orderBy('name')
            ->paginate(25);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'category_id' => ['required', 'string'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $data['search_key'] = strtolower($data['name']);
        $data['user_id'] = auth()->id();

        return response()->json(Medicine::create($data), 201);
    }

    public function show(Medicine $medicine)
    {
        return $medicine->load('category', 'batches');
    }

    public function update(Request $request, Medicine $medicine)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:180'],
            'category_id' => ['sometimes', 'required', 'string'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        if (isset($data['name'])) {
            $data['search_key'] = strtolower($data['name']);
        }

        $medicine->update($data);

        return $medicine->fresh('category');
    }

    public function destroy(Medicine $medicine)
    {
        abort_if($medicine->batches()->exists(), 422, 'Cannot delete a medicine that has batches.');
        $medicine->delete();

        return response()->noContent();
    }
}
