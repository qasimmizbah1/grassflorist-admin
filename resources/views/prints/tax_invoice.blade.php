<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فاتورة ضريبية #{{ $order->order_number ?? $order->id }} - Grass Florist</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f3f4f6;
            color: #1f2937;
            padding: 20px;
            font-size: 13px;
            line-height: 1.5;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Screen Action Bar */
        .toolbar {
            position: fixed;
            top: 15px;
            left: 50%;
            transform: translateX(-50%);
            background: #ffffff;
            padding: 10px 20px;
            border-radius: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            display: flex;
            gap: 15px;
            align-items: center;
            z-index: 9999;
        }

        .btn {
            background: #059669;
            color: #ffffff;
            border: none;
            padding: 8px 18px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn:hover { background: #047857; }
        .btn-secondary { background: #4b5563; }
        .btn-secondary:hover { background: #374151; }

        .invoice-container {
            max-width: 800px;
            margin: 60px auto 20px auto;
            background: #ffffff;
            border-radius: 8px;
            padding: 30px 35px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        /* Header */
        .header-table {
            width: 100%;
            margin-bottom: 25px;
            border-bottom: 2px solid #059669;
            padding-bottom: 15px;
        }

        .header-table td {
            vertical-align: top;
        }

        .brand-name {
            font-size: 22px;
            font-weight: 800;
            color: #065f46;
        }

        .brand-sub {
            font-size: 13px;
            color: #4b5563;
        }

        .tax-badge {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            display: inline-block;
            margin-top: 5px;
            font-weight: 600;
        }

        .invoice-title-block {
            text-align: left;
        }

        .invoice-title {
            font-size: 20px;
            font-weight: 800;
            color: #111827;
        }

        /* Information Grid */
        .info-grid {
            width: 100%;
            margin-bottom: 25px;
            border-collapse: collapse;
        }

        .info-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 12px 15px;
            width: 48%;
            vertical-align: top;
        }

        .info-title {
            font-size: 13px;
            font-weight: 700;
            color: #065f46;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 5px;
            margin-bottom: 8px;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        .items-table th {
            background: #f3f4f6;
            color: #374151;
            font-weight: 700;
            text-align: right;
            padding: 10px;
            border: 1px solid #e5e7eb;
            font-size: 12px;
        }

        .items-table td {
            padding: 10px;
            border: 1px solid #e5e7eb;
            font-size: 12px;
        }

        /* Totals & ZATCA QR Code */
        .summary-table {
            width: 100%;
            margin-top: 15px;
        }

        .summary-table td {
            vertical-align: top;
        }

        .qr-section {
            text-align: center;
            width: 40%;
        }

        .qr-code-img {
            width: 120px;
            height: 120px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 4px;
        }

        .qr-caption {
            font-size: 10px;
            color: #6b7280;
            margin-top: 4px;
        }

        .calculation-table {
            width: 100%;
            border-collapse: collapse;
        }

        .calculation-table td {
            padding: 6px 10px;
            font-size: 13px;
        }

        .total-row {
            background: #ecfdf5;
            font-weight: 800;
            font-size: 15px;
            color: #065f46;
            border-top: 2px solid #059669;
        }

        .footer-note {
            margin-top: 30px;
            border-top: 1px solid #e5e7eb;
            padding-top: 12px;
            text-align: center;
            font-size: 11px;
            color: #6b7280;
        }

        @media print {
            body {
                background: none;
                padding: 0;
            }
            .toolbar { display: none !important; }
            .invoice-container {
                box-shadow: none;
                margin: 0;
                padding: 15px 20px;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>

    <!-- Action Bar -->
    <div class="toolbar">
        <button class="btn" onclick="window.print()">
            Print Tax Invoice
        </button>
        <button class="btn btn-secondary" onclick="window.close()">
            Close
        </button>
    </div>

    @php
        $settings = \App\Models\GlobalSetting::current();
        $trn = $settings->vat_registration_number ?? '300000000000003';
        $vatRate = (float)($settings->vat_percentage ?? 15.00);

        $subtotal = (float)$order->subtotal;
        $shipping = (float)($order->shipping_amount ?? 0);
        $tax = (float)($order->tax_amount ?? ($subtotal * ($vatRate / 100)));
        $total = (float)$order->total_amount;

        // Construct Saudi ZATCA QR Code Payload Data
        $qrData = "Seller: " . ($settings->site_name ?? 'Grass Florist') . "\n";
        $qrData .= "TRN: " . $trn . "\n";
        $qrData .= "Date: " . ($order->created_at ?? now())->format('Y-m-d H:i:s') . "\n";
        $qrData .= "Total: " . number_format($total, 2) . " SAR\n";
        $qrData .= "VAT: " . number_format($tax, 2) . " SAR";
    @endphp

    <div class="invoice-container">
        
        <!-- Header -->
        <table class="header-table">
            <tr>
                <td>
                    <div class="brand-name">جراس فلوريست للزهور والهدايا</div>
                    <div class="brand-sub">GRASS FLORIST ESTABLISHMENT</div>
                    <div class="tax-badge">
                        الرقم الضريبي (VAT TRN): <strong>{{ $trn }}</strong>
                    </div>
                </td>
                <td class="invoice-title-block">
                    <div class="invoice-title">فاتورة ضريبية مبسطة</div>
                    <div style="font-size: 12px; color: #6b7280;">Simplified Tax Invoice</div>
                    <div style="margin-top: 5px; font-weight: 700; color: #111827;">
                        رقم الطلب: #{{ $order->order_number ?? $order->id }}
                    </div>
                    <div style="font-size: 11px; color: #6b7280;">
                        تاريخ الإصدار: {{ $order->created_at ? $order->created_at->format('Y-m-d h:i A') : date('Y-m-d') }}
                    </div>
                </td>
            </tr>
        </table>

        <!-- Details Grid -->
        <table style="width: 100%; margin-bottom: 20px;">
            <tr>
                <!-- Customer Details -->
                <td class="info-box" style="width: 48%;">
                    <div class="info-title">بيانات العميل والمستلم (Customer & Recipient)</div>
                    <div><strong>العميل:</strong> {{ $order->first_name }} {{ $order->last_name }}</div>
                    <div><strong>الهاتف:</strong> {{ $order->customer_phone ?? $order->email }}</div>
                    <div><strong>المستلم:</strong> {{ $order->recipient_name ?? $order->first_name }} ({{ $order->recipient_phone }})</div>
                    <div><strong>العنوان:</strong> {{ $order->address ?? 'Jeddah' }}, {{ $order->city ?? 'Saudi Arabia' }}</div>
                </td>
                <td style="width: 4%;"></td>
                <!-- Delivery & Payment -->
                <td class="info-box" style="width: 48%;">
                    <div class="info-title">تفاصيل التوصيل والدفع (Delivery & Payment)</div>
                    <div><strong>تاريخ التوصيل:</strong> {{ $order->delivery_date ?? date('Y-m-d') }}</div>
                    <div><strong>الفترة المحددة:</strong> {{ $order->delivery_time ?? 'Standard' }}</div>
                    <div><strong>وسيلة الدفع:</strong> {{ strtoupper($order->payment_method ?? 'Mada / Apple Pay') }}</div>
                    <div>
                        <strong>حالة السداد:</strong> 
                        <span style="color: {{ $order->payment_status === 'paid' ? '#059669' : '#d97706' }}; font-weight: 700;">
                            {{ $order->payment_status === 'paid' ? 'تم السداد (PAID)' : 'معلق (PENDING)' }}
                        </span>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Line Items -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 50%;">المنتج / الوصف (Item Description)</th>
                    <th style="width: 15%; text-align: center;">الكمية (Qty)</th>
                    <th style="width: 15%;">سعر الوحدة (Unit Price)</th>
                    <th style="width: 15%;">المجموع (Total SAR)</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $orderItems = $order->items ?? collect();
                @endphp
                @if($orderItems->count() > 0)
                    @foreach($orderItems as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $item->product_name ?? 'باقة ورد طبيعي' }}</strong>
                                @if(!empty($item->variation_details))
                                    <div style="font-size: 11px; color: #6b7280;">{{ $item->variation_details }}</div>
                                @endif
                            </td>
                            <td style="text-align: center;">{{ $item->quantity }}</td>
                            <td>{{ number_format($item->price, 2) }} ر.س</td>
                            <td>{{ number_format($item->price * $item->quantity, 2) }} ر.س</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td>1</td>
                        <td><strong>باقة زهور وتنسيق خاص - Grass Florist Arrangement</strong></td>
                        <td style="text-align: center;">1</td>
                        <td>{{ number_format($subtotal, 2) }} ر.س</td>
                        <td>{{ number_format($subtotal, 2) }} ر.س</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <!-- Summary & ZATCA QR -->
        <table class="summary-table">
            <tr>
                <!-- ZATCA QR Code -->
                <td class="qr-section">
                    <img class="qr-code-img" src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data={{ urlencode($qrData) }}" alt="ZATCA E-Invoice QR Code">
                    <div class="qr-caption">
                        رمز الاستجابة السريع للفوترة الإلكترونية<br>
                        (ZATCA E-Invoicing Compliant QR)
                    </div>
                </td>

                <!-- Financial Calculation -->
                <td style="width: 60%; padding-right: 20px;">
                    <table class="calculation-table">
                        <tr>
                            <td>المجموع الفرعي (غير شامل الضريبة):</td>
                            <td style="text-align: left;"><strong>{{ number_format($subtotal, 2) }} ر.س</strong></td>
                        </tr>
                        <tr>
                            <td>رسوم التوصيل والشحن:</td>
                            <td style="text-align: left;"><strong>{{ number_format($shipping, 2) }} ر.س</strong></td>
                        </tr>
                        <tr>
                            <td>ضريبة القيمة المضافة ({{ $vatRate }}% VAT):</td>
                            <td style="text-align: left;"><strong>{{ number_format($tax, 2) }} ر.س</strong></td>
                        </tr>
                        @if((float)($order->discount_amount ?? 0) > 0)
                            <tr style="color: #dc2626;">
                                <td>خصم الكوبون:</td>
                                <td style="text-align: left;"><strong>-{{ number_format($order->discount_amount, 2) }} ر.س</strong></td>
                            </tr>
                        @endif
                        <tr class="total-row">
                            <td>المجموع الإجمالي النهائي:</td>
                            <td style="text-align: left;">{{ number_format($total, 2) }} ر.س</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- Footer -->
        <div class="footer-note">
            شكراً لتعاملكم مع <strong>جراس فلوريست</strong> • المملكة العربية السعودية • الرقم الضريبي: {{ $trn }}<br>
            Grass Florist Saudi Arabia • Online Flower & Gifts Delivery
        </div>

    </div>

</body>
</html>
