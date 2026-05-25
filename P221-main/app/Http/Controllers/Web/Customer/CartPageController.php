<?php

namespace App\Http\Controllers\Web\Customer;

use App\Http\Controllers\Controller;
use App\Services\CustomerOrderService;
use Inertia\Inertia;

class CartPageController extends Controller
{
    public function __invoke(CustomerOrderService $orders)
    {
        $cart = $orders->getOrCreateCart(auth()->id());

        return Inertia::render('Customer/Cart', [
            'cart' => $orders->cartSummary($cart),
            'coupons' => ['WELCOME15', 'PAIN20', 'HEALTH10'],
        ]);
    }
}
