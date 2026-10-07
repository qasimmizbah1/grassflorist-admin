<!DOCTYPE html>
<html lang="{{ $order->order_language ?? 'ar' }}" dir="{{ ($order->order_language ?? 'ar') === 'en' ? 'ltr' : 'rtl' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gift Card - #{{ $order->order_number ?? $order->id }} - Grass Florist</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400&family=Playfair+Display:ital,wght@0,600;1,400&family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        @page {
            size: 148mm 105mm; /* A6 Landscape Card / 6"x4" standard gift card size */
            margin: 0;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Cairo', 'Amiri', 'Playfair Display', serif;
            background-color: #fdfbf7;
            color: #2c2523;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 15px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Screen Controls Toolbar (Hidden when printing) */
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
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: background 0.2s;
        }

        .btn:hover {
            background: #047857;
        }

        .btn-secondary {
            background: #4b5563;
        }

        .btn-secondary:hover {
            background: #374151;
        }

        /* Card Container (A6 Size: 148mm x 105mm) */
        .card {
            width: 148mm;
            height: 105mm;
            background: #fffdfa;
            border: 2px solid #e7d8c9;
            border-radius: 8px;
            padding: 12mm 14mm;
            position: relative;
            box-shadow: 0 10px 30px rgba(74, 52, 38, 0.08);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
        }

        /* Subtle Luxury Border Frame */
        .card::before {
            content: '';
            position: absolute;
            top: 4mm;
            left: 4mm;
            right: 4mm;
            bottom: 4mm;
            border: 1px solid #ebd9c8;
            pointer-events: none;
            border-radius: 4px;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #f1e5d8;
            padding-bottom: 6px;
        }

        .brand-title {
            font-family: 'Playfair Display', serif;
            font-size: 16px;
            color: #065f46;
            letter-spacing: 1px;
            font-weight: 600;
        }

        .brand-sub {
            font-size: 11px;
            color: #8c786a;
        }

        .recipient-label {
            font-size: 14px;
            font-weight: 700;
            color: #1f2937;
        }

        .card-body {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 8px 0;
        }

        .message-text {
            font-size: 16px;
            line-height: 1.6;
            color: #372b25;
            font-style: italic;
            max-width: 90%;
            word-wrap: break-word;
            white-space: pre-line;
        }

        .card-footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            border-top: 1px solid #f1e5d8;
            padding-top: 6px;
        }

        .sender-box {
            font-size: 13px;
            font-weight: 600;
            color: #065f46;
        }

        .song-qr-box {
            display: flex;
            align-items: center;
            gap: 8px;
            text-align: left;
        }

        .qr-img {
            width: 48px;
            height: 48px;
            border-radius: 4px;
            border: 1px solid #e5e7eb;
        }

        .song-caption {
            font-size: 10px;
            color: #6b7280;
            line-height: 1.2;
        }

        .order-meta {
            font-size: 9px;
            color: #9ca3af;
            position: absolute;
            bottom: 6px;
            left: 50%;
            transform: translateX(-50%);
        }

        @media print {
            body {
                background: none;
                padding: 0;
            }
            .toolbar {
                display: none !important;
            }
            .card {
                box-shadow: none;
                border: 1px solid #d4c5b5;
                margin: 0;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

    <!-- Screen Action Bar -->
    <div class="toolbar">
        <button class="btn" onclick="window.print()">
            Print Gift Card
        </button>
        <button class="btn btn-secondary" onclick="window.close()">
            Close
        </button>
    </div>

    <!-- Printable Gift Card -->
    <div class="card">
        
        <!-- Header -->
        <div class="card-header">
            <div>
                <div class="brand-title">GRASS FLORIST</div>
                <div class="brand-sub">جراس فلوريست للزهور والهدايا</div>
            </div>
            
            <div class="recipient-label">
                @if(!empty($order->recipient_name))
                    <span>إلى / To: <strong>{{ $order->recipient_name }}</strong></span>
                @elseif(!empty($order->first_name))
                    <span>إلى / To: <strong>{{ $order->first_name }} {{ $order->last_name }}</strong></span>
                @else
                    <span>إلى / To: <strong>Special Someone</strong></span>
                @endif
            </div>
        </div>

        <!-- Body Message -->
        <div class="card-body">
            <div class="message-text">
                @if(!empty($order->delivery_message))
                    "{{ $order->delivery_message }}"
                @else
                    "بكل حب وأطيب الأماني"
                @endif
            </div>
        </div>

        <!-- Footer -->
        <div class="card-footer">
            
            <!-- Sender -->
            <div class="sender-box">
                @if(!empty($order->sender_name))
                    <span>من / From: <strong>{{ $order->sender_name }}</strong></span>
                @else
                    <span>من / From: <strong>Someone who cares</strong></span>
                @endif
            </div>

            <!-- Spotify / Song QR Code if provided -->
            @if(!empty($order->song_link))
                <div class="song-qr-box">
                    <img class="qr-img" src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode($order->song_link) }}" alt="Song QR Code">
                    <div class="song-caption">
                        <strong>Song Attached</strong><br>
                        <span>امسح للاستماع</span>
                    </div>
                </div>
            @endif

        </div>

        <!-- Tracking Reference -->
        <div class="order-meta">
            #{{ $order->order_number ?? $order->id }} • {{ $order->delivery_date ?? date('Y-m-d') }}
        </div>

    </div>

</body>
</html>
