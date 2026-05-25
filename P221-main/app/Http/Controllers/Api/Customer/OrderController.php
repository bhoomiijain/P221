<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\CustomerOrder;
use App\Services\AiConsultationService;
use App\Services\CustomerOrderService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = CustomerOrder::where('user_id', auth()->id())
            ->with('items')
            ->latest()
            ->paginate(10);

        return response()->json($orders);
    }

    public function show(string $id)
    {
        $order = CustomerOrder::with('items')
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        return response()->json(['order' => $order]);
    }

    public function store(Request $request, CustomerOrderService $orders)
    {
        $data = $request->validate([
            'address' => 'required|array',
            'payment_method' => 'required|in:cod,upi,card,wallet',
            'ai_responses' => 'required|array',
            'prescription_ids' => 'array',
            'delivery_instructions' => 'nullable|string|max:500',
        ]);

        $order = $orders->placeOrder(
            auth()->id(),
            $data['address'],
            $data['payment_method'],
            $data['ai_responses'],
            $data['prescription_ids'] ?? [],
            $data['delivery_instructions'] ?? null,
        );

        return response()->json(['order' => $order, 'message' => 'Order placed successfully'], 201);
    }

    public function cancel(string $id, CustomerOrderService $orderService)
    {
        $order = CustomerOrder::where('user_id', auth()->id())->findOrFail($id);

        if (in_array($order->status, ['delivered', 'cancelled'], true)) {
            return response()->json(['message' => 'Cannot cancel this order'], 422);
        }

        $order->update(['status' => 'cancelled', 'cancelled_at' => now()]);
        $orderService->notify(auth()->id(), 'order_cancelled', 'Order Cancelled', "Order {$order->order_number} was cancelled.");

        return response()->json(['order' => $order->fresh()]);
    }

    public function invoice(string $id)
    {
        $order = CustomerOrder::with('items')
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        $pdf = Pdf::loadView('pdf.customer-invoice', ['order' => $order]);

        return $pdf->download("invoice-{$order->order_number}.pdf");
    }

    public function analyzeAi(Request $request, AiConsultationService $ai)
    {
        $request->validate(['responses' => 'required|array']);

        return response()->json($ai->analyze($request->responses));
    }

    public function aiQuestions(AiConsultationService $ai)
    {
        return response()->json(['questions' => $ai->defaultQuestions()]);
    }
}
