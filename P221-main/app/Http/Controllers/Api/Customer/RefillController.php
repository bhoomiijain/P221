<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\RefillReminder;
use App\Services\CustomerOrderService;
use Illuminate\Http\Request;

class RefillController extends Controller
{
    public function index()
    {
        return response()->json([
            'reminders' => RefillReminder::where('user_id', auth()->id())->with('medicine')->get(),
        ]);
    }

    public function store(Request $request, CustomerOrderService $orders)
    {
        $data = $request->validate([
            'medicine_id' => 'required|string',
            'quantity_days' => 'required|integer|min:7|max:90',
            'subscription_enabled' => 'boolean',
        ]);

        $reminder = RefillReminder::updateOrCreate(
            ['user_id' => auth()->id(), 'medicine_id' => $data['medicine_id']],
            [
                'quantity_days' => $data['quantity_days'],
                'next_reminder_at' => now()->addDays($data['quantity_days']),
                'subscription_enabled' => $data['subscription_enabled'] ?? false,
                'active' => true,
            ]
        );

        $orders->notify(auth()->id(), 'refill_reminder', 'Refill Reminder Set', 'We will remind you before your medicine runs out.');

        return response()->json(['reminder' => $reminder], 201);
    }

    public function destroy(string $id)
    {
        RefillReminder::where('user_id', auth()->id())->findOrFail($id)->delete();

        return response()->json(['message' => 'Reminder removed']);
    }
}
