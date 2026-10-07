<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Invoice - {{ $order->order_number ?? $order->id }}</title>

<style>
body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 12px;
    color: #333;
    line-height: 1.4;
    margin: 0;
    padding: 15px;
}

.header {
    text-align: center;
    border-bottom: 2px solid #059669;
    padding-bottom: 10px;
    margin-bottom: 15px;
}

.brand-title {
    font-size: 20px;
    font-weight: bold;
    color: #065f46;
}

.brand-sub {
    font-size: 11px;
    color: #666;
}

.details-section {
    width: 100%;
    margin-bottom: 15px;
}

.details-section td {
    vertical-align: top;
    padding: 0;
    border: none;
}

.deliver-to {
    width: 50%;
    padding-right: 15px;
}

.shipping-from {
    width: 50%;
    padding-left: 15px;
}

h4 {
    margin: 0 0 8px 0;
    font-size: 13px;
    font-weight: bold;
    color: #065f46;
    border-bottom: 1px solid #eee;
    padding-bottom: 4px;
}

table {
    width: 100%;
    border-collapse: collapse;
}

.items-table {
    margin: 15px 0;
}

.items-table th {
    background-color: #f3f4f6;
    border: 1px solid #ddd;
    padding: 8px;
    font-size: 11px;
    text-align: left;
}

.items-table td {
    border: 1px solid #ddd;
    padding: 8px;
    font-size: 11px;
}

.product-col {
    text-align: left;
}

.totals-table {
    width: 45%;
    margin-left: auto;
    margin-top: 10px;
}

.totals-table td {
    padding: 5px 8px;
    border: none;
    font-size: 12px;
}

.totals-table .label {
    text-align: left;
}

.totals-table .value {
    text-align: right;
}

.bold {
    font-weight: bold;
}

.grand-total {
    border-top: 2px solid #059669;
    background-color: #ecfdf5;
    font-weight: bold;
    font-size: 13px;
    color: #065f46;
}

.footer {
    margin-top: 25px;
    border-top: 1px solid #ddd;
    padding-top: 10px;
    text-align: center;
    font-size: 10px;
    color: #777;
}
</style>
</head>
<body>

@php
    $settings = \App\Models\GlobalSetting::current();
    $trn = $settings->vat_registration_number ?? '300000000000003';
    $vatRate = (float)($settings->vat_percentage ?? 15.00);
@endphp

<div class="header">
    <div class="brand-title">GRASS FLORIST</div>
    <div class="brand-sub">Online Flower & Gifts Delivery • Kingdom of Saudi Arabia</div>
    <div class="brand-sub" style="margin-top: 3px;">VAT TRN: <strong>{{ $trn }}</strong></div>
</div>

<table class="details-section">
    <tr>
        <td class="deliver-to">
            <h4>Customer & Recipient Details</h4>
            <strong>Customer:</strong> {{ $order->first_name }} {{ $order->last_name }}<br>
            @if(!empty($order->recipient_name))
                <strong>Recipient:</strong> {{ $order->recipient_name }} ({{ $order->recipient_phone ?? '-' }})<br>
            @endif
            <strong>Address:</strong> {{ $order->address ?? 'Jeddah' }}, {{ $order->city ?? 'Saudi Arabia' }}<br>
            <strong>Phone:</strong> {{ $order->customer_phone ?? $order->recipient_phone ?? $order->email }}<br>
            <strong>Delivery Date:</strong> {{ $order->delivery_date ?? date('Y-m-d') }} ({{ $order->delivery_time ?? 'Standard' }})
        </td>
        <td class="shipping-from">
            <h4>Invoice & Payment Details</h4>
            <strong>Invoice / Order #:</strong> #{{ $order->order_number ?? $order->id }}<br>
            <strong>Date:</strong> {{ $order->created_at ? $order->created_at->format('d M Y, h:i A') : date('d M Y') }}<br>
            <strong>Payment Method:</strong> {{ strtoupper($order->payment_method ?? 'Mada / Apple Pay') }}<br>
            <strong>Payment Status:</strong> {{ strtoupper($order->payment_status ?? 'Pending') }}
        </td>
    </tr>
</table>

@php
    $displayItems = $items ?? $order->items ?? collect();
@endphp

<table class="items-table">
    <thead>
        <tr>
            <th width="5%">#</th>
            <th width="50%">Item Description</th>
            <th width="15%" align="center">Quantity</th>
            <th width="15%" align="right">Unit Price</th>
            <th width="15%" align="right">Total (SAR)</th>
        </tr>
    </thead>
    <tbody>
        @if($displayItems->count() > 0)
            @foreach($displayItems as $index => $item)
            @php
                $pName = $item->product_name;
                if (empty($pName)) {
                    $rawName = $item->product?->name;
                    if (is_array($rawName)) {
                        $pName = $rawName['en'] ?? $rawName['ar'] ?? 'Flower Bouquet';
                    } elseif (is_string($rawName)) {
                        $pName = $rawName;
                    } else {
                        $pName = 'Flower Arrangement';
                    }
                }
            @endphp
            <tr>
                <td align="center">{{ $loop->iteration }}</td>
                <td class="product-col">
                    <strong>{{ $pName }}</strong>
                    @if(!empty($item->variation_details))
                        <br><small style="color: #666;">{{ $item->variation_details }}</small>
                    @endif
                </td>
                <td align="center" class="bold">{{ $item->quantity }}</td>
                <td align="right">{{ number_format($item->price, 2) }} SAR</td>
                <td align="right" class="bold">{{ number_format($item->quantity * $item->price, 2) }} SAR</td>
            </tr>
            @endforeach
        @else
            <tr>
                <td align="center">1</td>
                <td class="product-col"><strong>Fresh Flower & Gift Arrangement</strong></td>
                <td align="center" class="bold">1</td>
                <td align="right">{{ number_format((float)$order->subtotal, 2) }} SAR</td>
                <td align="right" class="bold">{{ number_format((float)$order->subtotal, 2) }} SAR</td>
            </tr>
        @endif
    </tbody>
</table>

<table class="totals-table">
    <tr>
        <td class="label">Subtotal:</td>
        <td class="value">{{ number_format((float)$order->subtotal, 2) }} SAR</td>
    </tr>
    <tr>
        <td class="label">Shipping / Delivery:</td>
        <td class="value">{{ number_format((float)($order->shipping_amount ?? 0), 2) }} SAR</td>
    </tr>
    <tr>
        <td class="label">VAT ({{ $vatRate }}%):</td>
        <td class="value">{{ number_format((float)($order->tax_amount ?? 0), 2) }} SAR</td>
    </tr>
    @if((float)($order->discount_amount ?? 0) > 0)
    <tr style="color: #c00;">
        <td class="label">Coupon Discount:</td>
        <td class="value">-{{ number_format((float)$order->discount_amount, 2) }} SAR</td>
    </tr>
    @endif
    <tr class="grand-total">
        <td class="label bold">Total Amount:</td>
        <td class="value bold">{{ number_format((float)$order->total_amount, 2) }} SAR</td>
    </tr>
</table>

<div class="footer">
    Thank you for shopping with <strong>Grass Florist</strong> • Saudi Arabia • Tax Invoice
</div>

</body>
</html>
