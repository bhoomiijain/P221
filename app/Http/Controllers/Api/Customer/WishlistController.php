<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\WishlistItem;
use App\Services\CustomerCatalogService;
use App\Services\CustomerOrderService;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(CustomerCatalogService $catalog)
    {
        $items = WishlistItem::where('user_id', auth()->id())->with('medicine')->get();

        return response()->json([
            'items' => $items->map(fn ($w) => $catalog->formatMedicine($w->medicine))->filter()->values(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['medicine_id' => 'required|string']);

        WishlistItem::updateOrCreate(
            ['user_id' => auth()->id(), 'medicine_id' => $data['medicine_id']],
            []
        );

        return response()->json(['message' => 'Added to wishlist']);
    }

    public function destroy(string $medicineId)
    {
        WishlistItem::where('user_id', auth()->id())->where('medicine_id', $medicineId)->delete();

        return response()->json(['message' => 'Removed from wishlist']);
    }

    public function moveToCart(string $medicineId, CustomerOrderService $orders)
    {
        $cart = $orders->getOrCreateCart(auth()->id());
        \App\Models\CustomerCartItem::updateOrCreate(
            ['cart_id' => $cart->getKey(), 'medicine_id' => $medicineId, 'saved_for_later' => false],
            ['quantity' => 1]
        );
        WishlistItem::where('user_id', auth()->id())->where('medicine_id', $medicineId)->delete();

        return response()->json($orders->cartSummary($cart));
    }
}
