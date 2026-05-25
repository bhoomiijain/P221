<?php

namespace App\Http\Controllers\Web\Customer;

use App\Http\Controllers\Controller;
use App\Models\CustomerOrder;
use Inertia\Inertia;

class OrderPageController extends Controller
{
    public function index()
    {
        $orders = CustomerOrder::where('user_id', auth()->id())->with('items')->latest()->paginate(10);

        return Inertia::render('Customer/Orders/Index', ['orders' => $orders]);
    }

    public function show(string $id)
    {
        $order = CustomerOrder::with('items')
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        return Inertia::render('Customer/Orders/Show', ['order' => $order]);
    }

    public function track(string $id)
    {
        $order = CustomerOrder::where('user_id', auth()->id())->findOrFail($id);
        $steps = ['placed', 'verified', 'packed', 'out_for_delivery', 'delivered'];
        $currentIdx = array_search($order->status, $steps, true) ?? 0;

        return Inertia::render('Customer/Orders/Track', [
            'order' => $order,
            'steps' => collect($steps)->map(fn ($s, $i) => [
                'key' => $s,
                'label' => ucwords(str_replace('_', ' ', $s)),
                'done' => $i <= $currentIdx,
                'active' => $i === $currentIdx,
            ])->values(),
        ]);
    }
}
