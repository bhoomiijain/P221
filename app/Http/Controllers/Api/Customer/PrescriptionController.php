<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\CustomerNotification;
use App\Models\Prescription;
use App\Services\CustomerOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PrescriptionController extends Controller
{
    public function index()
    {
        $items = Prescription::where('user_id', auth()->id())->latest()->get();

        return response()->json(['prescriptions' => $items]);
    }

    public function store(Request $request, CustomerOrderService $orders)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $file = $request->file('file');
        $path = $file->store('prescriptions/'.auth()->id(), 'public');

        $rx = Prescription::create([
            'user_id' => auth()->id(),
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'status' => 'pending',
            'ocr_data' => ['ready' => true, 'parsed' => false],
        ]);

        $orders->notify(auth()->id(), 'prescription_uploaded', 'Prescription Uploaded', 'Your prescription is pending pharmacist verification.');

        return response()->json(['prescription' => $rx], 201);
    }

    public function show(string $id)
    {
        $rx = Prescription::where('user_id', auth()->id())->findOrFail($id);

        return response()->json([
            'prescription' => $rx,
            'url' => Storage::disk('public')->url($rx->file_path),
        ]);
    }
}
