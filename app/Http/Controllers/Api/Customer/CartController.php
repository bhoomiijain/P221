<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\CustomerCartItem;
use App\Models\Medicine;
use App\Services\CustomerOrderService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(CustomerOrderService $orders)
    {
        $cart = $orders->getOrCreateCart(auth()->id());

        return response()->json($orders->cartSummary($cart));
    }

    public function store(Request $request, CustomerOrderService $orders)
    {
        $data = $request->validate([
            'medicine_id' => 'required|string',
            'quantity' => 'integer|min:1|max:99',
        ]);

        $cart = $orders->getOrCreateCart(auth()->id());
        Medicine::findOrFail($data['medicine_id']);

        $item = CustomerCartItem::firstOrNew([
            'cart_id' => $cart->getKey(),
            'medicine_id' => $data['medicine_id'],
            'saved_for_later' => false,
        ]);
        $item->quantity = ($item->quantity ?? 0) + ($data['quantity'] ?? 1);
        $item->save();

        return response()->json($orders->cartSummary($cart->fresh()));
    }

    public function update(Request $request, string $id, CustomerOrderService $orders)
    {
        $data = $request->validate([
            'quantity' => 'sometimes|integer|min:1|max:99',
            'saved_for_later' => 'sometimes|boolean',
        ]);

        $item = CustomerCartItem::with('cart')->findOrFail($id);
        abort_unless($item->cart && $item->cart->user_id === auth()->id(), 403);

        if (isset($data['quantity'])) {
            $item->quantity = $data['quantity'];
        }
        if (isset($data['saved_for_later'])) {
            $item->saved_for_later = $data['saved_for_later'];
        }
        $item->save();

        return response()->json($orders->cartSummary($orders->getOrCreateCart(auth()->id())));
    }

    public function destroy(string $id, CustomerOrderService $orders)
    {
        $item = CustomerCartItem::with('cart')->findOrFail($id);
        abort_unless($item->cart && $item->cart->user_id === auth()->id(), 403);
        $item->delete();

        return response()->json($orders->cartSummary($orders->getOrCreateCart(auth()->id())));
    }

    public function applyCoupon(Request $request, CustomerOrderService $orders)
    {
        $request->validate(['code' => 'required|string']);
        $cart = $orders->getOrCreateCart(auth()->id());

        return response()->json($orders->applyCoupon($cart, $request->code));
    }
}
