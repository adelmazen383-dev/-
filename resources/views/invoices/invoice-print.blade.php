<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>فاتورة حجز #{{ $invoice->invoice_number }} | توثيق العقود</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Cairo', sans-serif;
            background: #f5f0e8;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px 12px;
            color: #2d2d2d;
        }

        /* ===== Admin Toolbar ===== */
        .admin-bar {
            width: 100%;
            max-width: 680px;
            background: rgba(40, 30, 15, 0.92);
            border: 1px solid rgba(197, 155, 99, 0.4);
            backdrop-filter: blur(12px);
            border-radius: 12px;
            padding: 10px 16px;
            margin-bottom: 16px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }
        .admin-bar .brand-group {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #ffffff;
        }
        .admin-bar .brand-logo-mini {
            height: 30px;
            object-fit: contain;
        }
        .admin-bar .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
        .btn-act {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 5px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            border: none;
            font-family: 'Cairo', sans-serif;
            transition: all 0.2s ease;
        }
        .btn-act.download {
            background: linear-gradient(135deg, #c59b63 0%, #a07d4e 100%);
            color: #ffffff;
        }
        .btn-act.download:hover { transform: translateY(-1px); }
        .btn-act.print { background: rgba(255,255,255,0.12); color: #ffffff; border: 1px solid rgba(255,255,255,0.2); }
        .btn-act.print:hover { background: rgba(255,255,255,0.2); }
        .btn-act.whatsapp { background: #25d366; color: #ffffff; }
        .btn-act.whatsapp:hover { transform: translateY(-1px); }
        .btn-act.back { background: transparent; color: #c59b63; border: 1px solid rgba(197, 155, 99, 0.4); }
        .btn-act.back:hover { color: #ffffff; border-color: #c59b63; }

        /* ===== Invoice Container ===== */
        .invoice-container {
            width: 100%;
            max-width: 680px;
        }

        .invoice-page {
            width: 100%;
            background: #ffffff;
            border-radius: 4px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        /* ===== Header Section ===== */
        .invoice-header {
            background: linear-gradient(180deg, #faf6f0 0%, #f5efe5 100%);
            padding: 30px 32px 20px;
            text-align: center;
            border-bottom: 2px solid #c59b63;
            position: relative;
        }
        .invoice-header::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 0;
            right: 0;
            height: 2px;
            background: #c59b63;
            opacity: 0.4;
        }

        .header-logo {
            width: 90px;
            height: 90px;
            object-fit: contain;
            margin: 0 auto 8px;
            display: block;
        }

        .header-brand-name {
            font-size: 22px;
            font-weight: 900;
            color: #8b6914;
            margin-bottom: 2px;
            letter-spacing: 0;
        }

        .header-brand-sub {
            font-size: 11px;
            font-weight: 700;
            color: #a68b5b;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .header-brand-loc {
            font-size: 11px;
            font-weight: 600;
            color: #b89e6e;
            letter-spacing: 0;
        }

        /* ===== Title Strip ===== */
        .invoice-title-strip {
            background: linear-gradient(90deg, #c59b63 0%, #d4af7a 50%, #c59b63 100%);
            padding: 8px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .invoice-title-strip h2 {
            font-size: 15px;
            font-weight: 800;
            color: #ffffff;
            text-shadow: 0 1px 2px rgba(0,0,0,0.15);
        }
        .invoice-title-strip .en-title {
            font-size: 11px;
            font-weight: 700;
            color: rgba(255,255,255,0.85);
            letter-spacing: 1px;
        }

        /* ===== Invoice Meta & Company Info ===== */
        .invoice-meta-section {
            padding: 16px 32px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            border-bottom: 1px solid #e8e0d4;
            background: #fefcf9;
        }

        .meta-block {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .meta-row {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
        }
        .meta-row .label {
            font-weight: 800;
            color: #6b5b3e;
            white-space: nowrap;
            min-width: 75px;
        }
        .meta-row .label-en {
            font-weight: 600;
            color: #9b8b6e;
            font-size: 10px;
        }
        .meta-row .value {
            font-weight: 700;
            color: #2d2d2d;
        }

        .company-info {
            text-align: left;
            direction: ltr;
        }
        .company-info .company-name {
            font-size: 13px;
            font-weight: 800;
            color: #8b6914;
            margin-bottom: 2px;
        }
        .company-info .company-name-ar {
            font-size: 12px;
            font-weight: 700;
            color: #6b5b3e;
            direction: rtl;
            text-align: right;
        }
        .company-info .company-detail {
            font-size: 11px;
            color: #7a6b50;
            display: flex;
            align-items: center;
            gap: 5px;
            margin-top: 3px;
        }
        .company-info .company-detail svg {
            width: 12px;
            height: 12px;
            fill: #c59b63;
            flex-shrink: 0;
        }

        /* ===== Sections ===== */
        .section-title-bar {
            background: #f7f3ec;
            padding: 7px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e8e0d4;
            border-top: 1px solid #e8e0d4;
        }
        .section-title-bar h3 {
            font-size: 12px;
            font-weight: 800;
            color: #6b5b3e;
        }
        .section-title-bar .en {
            font-size: 10px;
            font-weight: 600;
            color: #9b8b6e;
        }

        /* ===== Customer & Booking Info ===== */
        .info-grid-section {
            padding: 14px 32px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            border-bottom: 1px solid #e8e0d4;
        }

        .info-half {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .info-half:first-child {
            padding-left: 16px;
            border-left: 1px solid #e8e0d4;
        }
        .info-half:last-child {
            padding-right: 16px;
        }

        .info-item {
            display: flex;
            align-items: baseline;
            gap: 6px;
        }
        .info-item .lbl {
            font-size: 11px;
            font-weight: 800;
            color: #6b5b3e;
            white-space: nowrap;
        }
        .info-item .lbl-en {
            font-size: 9px;
            font-weight: 600;
            color: #b0a08a;
            display: block;
        }
        .info-item .val {
            font-size: 12px;
            font-weight: 700;
            color: #2d2d2d;
        }

        /* ===== Booking Details Table ===== */
        .booking-table {
            width: 100%;
            border-collapse: collapse;
        }
        .booking-table th {
            background: #f7f3ec;
            padding: 8px 12px;
            font-size: 11px;
            font-weight: 800;
            color: #6b5b3e;
            border-bottom: 1px solid #e8e0d4;
            text-align: center;
        }
        .booking-table th .en {
            display: block;
            font-size: 9px;
            font-weight: 600;
            color: #9b8b6e;
        }
        .booking-table td {
            padding: 10px 12px;
            font-size: 12px;
            font-weight: 700;
            color: #2d2d2d;
            text-align: center;
            border-bottom: 1px solid #e8e0d4;
        }

        /* ===== Financial Summary ===== */
        .financial-section {
            padding: 14px 32px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            border-bottom: 1px solid #e8e0d4;
        }

        .finance-block {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .finance-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 4px 0;
        }
        .finance-row .f-label {
            font-size: 11px;
            font-weight: 700;
            color: #6b5b3e;
        }
        .finance-row .f-label .en {
            font-size: 9px;
            color: #9b8b6e;
            display: block;
        }
        .finance-row .f-value {
            font-size: 12px;
            font-weight: 800;
            color: #2d2d2d;
        }
        .finance-row.total {
            border-top: 2px solid #c59b63;
            padding-top: 8px;
            margin-top: 4px;
        }
        .finance-row.total .f-label {
            font-size: 13px;
            font-weight: 900;
            color: #8b6914;
        }
        .finance-row.total .f-value {
            font-size: 16px;
            font-weight: 900;
            color: #8b6914;
        }

        /* Payment Status */
        .payment-status-box {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 8px;
        }
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 800;
        }
        .status-badge.paid {
            background: #ecfdf5;
            color: #059669;
            border: 1.5px solid #a7f3d0;
        }
        .status-badge.pending {
            background: #fffbeb;
            color: #d97706;
            border: 1.5px solid #fde68a;
        }
        .status-badge.failed {
            background: #fef2f2;
            color: #dc2626;
            border: 1.5px solid #fecaca;
        }
        .status-badge-label {
            font-size: 10px;
            font-weight: 600;
            color: #9b8b6e;
        }

        .status-badge-title {
            font-size: 11px;
            font-weight: 800;
            color: #6b5b3e;
            margin-bottom: 4px;
        }

        /* ===== Payment Methods ===== */
        .payment-methods-section {
            padding: 14px 32px;
            border-bottom: 1px solid #e8e0d4;
        }
        .pm-title {
            font-size: 11px;
            font-weight: 800;
            color: #6b5b3e;
            text-align: left;
            margin-bottom: 10px;
        }
        .pm-title .en {
            font-size: 9px;
            color: #9b8b6e;
            font-weight: 600;
            margin-right: 6px;
        }
        .payment-logos {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .pay-logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 34px;
            padding: 0 14px;
            border-radius: 6px;
            font-weight: 800;
            font-size: 13px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            border: 1px solid rgba(0,0,0,0.08);
            background: #ffffff;
        }
        .pay-logo.mada {
            color: #003087;
            font-weight: 900;
            gap: 4px;
        }
        .pay-logo.mada .dot { width: 5px; height: 5px; border-radius: 50%; background: #00843d; }
        .pay-logo.visa { color: #1a1f71; font-weight: 900; font-style: italic; font-size: 15px; }
        .pay-logo.mc { gap: 3px; }
        .pay-logo.mc .circle { width: 16px; height: 16px; border-radius: 50%; }
        .pay-logo.mc .c1 { background: #eb001b; }
        .pay-logo.mc .c2 { background: #f79e1b; margin-right: -6px; }
        .pay-logo.apple {
            color: #000;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-weight: 700;
            font-size: 13px;
            gap: 3px;
        }
        .pay-logo.apple svg { width: 14px; height: 14px; fill: #000; }
        .pay-logo.stc {
            color: #4f008c;
            font-weight: 900;
        }
        .pay-logo.stc span { color: #00c48c; margin-left: 1px; }
        .pay-logo.tabby {
            background: #3bffc0;
            color: #111827;
            font-weight: 900;
        }
        .pay-logo.tamara {
            background: linear-gradient(135deg, #ffd3b6 0%, #ffaaa5 100%);
            color: #2d1810;
            font-weight: 900;
        }

        /* ===== QR & Notes Section ===== */
        .footer-section {
            padding: 16px 32px;
            background: #faf8f4;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 20px;
            align-items: start;
        }

        .qr-block {
            text-align: center;
        }
        .qr-block #qrcode {
            width: 80px;
            height: 80px;
        }
        .qr-block #qrcode img,
        .qr-block #qrcode canvas {
            width: 80px !important;
            height: 80px !important;
        }
        .qr-block .qr-label {
            font-size: 9px;
            color: #9b8b6e;
            margin-top: 4px;
            font-weight: 600;
            max-width: 80px;
            line-height: 1.4;
        }

        .notes-block {
            text-align: right;
        }
        .notes-block .notes-title {
            font-size: 11px;
            font-weight: 800;
            color: #6b5b3e;
            margin-bottom: 6px;
        }
        .notes-block .notes-title .en {
            font-size: 9px;
            color: #9b8b6e;
            font-weight: 600;
            margin-right: 6px;
        }
        .notes-block ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .notes-block ul li {
            font-size: 10px;
            font-weight: 600;
            color: #7a6b50;
            line-height: 1.8;
            padding-right: 10px;
            position: relative;
        }
        .notes-block ul li::before {
            content: '•';
            position: absolute;
            right: 0;
            color: #c59b63;
        }

        /* ===== Thank You Strip ===== */
        .thank-you-strip {
            background: linear-gradient(90deg, #c59b63 0%, #d4af7a 50%, #c59b63 100%);
            padding: 10px 32px;
            text-align: center;
        }
        .thank-you-strip p {
            font-size: 13px;
            font-weight: 800;
            color: #ffffff;
            text-shadow: 0 1px 2px rgba(0,0,0,0.15);
        }
        .thank-you-strip .en {
            font-size: 10px;
            font-weight: 600;
            color: rgba(255,255,255,0.8);
            display: block;
            margin-top: 2px;
        }

        /* ===== Responsive ===== */
        @media (max-width: 640px) {
            .invoice-meta-section,
            .info-grid-section,
            .financial-section {
                grid-template-columns: 1fr;
            }
            .info-half:first-child {
                border-left: none;
                padding-left: 0;
                padding-bottom: 10px;
                border-bottom: 1px solid #e8e0d4;
            }
            .info-half:last-child {
                padding-right: 0;
                padding-top: 10px;
            }
            .footer-grid {
                grid-template-columns: 1fr;
            }
            .qr-block { order: 2; }
            .notes-block { order: 1; }
            .invoice-header { padding: 20px 16px 14px; }
            .header-logo { width: 70px; height: 70px; }
            .invoice-meta-section,
            .info-grid-section,
            .financial-section,
            .payment-methods-section,
            .footer-section {
                padding-left: 16px;
                padding-right: 16px;
            }
        }

        @media print {
            body { background: #ffffff; padding: 0; }
            .admin-bar { display: none !important; }
            .invoice-page {
                box-shadow: none;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>

    <!-- ===== Admin Toolbar ===== -->
    <div class="admin-bar" id="admin-toolbar">
        <div class="brand-group">
            <img src="{{ asset('images/image.png') }}" alt="توثيق العقود" class="brand-logo-mini">
            <div>
                <strong style="font-size:13px; color:#ffffff;">فاتورة حجز</strong>
                <span style="font-size:11px; color:#c59b63; margin-right:6px;">#{{ $invoice->invoice_number }}</span>
            </div>
            <span style="font-size:11px; padding:2px 10px; border-radius:10px; font-weight:700;
                @if($invoice->isPaid()) background:rgba(16,185,129,0.2); color:#34d399; border:1px solid rgba(16,185,129,0.4);
                @elseif($invoice->isPending()) background:rgba(245,158,11,0.2); color:#fbbf24; border:1px solid rgba(245,158,11,0.4);
                @else background:rgba(239,68,68,0.2); color:#f87171; border:1px solid rgba(239,68,68,0.4);
                @endif">
                {{ $invoice->statusLabel() }}
            </span>
        </div>

        <div class="actions">
            <button onclick="downloadInvoiceImage()" class="btn-act download" id="btn-download">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                تحميل PNG
            </button>

            <button onclick="window.print()" class="btn-act print">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                طباعة
            </button>

            @php
                $cleanPhone = preg_replace('/[^0-9]/', '', $invoice->phone);
                if (str_starts_with($cleanPhone, '05')) $cleanPhone = '966' . substr($cleanPhone, 1);
                elseif (str_starts_with($cleanPhone, '01')) $cleanPhone = '20' . substr($cleanPhone, 1);
                elseif (str_starts_with($cleanPhone, '5')) $cleanPhone = '966' . $cleanPhone;

                $cardLink = route('booking-card.show', $invoice->booking_card_token);
                $payLink  = $invoice->telr_transaction_url;

                $msg = "مرحباً أستاذ/ة " . $invoice->customer_name . "،\n"
                     . "يسعدنا تأكيد حجزكم عبر *منصة توثيق العقود* 📑\n\n"
                     . "🧾 *فاتورة الحجز:*\n" . url()->current() . "\n\n";

                if ($invoice->isPending() && $payLink) {
                    $msg .= "💳 *رابط الدفع الإلكتروني المباشر:*\n" . $payLink . "\n\n";
                }
                $msg .= "شكراً لاختياركم منصتنا.";
                $waUrl = "https://wa.me/" . $cleanPhone . "?text=" . urlencode($msg);

                // Calculate nights
                $nightsCount = 0;
                $ratePerNight = 0;
                if ($invoice->check_in_date && $invoice->check_out_date) {
                    $nightsCount = $invoice->check_in_date->diffInDays($invoice->check_out_date);
                    $ratePerNight = $nightsCount > 0 ? ($invoice->agreed_amount ?? $invoice->amount) / $nightsCount : 0;
                }

                // No VAT — total = agreed amount minus discount
                $totalBeforeVat = $invoice->agreed_amount ?? $invoice->amount;
                $discount = 0;
                $vatAmount = 0;
                $grandTotal = $totalBeforeVat - $discount;

                // Suite type label
                $suiteType = 'شقة مفروشة';
                if (stripos($invoice->suite_number ?: $invoice->apartment_number, 'جناح') !== false || stripos($invoice->suite_number ?: $invoice->apartment_number, 'suite') !== false) {
                    $suiteType = 'جناح صغير';
                }
            @endphp

            <a href="{{ $waUrl }}" target="_blank" class="btn-act whatsapp">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.625.846 5.059 2.284 7.034L.789 23.492a.5.5 0 00.613.613l4.458-1.495A11.952 11.952 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-2.352 0-4.55-.758-6.336-2.065l-.442-.332-3.262 1.093 1.093-3.262-.332-.442A9.96 9.96 0 012 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/></svg>
                واتساب
            </a>

            <a href="{{ route('invoices.show', $invoice) }}" class="btn-act back">
                ← الرجوع
            </a>
        </div>
    </div>

    <!-- ===== The Invoice ===== -->
    <div class="invoice-container">
        <div class="invoice-page" id="invoice-page">

            <!-- Header with Logo -->
            <div class="invoice-header">
                <img src="{{ asset('images/image.png') }}" alt="توثيق العقود" class="header-logo">
                <div class="header-brand-name">توثيق العقود</div>
                <div class="header-brand-sub">TAOTHEEQ AL-OQOOD</div>
                <div class="header-brand-loc">جدة</div>
            </div>

            <!-- Title Strip -->
            <div class="invoice-title-strip">
                <h2>فاتورة حجز</h2>
                <span class="en-title">INVOICE</span>
            </div>

            <!-- Invoice Meta & Company Info -->
            <div class="invoice-meta-section">
                <div class="meta-block">
                    <div class="meta-row">
                        <span class="label">رقم الفاتورة <span class="label-en">Invoice No.</span></span>
                        <span class="value">#{{ $invoice->invoice_number }}</span>
                    </div>
                    <div class="meta-row">
                        <span class="label">تاريخ الإصدار <span class="label-en">Issue Date</span></span>
                        <span class="value">{{ $invoice->created_at->format('Y/m/d') }}</span>
                    </div>
                </div>
                <div class="company-info">
                    <div class="company-name-ar" style="text-align:left; direction:ltr;" contenteditable="true">{{ $invoice->property_name ?: 'توثيق العقود | Taotheeq Al-Oqood' }}</div>
                    <div class="company-detail" style="margin-top:4px;">
                        <svg viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                        <span contenteditable="true">{{ $invoice->property_location ?: 'جدة - المملكة العربية السعودية' }}</span>
                    </div>
                    <div class="company-detail">
                        <svg viewBox="0 0 24 24"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>
                        <span contenteditable="true">{{ $invoice->property_phone ?: '+966 XX XXX XXXX' }}</span>
                    </div>
                </div>
            </div>

            <!-- Customer Info Title -->
            <div class="section-title-bar">
                <h3>بيانات العميل <span style="font-size:10px; color:#9b8b6e; margin-right:6px;">Customer Information</span></h3>
                <h3>بيانات الحجز <span style="font-size:10px; color:#9b8b6e; margin-right:6px;">Booking Details</span></h3>
            </div>

            <!-- Customer & Booking Info -->
            <div class="info-grid-section">
                <div class="info-half">
                    <div class="info-item">
                        <div>
                            <span class="lbl">اسم العميل</span>
                            <span class="lbl-en">Name</span>
                        </div>
                        <span class="val">{{ $invoice->customer_name }}</span>
                    </div>
                    <div class="info-item">
                        <div>
                            <span class="lbl">رقم الجوال</span>
                            <span class="lbl-en">Mobile No.</span>
                        </div>
                        <span class="val" style="direction:ltr;">{{ $invoice->phone }}</span>
                    </div>
                    <div class="info-item">
                        <div>
                            <span class="lbl">رقم الهوية</span>
                            <span class="lbl-en">ID No.</span>
                        </div>
                        <span class="val">{{ $invoice->national_id ?: '—' }}</span>
                    </div>
                </div>
                <div class="info-half">
                    <div class="info-item">
                        <div>
                            <span class="lbl">رقم الحجز</span>
                            <span class="lbl-en">Booking No.</span>
                        </div>
                        <span class="val">#{{ $invoice->invoice_number }}</span>
                    </div>
                    <div class="info-item">
                        <div>
                            <span class="lbl">الشقة</span>
                            <span class="lbl-en">Apartment</span>
                        </div>
                        <span class="val" style="color:#8b6914; font-weight:900;">{{ $invoice->suite_number ?: $invoice->apartment_number }}</span>
                    </div>
                    <div class="info-item">
                        <div>
                            <span class="lbl">نوع الشقة</span>
                            <span class="lbl-en">Suite Type</span>
                        </div>
                        <span class="val">{{ $suiteType }}</span>
                    </div>
                </div>
            </div>

            <!-- Booking Details Table -->
            <div style="padding: 0 32px;">
                <table class="booking-table">
                    <tr>
                        <th>
                            البند
                            <span class="en">Description</span>
                        </th>
                        <th>
                            المدة
                            <span class="en">Duration</span>
                        </th>
                        <th>
                            سعر الليلة
                            <span class="en">Rate per Night</span>
                        </th>
                        <th>
                            الإجمالي
                            <span class="en">Total</span>
                        </th>
                    </tr>
                    <tr>
                        <td style="text-align: right; font-size: 11px;">
                            إقامة في شقة {{ $invoice->suite_number ?: $invoice->apartment_number }}
                            ({{ $suiteType }})
                            <br>
                            <span style="font-size: 10px; color: #9b8b6e;">
                                Stay in Apartment {{ $invoice->suite_number ?: $invoice->apartment_number }} ({{ $suiteType == 'جناح صغير' ? 'Junior Suite' : 'Furnished Apt' }})
                            </span>
                        </td>
                        <td>
                            من {{ $invoice->check_in_date ? $invoice->check_in_date->format('Y/m/d') : '—' }}
                            <br>
                            إلى {{ $invoice->check_out_date ? $invoice->check_out_date->format('Y/m/d') : '—' }}
                            <br>
                            <span style="font-size: 11px; color: #8b6914; font-weight: 900;">
                                ({{ $nightsCount }} {{ $nightsCount == 1 ? 'ليلة' : 'ليالٍ' }})
                            </span>
                        </td>
                        <td>
                            <span style="font-weight: 900;">SAR {{ number_format($ratePerNight, 0) }}</span>
                        </td>
                        <td>
                            <span style="font-weight: 900;">SAR {{ number_format($totalBeforeVat, 0) }}</span>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Financial Summary -->
            <div class="financial-section">
                <div class="finance-block">
                    <div class="finance-row">
                        <div class="f-label">المبلغ الإجمالي <span class="en">Total Amount</span></div>
                        <div class="f-value">{{ number_format($totalBeforeVat, 0) }} SAR</div>
                    </div>
                    <div class="finance-row">
                        <div class="f-label">الخصم <span class="en">Discount</span></div>
                        <div class="f-value">{{ number_format($discount, 0) }} SAR</div>
                    </div>
                    <div class="finance-row">
                        <div class="f-label">المبلغ المدفوع <span class="en">Amount Paid</span></div>
                        <div class="f-value">{{ number_format($invoice->amount_paid ?? 0, 0) }} SAR</div>
                    </div>
                    <div class="finance-row">
                        <div class="f-label">المتبقي <span class="en">Remaining Amount</span></div>
                        <div class="f-value" style="color: #dc2626;">{{ number_format($invoice->remaining_amount ?? 0, 0) }} SAR</div>
                    </div>
                    <div class="finance-row total">
                        <div class="f-label">المبلغ الكلي <span class="en">Grand Total</span></div>
                        <div class="f-value">{{ number_format($grandTotal, 0) }} SAR</div>
                    </div>
                </div>

                <div class="payment-status-box">
                    <div class="status-badge-title">حالة الدفع <span style="font-size:9px; color:#9b8b6e; font-weight:600; margin-right:4px;">Payment Status</span></div>
                    @if($invoice->isPaid())
                        <div class="status-badge paid">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            تم الدفع بنجاح
                        </div>
                        <span class="status-badge-label">Payment Completed</span>
                    @elseif($invoice->isPending())
                        <div class="status-badge pending">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            بانتظار الدفع
                        </div>
                        <span class="status-badge-label">Payment Pending</span>
                    @else
                        <div class="status-badge failed">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            {{ $invoice->statusLabel() }}
                        </div>
                        <span class="status-badge-label">{{ $invoice->status }}</span>
                    @endif
                </div>
            </div>

            <!-- Payment Methods -->
            <div class="payment-methods-section">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                    <div></div>
                    <div class="pm-title">
                        وسائل الدفع
                        <span class="en">Payment Methods</span>
                    </div>
                </div>
                <div class="payment-logos">
                    <div class="pay-logo mada">
                        <span class="dot"></span>
                        mada
                    </div>
                    <div class="pay-logo visa">VISA</div>
                    <div class="pay-logo mc">
                        <span class="circle c2"></span>
                        <span class="circle c1"></span>
                    </div>
                    <div class="pay-logo apple">
                        <svg viewBox="0 0 384 512"><path d="M318.7 268.7c-.2-36.7 16.4-64.4 50-84.8-18.8-26.9-47.2-41.7-84.7-44.6-35.5-2.8-74.3 20.7-88.5 20.7-15 0-49.4-19.7-76.4-19.7C63.3 141.2 4 184.8 4 273.5c0 26.2 4.8 53.3 14.4 81.2 12.8 36.7 59 126.7 107.2 125.2 25.2-.6 43-17.9 75.8-17.9 31.8 0 48.3 17.9 76.4 17.9 48.6-.7 90.4-82.5 102.6-119.3-65.2-30.7-61.7-90-61.7-91.9zm-56.6-164.2c27.3-32.4 24.8-61.9 24-72.5-24.1 1.4-52 16.4-67.9 34.9-17.5 19.8-27.8 44.3-25.6 71.9 26.1 2 49.9-11.4 69.5-34.3z"/></svg>
                        Pay
                    </div>
                    <div class="pay-logo stc">stc<span>pay</span></div>
                    <div class="pay-logo tabby">tabby</div>
                    <div class="pay-logo tamara">تمارا</div>
                </div>
            </div>

            <!-- QR Code & Notes -->
            <div class="footer-section">
                <div class="footer-grid">
                    <div class="qr-block">
                        <div id="qrcode"></div>
                        <p class="qr-label">
                            امسح الرمز للدفع أو التحقق من حجزك
                            <br>
                            <span style="font-size:8px;">Scan the QR code to pay or verify your booking</span>
                        </p>
                    </div>
                    <div class="notes-block">
                        <div class="notes-title">
                            ملاحظات
                            <span class="en">Notes</span>
                        </div>
                        <ul>
                            <li>يرجى الحضور في الموعد المحدد قبل موعد تسجيل الدخول بساعة.</li>
                            <li>دفع التأمين القابل للاسترداد ({{ number_format($invoice->deposit_amount ?? 500, 0) }} ريال) عند تسليم الوحدة.</li>
                            <li>التأمين المدفوع نقداً يُسترد نقداً، والمدفوع إلكترونياً يُسترد خلال ٢٤ ساعة.</li>
                            <li>يتم إلغاء الحجز في حال عدم استكمال الدفع قبل 24 ساعة.</li>
                            @if($invoice->notes)
                                <li style="color: #8b6914; font-weight: 700;">{{ $invoice->notes }}</li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Thank You Strip -->
            <div class="thank-you-strip">
                <p>
                    شكراً لاختياركم سكننا
                    <span class="en">Thank you for choosing Taotheeq Al-Oqood</span>
                </p>
            </div>

        </div>
    </div>

    <!-- ===== Scripts ===== -->
    <script>
        // Generate QR Code
        document.addEventListener('DOMContentLoaded', function() {
            const qrUrl = '{{ $invoice->telr_transaction_url ?: route("booking-card.show", $invoice->booking_card_token) }}';
            if (typeof QRCode !== 'undefined') {
                new QRCode(document.getElementById('qrcode'), {
                    text: qrUrl,
                    width: 80,
                    height: 80,
                    colorDark: '#2d2d2d',
                    colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel.M
                });
            }
        });

        // Download as PNG
        function downloadInvoiceImage() {
            const btn = document.getElementById('btn-download');
            const originalText = btn.innerHTML;
            btn.innerHTML = '⏳ جارٍ التوليد...';
            btn.disabled = true;

            const page = document.getElementById('invoice-page');

            html2canvas(page, {
                scale: 2.5,
                useCORS: true,
                backgroundColor: '#ffffff',
                logging: false,
            }).then(canvas => {
                const link = document.createElement('a');
                link.download = 'فاتورة_{{ $invoice->customer_name }}_{{ $invoice->invoice_number }}.png';
                link.href = canvas.toDataURL('image/png');
                link.click();

                btn.innerHTML = originalText;
                btn.disabled = false;
            }).catch(err => {
                console.error(err);
                alert('حدث خطأ أثناء تحميل الصورة. يمكنك استخدام زر الطباعة.');
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        }
    </script>
</body>
</html>
