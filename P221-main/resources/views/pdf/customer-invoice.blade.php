<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $order->order_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1e293b; }
        h1 { color: #064e3b; font-size: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #e2e8f0; padding: 8px; text-align: left; }
        th { background: #f0fdfa; }
        .total { font-weight: bold; font-size: 14px; }
    </style>
</head>
<body>
    <h1>PharmaCare — Tax Invoice</h1>
    <p><strong>Order:</strong> {{ $order->order_number }}<br>
    <strong>Date:</strong> {{ $order->created_at?->format('d M Y H:i') }}</p>

    <p><strong>Deliver to:</strong><br>
    {{ $order->address_snapshot['name'] ?? '' }}<br>
    {{ $order->address_snapshot['address_line'] ?? '' }}<br>
    {{ $order->address_snapshot['city'] ?? '' }}, {{ $order->address_snapshot['pincode'] ?? '' }}
    </p>

    <table>
        <thead>
            <tr><th>Item</th><th>Qty</th><th>Price</th><th>Total</th></tr>
        </thead>
        <tbody>
            @foreach($order->items ?? [] as $item)
            <tr>
                <td>{{ $item->name }}</td>
                <td>{{ $item->quantity }}</td>
                <td>₹{{ number_format($item->unit_price, 2) }}</td>
                <td>₹{{ number_format($item->line_total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p class="total">Subtotal: ₹{{ number_format($order->subtotal, 2) }}</p>
    <p>Tax: ₹{{ number_format($order->tax, 2) }} | Delivery: ₹{{ number_format($order->delivery_charge, 2) }}</p>
    <p class="total">Grand Total: ₹{{ number_format($order->total, 2) }}</p>
    <p style="margin-top:24px;color:#64748b;font-size:10px;">QR-ready invoice — scan at pharmacy for verification.</p>
</body>
</html>
