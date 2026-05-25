<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 6mm 4mm; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: DejaVu Sans, monospace; font-size: 10px; color: #000; }

        .center { text-align: center; }
        .bold   { font-weight: bold; }
        .large  { font-size: 14px; }
        .small  { font-size: 9px; }
        .gray   { color: #555; }
        .divider{ border: none; border-top: 1px dashed #999; margin: 6px 0; }

        .pharmacy  { font-size: 15px; font-weight: bold; }
        .sub-label { font-size: 9px; color: #444; margin-top: 1px; }

        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; font-size: 9px; border-bottom: 1px solid #ccc; padding-bottom: 3px; }
        th.right, td.right { text-align: right; }
        td { padding: 3px 0; font-size: 10px; }

        .total-line { border-top: 1px solid #000; margin-top: 4px; padding-top: 4px; }
        .grand { font-size: 13px; font-weight: bold; }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="center">
        <div class="pharmacy">Retail Pharmacy</div>
        <div class="sub-label">Licensed Medical Store</div>
        <div class="sub-label">Ph: +91 00000 00000</div>
    </div>
    <hr class="divider">

    <!-- Invoice meta -->
    <table>
        <tr>
            <td class="small gray">Receipt #</td>
            <td class="small right">{{ strtoupper(substr($sale->getKey(), -6)) }}</td>
        </tr>
        <tr>
            <td class="small gray">Date</td>
            <td class="small right">{{ $sale->created_at?->format('d-m-Y H:i') }}</td>
        </tr>
        <tr>
            <td class="small gray">Cashier</td>
            <td class="small right">{{ $sale->user?->name ?? 'Staff' }}</td>
        </tr>
        @if($sale->customer_name)
        <tr>
            <td class="small gray">Customer</td>
            <td class="small right">{{ $sale->customer_name }}</td>
        </tr>
        @endif
    </table>
    <hr class="divider">

    <!-- Items -->
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th class="right">Qty</th>
                <th class="right">Rate</th>
                <th class="right">Amt</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sale->items as $item)
                <tr>
                    <td>{{ Str::limit($item->medicine?->name ?? '—', 16) }}</td>
                    <td class="right">{{ $item->quantity }}</td>
                    <td class="right">{{ number_format((float)$item->selling_price, 2) }}</td>
                    <td class="right">{{ number_format((float)$item->line_total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <hr class="divider">

    <!-- Totals -->
    <table>
        <tr>
            <td class="small gray">Subtotal</td>
            <td class="small right">₹{{ number_format((float)$sale->subtotal, 2) }}</td>
        </tr>
        @if((float)$sale->discount > 0)
        <tr>
            <td class="small gray">Discount</td>
            <td class="small right">- ₹{{ number_format((float)$sale->discount, 2) }}</td>
        </tr>
        @endif
        @if((float)$sale->tax > 0)
        <tr>
            <td class="small gray">Tax</td>
            <td class="small right">₹{{ number_format((float)$sale->tax, 2) }}</td>
        </tr>
        @endif
        <tr class="total-line">
            <td class="grand">TOTAL</td>
            <td class="grand right">₹{{ number_format((float)$sale->total_amount, 2) }}</td>
        </tr>
        <tr>
            <td class="small gray">Payment</td>
            <td class="small right bold">{{ strtoupper($sale->payment_method ?? 'CASH') }}</td>
        </tr>
    </table>
    <hr class="divider">

    <!-- Footer -->
    <div class="center small gray">
        Thank you for your purchase!<br>
        Rx prescription medicines only.<br>
        {{ $sale->created_at?->format('d M Y') }}
    </div>
</body>
</html>
