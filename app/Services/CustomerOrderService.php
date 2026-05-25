<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\CustomerCart;
use App\Models\CustomerCartItem;
use App\Models\CustomerNotification;
use App\Models\CustomerOrder;
use App\Models\CustomerOrderItem;
use App\Models\CustomerPayment;
use App\Models\Medicine;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CustomerOrderService
{
    public function __construct(
        private CustomerCatalogService $catalog,
        private AiConsultationService $aiConsultation,
    ) {}

    public function getOrCreateCart(string $userId): CustomerCart
    {
        return CustomerCart::firstOrCreate(['user_id' => $userId]);
    }

    public function cartSummary(CustomerCart $cart): array
    {
        $items = CustomerCartItem::with('medicine')
            ->where('cart_id', $cart->getKey())
            ->where('saved_for_later', false)
            ->get();

        $lines = [];
        $subtotal = 0;
        $needsPrescription = false;

        foreach ($items as $item) {
            $medicine = $item->medicine;
            if (! $medicine) {
                continue;
            }
            $price = $medicine->sellingPrice();
            $lineTotal = round($price * $item->quantity, 2);
            $subtotal += $lineTotal;
            if ($medicine->prescription_required) {
                $needsPrescription = true;
            }
            $lines[] = [
                'id' => $item->getKey(),
                'medicine_id' => $medicine->getKey(),
                'name' => $medicine->name,
                'image' => $medicine->image,
                'quantity' => $item->quantity,
                'unit_price' => $price,
                'line_total' => $lineTotal,
                'prescription_required' => (bool) $medicine->prescription_required,
                'in_stock' => $this->catalog->availableStock($medicine->getKey()) >= $item->quantity,
            ];
        }

        $couponDiscount = (float) ($cart->coupon_discount ?? 0);
        $tax = round(max(0, $subtotal - $couponDiscount) * 0.05, 2);
        $delivery = $subtotal > 500 ? 0 : ($subtotal > 0 ? 49 : 0);

        return [
            'items' => $lines,
            'subtotal' => round($subtotal, 2),
            'coupon_code' => $cart->coupon_code,
            'coupon_discount' => $couponDiscount,
            'tax' => $tax,
            'delivery_charge' => $delivery,
            'total' => round(max(0, $subtotal - $couponDiscount) + $tax + $delivery, 2),
            'needs_prescription' => $needsPrescription,
            'estimated_delivery' => now()->addDays(3)->format('D, M j'),
        ];
    }

    public function applyCoupon(CustomerCart $cart, string $code): array
    {
        $coupon = Coupon::where('code', strtoupper($code))->where('active', true)->first();
        if (! $coupon) {
            throw ValidationException::withMessages(['coupon' => 'Invalid coupon code.']);
        }

        $summary = $this->cartSummary($cart);
        if ($summary['subtotal'] < (float) $coupon->min_order) {
            throw ValidationException::withMessages(['coupon' => 'Minimum order not met for this coupon.']);
        }

        $discount = $coupon->type === 'percent'
            ? round($summary['subtotal'] * ((float) $coupon->value / 100), 2)
            : (float) $coupon->value;

        $cart->update(['coupon_code' => $coupon->code, 'coupon_discount' => $discount]);

        return $this->cartSummary($cart->fresh());
    }

    public function placeOrder(
        string $userId,
        array $address,
        string $paymentMethod,
        array $aiResponses,
        array $prescriptionIds = [],
        ?string $deliveryInstructions = null,
    ): CustomerOrder {
        $cart = $this->getOrCreateCart($userId);
        $summary = $this->cartSummary($cart);

        if (empty($summary['items'])) {
            throw ValidationException::withMessages(['cart' => 'Your cart is empty.']);
        }

        foreach ($summary['items'] as $line) {
            if (! $line['in_stock']) {
                throw ValidationException::withMessages(['cart' => "{$line['name']} is out of stock."]);
            }
        }

        if ($summary['needs_prescription'] && empty($prescriptionIds)) {
            throw ValidationException::withMessages(['prescription' => 'Prescription required for one or more items.']);
        }

        $analysis = $this->aiConsultation->analyze($aiResponses);
        $consultation = $this->aiConsultation->logConsultation($userId, $aiResponses, $analysis);

        $order = CustomerOrder::create([
            'user_id' => $userId,
            'order_number' => 'PH'.strtoupper(Str::random(8)),
            'status' => $analysis['requires_pharmacist_review'] ? 'placed' : 'verified',
            'subtotal' => $summary['subtotal'],
            'tax' => $summary['tax'],
            'delivery_charge' => $summary['delivery_charge'],
            'discount' => $summary['coupon_discount'],
            'total' => $summary['total'],
            'payment_method' => $paymentMethod,
            'payment_status' => $paymentMethod === 'cod' ? 'pending' : 'paid',
            'address_snapshot' => $address,
            'delivery_instructions' => $deliveryInstructions,
            'prescription_ids' => $prescriptionIds,
            'requires_pharmacist_review' => $analysis['requires_pharmacist_review'],
            'ai_consultation_id' => $consultation->getKey(),
            'coupon_code' => $summary['coupon_code'],
            'estimated_delivery' => now()->addDays(3),
        ]);

        $consultation->update(['order_id' => $order->getKey()]);

        foreach ($summary['items'] as $line) {
            CustomerOrderItem::create([
                'order_id' => $order->getKey(),
                'medicine_id' => $line['medicine_id'],
                'name' => $line['name'],
                'quantity' => $line['quantity'],
                'unit_price' => $line['unit_price'],
                'line_total' => $line['line_total'],
                'prescription_required' => $line['prescription_required'],
            ]);
        }

        CustomerPayment::create([
            'order_id' => $order->getKey(),
            'user_id' => $userId,
            'method' => $paymentMethod,
            'amount' => $summary['total'],
            'status' => $paymentMethod === 'cod' ? 'pending' : 'completed',
            'transaction_ref' => 'TXN'.strtoupper(Str::random(10)),
        ]);

        CustomerCartItem::where('cart_id', $cart->getKey())->where('saved_for_later', false)->delete();
        $cart->update(['coupon_code' => null, 'coupon_discount' => 0]);

        $this->notify($userId, 'order_placed', 'Order Placed', "Order {$order->order_number} has been placed successfully.");

        if ($paymentMethod !== 'cod') {
            $this->notify($userId, 'payment_success', 'Payment Successful', "Payment of ₹{$summary['total']} received.");
        }

        return $order->load('items');
    }

    public function notify(string $userId, string $type, string $title, string $message, array $data = []): void
    {
        CustomerNotification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
        ]);
    }

    public function advanceStatus(CustomerOrder $order): CustomerOrder
    {
        $flow = ['placed', 'verified', 'packed', 'out_for_delivery', 'delivered'];
        $idx = array_search($order->status, $flow, true);
        if ($idx !== false && $idx < count($flow) - 1) {
            $order->update(['status' => $flow[$idx + 1]]);
            $this->notify(
                $order->user_id,
                'delivery_update',
                'Delivery Update',
                "Order {$order->order_number} is now: ".str_replace('_', ' ', $order->status),
                ['order_id' => $order->getKey()]
            );
        }

        return $order->fresh();
    }
}
