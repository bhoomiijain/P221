<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bill #{{ strtoupper(substr($sale->getKey(), -8)) }}</title>
    <style>
        @page { margin: 0; size: A4; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
            color: #1a1a2e;
            background: #fff;
        }

        /* ══ HEADER ══════════════════════════════════════════════════════ */
        .header {
            background: linear-gradient(135deg, #0f3460 0%, #16213e 50%, #0f3460 100%);
            padding: 0;
            position: relative;
            overflow: hidden;
        }
        .header-accent {
            position: absolute;
            top: -30px; right: -30px;
            width: 160px; height: 160px;
            border-radius: 50%;
            background: rgba(0,212,255,0.08);
        }
        .header-accent2 {
            position: absolute;
            bottom: -40px; left: 60px;
            width: 120px; height: 120px;
            border-radius: 50%;
            background: rgba(0,212,255,0.05);
        }
        .header-inner {
            padding: 30px 40px 24px;
            position: relative;
            z-index: 1;
        }
        .header-top-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .brand-icon {
            width: 52px; height: 52px;
            border-radius: 14px;
            background: rgba(255,255,255,0.15);
            border: 1.5px solid rgba(255,255,255,0.25);
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; font-weight: 900; color: #00d4ff;
            letter-spacing: -1px;
        }
        .brand-name { font-size: 20px; font-weight: 700; color: #fff; letter-spacing: -0.3px; }
        .brand-sub  { font-size: 10px; color: rgba(255,255,255,0.55); margin-top: 3px; }

        .invoice-badge {
            text-align: right;
        }
        .invoice-title {
            font-size: 30px; font-weight: 900;
            color: #fff;
            letter-spacing: 3px;
            line-height: 1;
        }
        .invoice-num {
            font-size: 12px;
            color: #00d4ff;
            margin-top: 5px;
            font-weight: 600;
            letter-spacing: 1px;
        }

        .header-divider {
            border: none;
            border-top: 1px solid rgba(255,255,255,0.1);
            margin: 18px 0 14px;
        }

        .header-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .meta-pill {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 20px;
            padding: 5px 14px;
            display: inline-flex;
            flex-direction: column;
            align-items: center;
        }
        .meta-pill-label { font-size: 8px; color: rgba(255,255,255,0.5); text-transform: uppercase; letter-spacing: 0.08em; }
        .meta-pill-value { font-size: 11px; font-weight: 700; color: #fff; margin-top: 1px; }

        .paid-badge {
            background: #00d4ff;
            color: #0f3460;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: 2px;
            padding: 5px 16px;
            border-radius: 20px;
            text-transform: uppercase;
        }

        /* ══ BODY ════════════════════════════════════════════════════════ */
        .body { padding: 28px 40px; }

        /* Bill To / From */
        .parties {
            display: flex;
            gap: 16px;
            margin-bottom: 24px;
        }
        .party-box {
            flex: 1;
            border-radius: 12px;
            padding: 16px 18px;
            border: 1.5px solid #e8edf5;
            background: #f8faff;
        }
        .party-label {
            font-size: 8px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #0f3460;
            margin-bottom: 8px;
            padding-bottom: 6px;
            border-bottom: 2px solid #0f3460;
            display: inline-block;
        }
        .party-name { font-size: 14px; font-weight: 700; color: #1a1a2e; margin-bottom: 3px; }
        .party-info { font-size: 10px; color: #64748b; line-height: 1.6; }

        /* Payment method pill */
        .payment-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
        }
        .pm-label { font-size: 10px; color: #64748b; }
        .pm-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
        }
        .pm-cash    { background: #dcfce7; color: #166534; }
        .pm-upi     { background: #ede9fe; color: #5b21b6; }
        .pm-card    { background: #dbeafe; color: #1e40af; }
        .pm-insurance{ background: #fef3c7; color: #92400e; }

        /* Items table */
        .items-section { margin-bottom: 0; }
        .section-title {
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #0f3460;
            margin-bottom: 10px;
        }

        table.items { width: 100%; border-collapse: collapse; }
        table.items thead tr {
            background: #0f3460;
            color: #fff;
        }
        table.items thead th {
            padding: 10px 12px;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            text-align: left;
        }
        table.items thead th.right { text-align: right; }
        table.items tbody tr { border-bottom: 1px solid #f0f4f8; }
        table.items tbody tr:nth-child(even) { background: #f8faff; }
        table.items tbody td {
            padding: 10px 12px;
            font-size: 11px;
            color: #334155;
            vertical-align: middle;
        }
        table.items tbody td.right { text-align: right; }
        .med-name { font-weight: 700; color: #1a1a2e; margin-bottom: 2px; }
        .batch-tag {
            display: inline-block;
            background: #e0f2fe;
            color: #0369a1;
            font-size: 9px;
            font-weight: 600;
            padding: 1px 6px;
            border-radius: 4px;
        }
        .exp-tag {
            display: inline-block;
            background: #fef3c7;
            color: #92400e;
            font-size: 9px;
            font-weight: 600;
            padding: 1px 6px;
            border-radius: 4px;
            margin-left: 3px;
        }

        /* Totals section */
        .totals-area {
            display: flex;
            justify-content: flex-end;
            margin-top: 16px;
        }
        .totals-box {
            width: 280px;
            border: 1.5px solid #e8edf5;
            border-radius: 12px;
            overflow: hidden;
        }
        .totals-inner { padding: 4px 0; }
        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 7px 16px;
            font-size: 11px;
        }
        .totals-row .t-label { color: #64748b; }
        .totals-row .t-val   { font-weight: 600; color: #1a1a2e; }
        .totals-row.discount .t-val { color: #ef4444; }
        .totals-divider { border: none; border-top: 1px solid #e8edf5; margin: 2px 0; }
        .totals-grand {
            background: linear-gradient(135deg, #0f3460, #16213e);
            padding: 12px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .totals-grand .t-label { color: rgba(255,255,255,0.7); font-size: 11px; font-weight: 600; }
        .totals-grand .t-val   { color: #00d4ff; font-size: 16px; font-weight: 900; }

        /* Footer */
        .footer-strip {
            margin-top: 28px;
            background: #f8faff;
            border: 1.5px solid #e8edf5;
            border-radius: 12px;
            padding: 14px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .footer-left { font-size: 10px; color: #64748b; line-height: 1.7; }
        .footer-right { text-align: right; }
        .footer-brand { font-size: 13px; font-weight: 700; color: #0f3460; }
        .footer-powered { font-size: 9px; color: #94a3b8; margin-top: 2px; }

        .footer-note {
            margin-top: 10px;
            text-align: center;
            font-size: 9px;
            color: #94a3b8;
        }
    </style>
</head>
<body>

    <!-- ══ HEADER ══ -->
    <div class="header">
        <div class="header-accent"></div>
        <div class="header-accent2"></div>
        <div class="header-inner">
            <div class="header-top-row">
                <div class="brand">
                    <div class="brand-icon">Rx</div>
                    <div>
                        <div class="brand-name">
                            {{ $sale->user?->pharmacy_name ?? 'Retail Pharmacy' }}
                        </div>
                        <div class="brand-sub">
                            {{ $sale->user?->address ? $sale->user->address . ', ' : '' }}{{ $sale->user?->city ?? 'Licensed Medical Store' }}
                        </div>
                        @if($sale->user?->license_number)
                            <div class="brand-sub">License: {{ $sale->user->license_number }}</div>
                        @endif
                    </div>
                </div>
                <div class="invoice-badge">
                    <div class="invoice-title">BILL</div>
                    <div class="invoice-num"># {{ strtoupper(substr($sale->getKey(), -8)) }}</div>
                </div>
            </div>

            <hr class="header-divider">

            <div class="header-meta">
                <div style="display:flex; gap:8px;">
                    <div class="meta-pill">
                        <span class="meta-pill-label">Date</span>
                        <span class="meta-pill-value">{{ $sale->created_at?->format('d M Y') }}</span>
                    </div>
                    <div class="meta-pill">
                        <span class="meta-pill-label">Time</span>
                        <span class="meta-pill-value">{{ $sale->created_at?->format('h:i A') }}</span>
                    </div>
                    <div class="meta-pill">
                        <span class="meta-pill-label">Pharmacist</span>
                        <span class="meta-pill-value">{{ $sale->user?->name ?? 'Staff' }}</span>
                    </div>
                    <div class="meta-pill">
                        <span class="meta-pill-label">Items</span>
                        <span class="meta-pill-value">{{ $sale->items->count() }}</span>
                    </div>
                </div>
                <div class="paid-badge">✓ PAID</div>
            </div>
        </div>
    </div>

    <!-- ══ BODY ══ -->
    <div class="body">

        <!-- Bill To / From -->
        <div class="parties">
            <div class="party-box">
                <div class="party-label">Bill To (Patient)</div>
                <div class="party-name">
                    {{ $sale->customer_name ?: 'Walk-in Customer' }}
                </div>
                <div class="party-info">
                    @if($sale->customer_phone)
                        📞 {{ $sale->customer_phone }}<br>
                    @endif
                    @if(!$sale->customer_name)
                        No patient details recorded
                    @endif
                </div>
            </div>
            <div class="party-box">
                <div class="party-label">Dispensed By</div>
                <div class="party-name">
                    {{ $sale->user?->pharmacy_name ?? 'Retail Pharmacy' }}
                </div>
                <div class="party-info">
                    @if($sale->user?->address){{ $sale->user->address }}<br>@endif
                    @if($sale->user?->city){{ $sale->user->city }}{{ $sale->user?->state ? ', ' . $sale->user->state : '' }}<br>@endif
                    @if($sale->user?->phone)📞 {{ $sale->user->phone }}<br>@endif
                    @if($sale->user?->license_number)License No: {{ $sale->user->license_number }}@endif
                </div>
            </div>
            <div class="party-box">
                <div class="party-label">Payment Info</div>
                <div class="party-name" style="font-size:12px; text-transform:uppercase; letter-spacing:1px;">
                    {{ strtoupper($sale->payment_method ?? 'Cash') }}
                </div>
                <div class="party-info">
                    Mode of payment<br>
                    Status: <strong style="color:#16a34a;">Paid ✓</strong>
                </div>
            </div>
        </div>

        <!-- Items -->
        <div class="items-section">
            <div class="section-title">Medicines Dispensed</div>
            <table class="items">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Medicine</th>
                        <th>Batch / Expiry</th>
                        <th class="right">Qty</th>
                        <th class="right">Rate (₹)</th>
                        <th class="right">Amount (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sale->items as $i => $item)
                        <tr>
                            <td style="color:#94a3b8; font-weight:600;">{{ $i + 1 }}</td>
                            <td>
                                <div class="med-name">{{ $item->medicine?->name ?? '—' }}</div>
                                @if($item->medicine?->description)
                                    <div style="font-size:9px; color:#94a3b8;">{{ $item->medicine->description }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="batch-tag">{{ $item->batch?->batch_number ?? 'N/A' }}</span>
                                @if($item->batch?->expiry_date)
                                    <span class="exp-tag">Exp: {{ $item->batch->expiry_date->format('M Y') }}</span>
                                @endif
                            </td>
                            <td class="right" style="font-weight:700;">{{ $item->quantity }}</td>
                            <td class="right">₹{{ number_format((float) $item->selling_price, 2) }}</td>
                            <td class="right" style="font-weight:700; color:#0f3460;">₹{{ number_format((float) $item->line_total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals -->
        <div class="totals-area">
            <div class="totals-box">
                <div class="totals-inner">
                    <div class="totals-row">
                        <span class="t-label">Subtotal</span>
                        <span class="t-val">₹{{ number_format((float) $sale->subtotal, 2) }}</span>
                    </div>
                    @if((float)$sale->discount > 0)
                        <hr class="totals-divider">
                        <div class="totals-row discount">
                            <span class="t-label">Discount</span>
                            <span class="t-val">– ₹{{ number_format((float) $sale->discount, 2) }}</span>
                        </div>
                    @endif
                    @if((float)$sale->tax > 0)
                        <hr class="totals-divider">
                        <div class="totals-row">
                            <span class="t-label">GST / Tax</span>
                            <span class="t-val">₹{{ number_format((float) $sale->tax, 2) }}</span>
                        </div>
                    @endif
                </div>
                <div class="totals-grand">
                    <span class="t-label">GRAND TOTAL</span>
                    <span class="t-val">₹{{ number_format((float) $sale->total_amount, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Footer strip -->
        <div class="footer-strip">
            <div class="footer-left">
                <strong style="color:#1a1a2e;">Important:</strong> Prescription medicines cannot be returned.<br>
                This is a computer-generated bill. No signature required.<br>
                Keep this bill for your medical records.
            </div>
            <div class="footer-right">
                <div class="footer-brand">Retail Pharmacy</div>
                <div class="footer-powered">Pharmacy Management System</div>
                <div style="font-size:9px; color:#94a3b8; margin-top:4px;">
                    Thank you for your visit! 🙏
                </div>
            </div>
        </div>

        <div class="footer-note">
            Bill generated on {{ $sale->created_at?->format('d M Y \a\t h:i A') }} · Bill ID: {{ $sale->getKey() }}
        </div>

    </div>
</body>
</html>
