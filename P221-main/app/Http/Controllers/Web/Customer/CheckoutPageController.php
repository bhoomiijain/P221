<?php

namespace App\Http\Controllers\Web\Customer;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use App\Models\Prescription;
use App\Services\AiConsultationService;
use App\Services\CustomerOrderService;
use Inertia\Inertia;

class CheckoutPageController extends Controller
{
    public function __invoke(CustomerOrderService $orders, AiConsultationService $ai)
    {
        $cart = $orders->getOrCreateCart(auth()->id());

        return Inertia::render('Customer/Checkout', [
            'cart' => $orders->cartSummary($cart),
            'addresses' => CustomerAddress::where('user_id', auth()->id())->get(),
            'prescriptions' => Prescription::where('user_id', auth()->id())->where('status', '!=', 'rejected')->get(),
            'aiQuestions' => $ai->defaultQuestions(),
            'paymentMethods' => [
                ['id' => 'cod', 'label' => 'Cash on Delivery', 'icon' => 'banknote'],
                ['id' => 'upi', 'label' => 'UPI', 'icon' => 'smartphone'],
                ['id' => 'card', 'label' => 'Credit/Debit Card', 'icon' => 'credit-card'],
                ['id' => 'wallet', 'label' => 'Wallet', 'icon' => 'wallet'],
            ],
        ]);
    }
}
