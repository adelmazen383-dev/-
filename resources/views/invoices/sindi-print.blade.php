<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>فاتورة حجز | SINDI RESIDENCE - DAMAC AL JAWHARA TOWER</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Cinzel:wght@600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Cairo', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #111111;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 24px 12px;
            color: #2b2b2b;
        }

        /* ===== Toolbar for admin/user actions ===== */
        .admin-bar {
            width: 100%;
            max-width: 620px;
            background: rgba(30, 30, 30, 0.95);
            border: 1px solid rgba(197, 155, 99, 0.35);
            border-radius: 12px;
            padding: 10px 16px;
            margin-bottom: 16px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.5);
        }
        .admin-bar .brand-group {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #ffffff;
        }
        .admin-bar .brand-logo-mini {
            height: 32px;
            width: 32px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid #c59b63;
        }
        .admin-bar .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
        .btn-act {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 14px;
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

        /* ===== Main Invoice Page Canvas ===== */
        .invoice-page {
            width: 100%;
            max-width: 620px;
            background: #ffffff;
            background-image: radial-gradient(#fdfcfb 0%, #fbf9f6 100%);
            position: relative;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.35);
            padding: 24px 28px 18px;
            overflow: hidden;
        }

        /* ===== Header Section ===== */
        .sindi-header {
            text-align: center;
            padding-bottom: 14px;
            position: relative;
        }
        .sindi-logo-circle {
            width: 76px;
            height: 76px;
            margin: 0 auto 6px;
            display: block;
            object-fit: contain;
        }
        .sindi-title {
            font-family: 'Cinzel', 'Playfair Display', serif;
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 2.5px;
            color: #222222;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .sindi-subtitle-en {
            font-family: 'Playfair Display', serif;
            font-size: 9.5px;
            font-weight: 600;
            letter-spacing: 2px;
            color: #8c734b;
            text-transform: uppercase;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 4px;
        }
        .sindi-subtitle-en::before,
        .sindi-subtitle-en::after {
            content: '';
            width: 24px;
            height: 1px;
            background: #c59b63;
        }
        .sindi-subtitle-ar {
            font-size: 13px;
            font-weight: 800;
            color: #8c734b;
            margin-bottom: 1px;
        }
        .sindi-jeddah {
            font-size: 8px;
            font-weight: 700;
            letter-spacing: 4px;
            color: #9c8a74;
            text-transform: uppercase;
        }

        /* ===== Top Info Boxes (Invoice Meta & Location) ===== */
        .top-meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-top: 14px;
            margin-bottom: 16px;
            align-items: center;
        }

        /* Left Side: Invoice badge & info */
        .invoice-tag-box {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
        }
        .invoice-tag-icon {
            width: 26px;
            height: 26px;
            color: #8c734b;
            flex-shrink: 0;
        }
        .invoice-tag-text h3 {
            font-size: 13px;
            font-weight: 900;
            color: #2b2b2b;
            line-height: 1.1;
        }
        .invoice-tag-text span {
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 1px;
            color: #666666;
            text-transform: uppercase;
        }

        .meta-bordered-box {
            background: #faf8f5;
            border: 1px solid #e8e2d8;
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 11px;
        }
        .meta-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 3px 0;
        }
        .meta-row .label-group {
            display: flex;
            flex-direction: column;
            line-height: 1.15;
        }
        .meta-row .lbl-ar {
            font-size: 10.5px;
            font-weight: 800;
            color: #3a3a3a;
        }
        .meta-row .lbl-en {
            font-size: 8.5px;
            font-weight: 600;
            color: #8a8a8a;
        }
        .meta-row .val {
            font-weight: 800;
            color: #1a1a1a;
            font-size: 11.5px;
            direction: ltr;
        }

        /* Right Side: Building Contact Info */
        .building-contact {
            font-size: 11px;
            display: flex;
            flex-direction: column;
            gap: 5px;
            text-align: right;
        }
        .contact-item {
            display: flex;
            align-items: flex-start;
            gap: 7px;
        }
        .contact-icon {
            width: 14px;
            height: 14px;
            color: #8c734b;
            flex-shrink: 0;
            margin-top: 2px;
        }
        .contact-title {
            font-size: 11px;
            font-weight: 800;
            color: #222222;
            line-height: 1.25;
        }
        .contact-sub {
            font-size: 9.5px;
            color: #666666;
            font-weight: 600;
        }
        .contact-ltr {
            direction: ltr;
            text-align: right;
            font-size: 11px;
            font-weight: 700;
            color: #333333;
        }

        /* ===== Customer & Booking 2-Col Section ===== */
        .cards-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 16px;
            background: #faf8f5;
            border: 1px solid #e8e2d8;
            border-radius: 8px;
            padding: 12px 14px;
        }
        .card-col {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .card-col:first-child {
            padding-left: 12px;
            border-left: 1px solid #e8e2d8;
        }
        .card-col:last-child {
            padding-right: 12px;
        }

        .card-header-line {
            display: flex;
            align-items: center;
            gap: 6px;
            padding-bottom: 4px;
            border-bottom: 1px solid #ebe5da;
        }
        .card-header-icon {
            width: 16px;
            height: 16px;
            color: #8c734b;
        }
        .card-header-line h4 {
            font-size: 12px;
            font-weight: 900;
            color: #2b2b2b;
            line-height: 1.1;
        }
        .card-header-line span {
            font-size: 9px;
            font-weight: 700;
            color: #888888;
        }

        .field-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 11px;
            padding: 2px 0;
        }
        .field-labels {
            display: flex;
            flex-direction: column;
            line-height: 1.15;
        }
        .field-labels .f-ar {
            font-size: 10px;
            font-weight: 800;
            color: #444444;
        }
        .field-labels .f-en {
            font-size: 8px;
            font-weight: 600;
            color: #999999;
        }
        .field-val {
            font-weight: 800;
            color: #1a1a1a;
            font-size: 11px;
            text-align: left;
        }

        /* ===== Items Table ===== */
        .items-table-wrapper {
            margin-bottom: 16px;
            border-radius: 6px;
            overflow: hidden;
            border: 1px solid #373634;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
        }
        .items-table th {
            background: #373634;
            color: #ffffff;
            padding: 8px 10px;
            font-size: 11px;
            font-weight: 800;
            line-height: 1.2;
        }
        .items-table th .th-en {
            display: block;
            font-size: 8.5px;
            font-weight: 600;
            color: #c59b63;
        }
        .items-table td {
            background: #ffffff;
            padding: 12px 10px;
            font-size: 11.5px;
            color: #2b2b2b;
            border-bottom: 1px solid #ebe5da;
            vertical-align: middle;
            font-weight: 700;
        }
        .items-table td.desc-cell {
            text-align: right;
            padding-right: 14px;
        }
        .items-table td.desc-cell .ar-txt {
            font-weight: 800;
            color: #1a1a1a;
            font-size: 12px;
        }
        .items-table td.desc-cell .en-txt {
            font-size: 9.5px;
            color: #777777;
            font-weight: 600;
            display: block;
            margin-top: 2px;
        }
        .duration-nights {
            font-size: 10.5px;
            color: #8c734b;
            font-weight: 900;
            margin-top: 3px;
        }

        /* ===== Financial Summary & Payment Status ===== */
        .finance-status-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 16px;
        }

        /* Financial box (left) */
        .finance-box {
            background: #faf8f5;
            border: 1px solid #e8e2d8;
            border-radius: 6px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .finance-inner {
            padding: 10px 14px;
        }
        .fin-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 3px 0;
            font-size: 11px;
        }
        .fin-row .lbl-grp {
            display: flex;
            flex-direction: column;
            line-height: 1.15;
        }
        .fin-row .ar {
            font-size: 10px;
            font-weight: 800;
            color: #444;
        }
        .fin-row .en {
            font-size: 8px;
            font-weight: 600;
            color: #888;
        }
        .fin-row .amt {
            font-weight: 800;
            color: #222;
            font-size: 11px;
            direction: ltr;
        }

        .grand-total-bar {
            background: #373634;
            color: #ffffff;
            padding: 10px 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .grand-total-bar .ar-total {
            font-size: 11.5px;
            font-weight: 900;
            color: #ffffff;
            line-height: 1.1;
        }
        .grand-total-bar .en-total {
            font-size: 8.5px;
            color: #c59b63;
            font-weight: 700;
            display: block;
        }
        .grand-total-bar .val-total {
            font-size: 15px;
            font-weight: 900;
            color: #ffffff;
            direction: ltr;
        }

        /* Payment Status box (right) */
        .status-box {
            background: #faf8f5;
            border: 1px solid #e8e2d8;
            border-radius: 6px;
            padding: 12px 14px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .status-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 6px;
            border-bottom: 1px solid #ebe5da;
            margin-bottom: 6px;
        }
        .status-header-text h4 {
            font-size: 11.5px;
            font-weight: 900;
            color: #2b2b2b;
            line-height: 1.1;
        }
        .status-header-text span {
            font-size: 8.5px;
            font-weight: 600;
            color: #888888;
        }
        .status-icon-check {
            width: 18px;
            height: 18px;
            color: #8c734b;
        }

        .status-badge-pill {
            margin-top: 10px;
            padding: 8px 12px;
            border-radius: 8px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        .status-badge-pill.paid {
            border: 1.5px solid #10b981;
            background: #f0fdf4;
            color: #047857;
        }
        .status-badge-pill.pending {
            border: 1.5px solid #f59e0b;
            background: #fffbeb;
            color: #b45309;
        }
        .status-badge-pill svg {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
        }
        .status-badge-text .top-txt {
            font-size: 11px;
            font-weight: 900;
            line-height: 1.2;
        }
        .status-badge-text .sub-txt {
            font-size: 8.5px;
            font-weight: 700;
            opacity: 0.85;
            display: block;
        }

        /* ===== Payment Methods Section ===== */
        .payment-methods-strip {
            margin-bottom: 16px;
            border-top: 1px dashed #e8e2d8;
            border-bottom: 1px dashed #e8e2d8;
            padding: 10px 0;
        }
        .pm-header {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 8px;
            font-size: 11px;
            font-weight: 900;
            color: #333333;
        }
        .pm-header svg {
            width: 16px;
            height: 16px;
            color: #8c734b;
        }
        .pm-header span {
            font-size: 8.5px;
            font-weight: 600;
            color: #888888;
            margin-right: 4px;
        }

        .pm-logos-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 6px;
            flex-wrap: wrap;
        }
        .pm-pill {
            background: #ffffff;
            border: 1px solid #e0d8cc;
            border-radius: 6px;
            height: 28px;
            padding: 0 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 11.5px;
            font-weight: 800;
            box-shadow: 0 1px 2px rgba(0,0,0,0.04);
        }
        .pm-pill.mada {
            color: #003087;
            font-weight: 900;
            font-size: 11px;
            gap: 3px;
        }
        .pm-pill.mada .dot {
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: #00843d;
        }
        .pm-pill.visa {
            color: #1a1f71;
            font-style: italic;
            font-size: 13px;
            font-weight: 900;
        }
        .pm-pill.mc {
            gap: 2px;
        }
        .pm-pill.mc .c1 { width: 13px; height: 13px; border-radius: 50%; background: #eb001b; }
        .pm-pill.mc .c2 { width: 13px; height: 13px; border-radius: 50%; background: #f79e1b; margin-right: -5px; }
        .pm-pill.mc span { font-size: 9px; font-weight: 700; color: #222; margin-right: 3px; }
        .pm-pill.apple {
            color: #000;
            font-size: 12px;
            gap: 3px;
            font-family: -apple-system, BlinkMacSystemFont, sans-serif;
            font-weight: 700;
        }
        .pm-pill.apple svg { width: 12px; height: 12px; fill: #000; }
        .pm-pill.stc {
            color: #4f008c;
            font-size: 12px;
            font-weight: 900;
        }
        .pm-pill.stc span { color: #00c48c; margin-left: 1px; }
        .pm-pill.tabby {
            background: #3bffc0;
            color: #111827;
            font-weight: 900;
            border-color: #3bffc0;
            font-size: 11.5px;
        }
        .pm-pill.tamara {
            background: linear-gradient(135deg, #ffd3b6 0%, #ffaaa5 100%);
            color: #2d1810;
            font-weight: 900;
            border: none;
            font-size: 11.5px;
        }

        /* ===== Footer: QR & Notes ===== */
        .footer-grid {
            display: grid;
            grid-template-columns: 100px 1fr;
            gap: 16px;
            align-items: start;
            margin-bottom: 14px;
        }
        .qr-box {
            text-align: center;
        }
        .qr-box #qrcode {
            width: 76px;
            height: 76px;
            margin: 0 auto;
        }
        .qr-box #qrcode img,
        .qr-box #qrcode canvas {
            width: 76px !important;
            height: 76px !important;
        }
        .qr-subtext {
            font-size: 8px;
            font-weight: 700;
            color: #666666;
            margin-top: 4px;
            line-height: 1.35;
        }

        .notes-box {
            font-size: 9.5px;
            color: #555555;
            line-height: 1.65;
        }
        .notes-header {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 10.5px;
            font-weight: 900;
            color: #2b2b2b;
            margin-bottom: 4px;
        }
        .notes-header svg {
            width: 14px;
            height: 14px;
            color: #8c734b;
        }
        .notes-header span {
            font-size: 8px;
            color: #888888;
            font-weight: 600;
            margin-right: 4px;
        }
        .notes-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .notes-list li {
            position: relative;
            padding-right: 10px;
            font-weight: 600;
        }
        .notes-list li::before {
            content: '•';
            position: absolute;
            right: 0;
            color: #8c734b;
            font-weight: 900;
        }

        /* ===== Thank you banner ===== */
        .thank-you-bar {
            text-align: center;
            padding: 10px 0 16px;
            position: relative;
        }
        .thank-you-bar::before {
            content: '';
            display: block;
            width: 140px;
            height: 1px;
            background: linear-gradient(90deg, transparent, #c59b63, transparent);
            margin: 0 auto 8px;
        }
        .thank-you-ar {
            font-size: 13px;
            font-weight: 800;
            color: #8c734b;
        }
        .thank-you-en {
            font-family: 'Playfair Display', serif;
            font-size: 9.5px;
            font-style: italic;
            color: #9c8a74;
            display: block;
            margin-top: 2px;
        }

        /* ===== Bottom Versace Greek Meander Pattern ===== */
        .bottom-greek-border {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 12px;
            background-image: url('{{ asset("images/sindi_bottom_border.png") }}');
            background-repeat: repeat-x;
            background-size: auto 12px;
            border-top: 1px solid #c59b63;
        }

        /* ===== Print & Mobile Responsive ===== */
        @media print {
            body { background: #ffffff; padding: 0; }
            .admin-bar { display: none !important; }
            .invoice-page {
                box-shadow: none;
                max-width: 100%;
                padding: 16px 20px 14px;
            }
        }
        @media (max-width: 600px) {
            .top-meta-grid,
            .cards-grid,
            .finance-status-grid {
                grid-template-columns: 1fr;
            }
            .card-col:first-child {
                border-left: none;
                padding-left: 0;
                padding-bottom: 8px;
                border-bottom: 1px solid #e8e2d8;
            }
            .card-col:last-child {
                padding-right: 0;
                padding-top: 8px;
            }
            .footer-grid {
                grid-template-columns: 1fr;
            }
            .qr-box { order: 2; }
            .notes-box { order: 1; }
            .invoice-page { padding: 18px 16px 14px; }
        }
    </style>
</head>
<body>

    @php
        // Calculate nights and rates
        $nightsCount = 0;
        $ratePerNight = 0;
        if ($invoice->check_in_date && $invoice->check_out_date) {
            $nightsCount = $invoice->check_in_date->diffInDays($invoice->check_out_date);
            $ratePerNight = $nightsCount > 0 ? ($invoice->agreed_amount ?? $invoice->amount) / $nightsCount : 0;
        }
        if ($nightsCount == 0) {
            $nightsCount = 1;
            $ratePerNight = $invoice->agreed_amount ?? $invoice->amount;
        }

        // No VAT — total = agreed amount minus discount
        $totalBeforeVat = $invoice->agreed_amount ?? $invoice->amount;
        $discount = 0;
        $vatAmount = 0;
        $grandTotal = $totalBeforeVat - $discount;

        // Clean phone for whatsapp
        $cleanPhone = preg_replace('/[^0-9]/', '', $invoice->phone);
        if (str_starts_with($cleanPhone, '05')) $cleanPhone = '966' . substr($cleanPhone, 1);
        elseif (str_starts_with($cleanPhone, '01')) $cleanPhone = '20' . substr($cleanPhone, 1);
        elseif (str_starts_with($cleanPhone, '5')) $cleanPhone = '966' . $cleanPhone;

        $payLink = $invoice->telr_transaction_url;
        $bookingNo = '#SR-' . ($invoice->check_in_date ? $invoice->check_in_date->format('Ymd') : '20261002') . '-' . ($invoice->suite_number ?: $invoice->apartment_number);
        $sindiInvoiceNo = '#SR-' . ($invoice->check_in_date ? $invoice->check_in_date->format('my') : '1026') . '-' . str_pad($invoice->id, 4, '0', STR_PAD_LEFT);

        // Suite Type
        $suiteType = 'Junior Suite';
        $suiteTypeAr = 'جناح صغير';
        if (stripos($invoice->suite_number ?: $invoice->apartment_number, 'تنفيذي') !== false || stripos($invoice->suite_number ?: $invoice->apartment_number, 'executive') !== false) {
            $suiteType = 'Executive Suite';
            $suiteTypeAr = 'جناح تنفيذي';
        }

        // Floor
        $floorNum = preg_replace('/[^0-9]/', '', $invoice->suite_number ?: $invoice->apartment_number);
        $floor = strlen($floorNum) >= 3 ? substr($floorNum, 0, 1) : ($floorNum ?: '9');

        $msg = "مرحباً أستاذ/ة " . $invoice->customer_name . "،\n"
             . "يسعدنا تأكيد حجزكم في *Sindi Residence - برج داماك الجوهرة* 🏢\n\n"
             . "🧾 *فاتورة وتفاصيل الحجز:*\n" . url()->current() . "\n\n";
        if ($invoice->isPending() && $payLink) {
            $msg .= "💳 *رابط الدفع الإلكتروني المباشر:*\n" . $payLink . "\n\n";
        }
        $msg .= "شكراً لاختياركم سكننا.";
        $waUrl = "https://wa.me/" . $cleanPhone . "?text=" . urlencode($msg);
    @endphp

    <!-- ===== Admin Toolbar ===== -->
    <div class="admin-bar">
        <div class="brand-group">
            <img src="{{ asset('images/sindi_medusa_circle.png') }}" alt="Sindi Residence" class="brand-logo-mini">
            <div>
                <strong style="font-size:13px; color:#ffffff;">SINDI RESIDENCE</strong>
                <span style="font-size:11px; color:#c59b63; margin-right:6px;">{{ $sindiInvoiceNo }}</span>
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
            <button onclick="downloadInvoicePNG()" class="btn-act download" id="btn-download">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                تحميل PNG
            </button>

            <button onclick="window.print()" class="btn-act print">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                طباعة
            </button>

            <a href="{{ $waUrl }}" target="_blank" class="btn-act whatsapp">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.625.846 5.059 2.284 7.034L.789 23.492a.5.5 0 00.613.613l4.458-1.495A11.952 11.952 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-2.352 0-4.55-.758-6.336-2.065l-.442-.332-3.262 1.093 1.093-3.262-.332-.442A9.96 9.96 0 012 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/></svg>
                واتساب
            </a>

            <a href="{{ route('invoices.show', $invoice) }}" class="btn-act back">
                ← الرجوع
            </a>
        </div>
    </div>

    <!-- ===== The Sindi Residence Invoice Canvas ===== -->
    <div class="invoice-page" id="sindi-invoice-canvas">

        <!-- Top Header with Medusa Logo -->
        <div class="sindi-header">
            <img src="{{ asset('images/sindi_medusa_circle.png') }}" alt="Sindi Residence Medusa Logo" class="sindi-logo-circle">
            <h1 class="sindi-title">SINDI RESIDENCE</h1>
            <div class="sindi-subtitle-en">DAMAC AL JAWHARA TOWER</div>
            <div class="sindi-subtitle-ar">شقق داماك الجوهرة</div>
            <div class="sindi-jeddah">J E D D A H</div>
        </div>

        <!-- Meta strip: Invoice tag on left, building info on right -->
        <div class="top-meta-grid">
            <!-- Left: Invoice Tag & Numbers -->
            <div>
                <div class="invoice-tag-box">
                    <svg class="invoice-tag-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <div class="invoice-tag-text">
                        <h3>فاتورة حجز</h3>
                        <span>INVOICE</span>
                    </div>
                </div>
                <div class="meta-bordered-box">
                    <div class="meta-row">
                        <div class="label-group">
                            <span class="lbl-ar">رقم الفاتورة</span>
                            <span class="lbl-en">Invoice No.</span>
                        </div>
                        <span class="val">{{ $sindiInvoiceNo }}</span>
                    </div>
                    <div class="meta-row" style="border-top:1px dashed #e8e2d8; margin-top:3px; padding-top:4px;">
                        <div class="label-group">
                            <span class="lbl-ar">تاريخ الإصدار</span>
                            <span class="lbl-en">Issue Date</span>
                        </div>
                        <span class="val" style="display:flex; align-items:center; gap:4px;">
                            {{ $invoice->created_at->format('Y/m/d') }}
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#8c734b" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Right: Sindi Damac Location & Contact -->
            <div class="building-contact">
                <div class="contact-item">
                    <svg class="contact-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <div>
                        <div class="contact-title" contenteditable="true">{{ $invoice->property_name ?: 'برج داماك الجوهرة | Sindi Residence' }}</div>
                        <div class="contact-sub" contenteditable="true">{{ $invoice->property_location ?: 'جدة - المملكة العربية السعودية' }}</div>
                    </div>
                </div>
                <div class="contact-item">
                    <svg class="contact-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    <div class="contact-ltr" contenteditable="true">{{ $invoice->property_phone ?: '+966 59 235 8118' }}</div>
                </div>
            </div>
        </div>

        <!-- Customer & Booking 2-Cards Grid -->
        <div class="cards-grid">
            <!-- Left: Customer Information -->
            <div class="card-col">
                <div class="card-header-line">
                    <svg class="card-header-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <div>
                        <h4>بيانات العميل</h4>
                        <span>Customer Information</span>
                    </div>
                </div>
                <div class="field-row">
                    <div class="field-labels">
                        <span class="f-ar">اسم العميل</span>
                        <span class="f-en">Customer Name</span>
                    </div>
                    <span class="field-val">{{ $invoice->customer_name }}</span>
                </div>
                <div class="field-row">
                    <div class="field-labels">
                        <span class="f-ar">رقم الجوال</span>
                        <span class="f-en">Mobile No.</span>
                    </div>
                    <span class="field-val" style="direction:ltr;">{{ $invoice->phone }}</span>
                </div>
                <div class="field-row">
                    <div class="field-labels">
                        <span class="f-ar">رقم الهوية</span>
                        <span class="f-en">ID No.</span>
                    </div>
                    <span class="field-val" style="direction:ltr;">{{ $invoice->national_id ?: '—' }}</span>
                </div>
            </div>

            <!-- Right: Booking Details -->
            <div class="card-col">
                <div class="card-header-line">
                    <svg class="card-header-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <div>
                        <h4>بيانات الحجز</h4>
                        <span>Booking Details</span>
                    </div>
                </div>
                <div class="field-row">
                    <div class="field-labels">
                        <span class="f-ar">رقم الحجز</span>
                        <span class="f-en">Booking No.</span>
                    </div>
                    <span class="field-val" style="direction:ltr;">{{ $bookingNo }}</span>
                </div>
                <div class="field-row">
                    <div class="field-labels">
                        <span class="f-ar">الشقة</span>
                        <span class="f-en">Apartment</span>
                    </div>
                    <span class="field-val" style="font-weight:900;">{{ $invoice->suite_number ?: $invoice->apartment_number }}</span>
                </div>
                <div class="field-row">
                    <div class="field-labels">
                        <span class="f-ar">الطابق</span>
                        <span class="f-en">Floor</span>
                    </div>
                    <span class="field-val">{{ $floor }}</span>
                </div>
                <div class="field-row">
                    <div class="field-labels">
                        <span class="f-ar">نوع الشقة</span>
                        <span class="f-en">Suite Type</span>
                    </div>
                    <span class="field-val">{{ $suiteType }}</span>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="items-table-wrapper">
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 40%; text-align: right; padding-right: 14px;">
                            البند
                            <span class="th-en">Description</span>
                        </th>
                        <th style="width: 25%;">
                            المدة
                            <span class="th-en">Duration</span>
                        </th>
                        <th style="width: 17%;">
                            سعر الليلة
                            <span class="th-en">Rate per Night</span>
                        </th>
                        <th style="width: 18%;">
                            الإجمالي
                            <span class="th-en">Total</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="desc-cell">
                            <span class="ar-txt">إقامة في شقة {{ $invoice->suite_number ?: $invoice->apartment_number }} ({{ $suiteTypeAr }})</span>
                            <span class="en-txt">Stay in Apartment {{ $invoice->suite_number ?: $invoice->apartment_number }} ({{ $suiteType }})</span>
                        </td>
                        <td>
                            <div>من {{ $invoice->check_in_date ? $invoice->check_in_date->format('Y/m/d') : '—' }}</div>
                            <div>إلى {{ $invoice->check_out_date ? $invoice->check_out_date->format('Y/m/d') : '—' }}</div>
                            <div class="duration-nights">({{ $nightsCount }} {{ $nightsCount == 1 ? 'ليلة' : 'ليالٍ' }})<br><span style="font-size:8.5px; font-weight:600;">({{ $nightsCount }} {{ $nightsCount == 1 ? 'Night' : 'Nights' }})</span></div>
                        </td>
                        <td>
                            <div style="direction: ltr; font-weight: 800;">SAR {{ number_format($ratePerNight, 0) }}</div>
                        </td>
                        <td>
                            <div style="direction: ltr; font-weight: 900; color: #1a1a1a;">SAR {{ number_format($totalBeforeVat, 0) }}</div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Financial Summary & Payment Status Grid -->
        <div class="finance-status-grid">
            <!-- Financial breakdown (left) -->
            <div class="finance-box">
                <div class="finance-inner">
                    <div class="fin-row">
                        <div class="lbl-grp">
                            <span class="ar">المبلغ الإجمالي</span>
                            <span class="en">Total Amount</span>
                        </div>
                        <span class="amt">{{ number_format($totalBeforeVat, 0) }} SAR</span>
                    </div>
                    <div class="fin-row" style="border-top:1px dashed #ebe5da; margin-top:2px; padding-top:4px;">
                        <div class="lbl-grp">
                            <span class="ar">الخصم</span>
                            <span class="en">Discount</span>
                        </div>
                        <span class="amt">{{ number_format($discount, 0) }} SAR</span>
                    </div>
                </div>

                <div class="grand-total-bar">
                    <div>
                        <span class="ar-total">المبلغ الكلي</span>
                        <span class="en-total">Grand Total</span>
                    </div>
                    <span class="val-total">{{ number_format($grandTotal, 0) }} SAR</span>
                </div>
            </div>

            <!-- Payment Status box (right) -->
            <div class="status-box">
                <div>
                    <div class="status-header">
                        <div class="status-header-text">
                            <h4>حالة الدفع</h4>
                            <span>Payment Status</span>
                        </div>
                        <svg class="status-icon-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>

                    <div class="fin-row">
                        <div class="lbl-grp">
                            <span class="ar">المبلغ المدفوع</span>
                            <span class="en">Amount Paid</span>
                        </div>
                        <span class="amt">{{ number_format($invoice->isPaid() ? $grandTotal : ($invoice->amount_paid ?? 0), 0) }} SAR</span>
                    </div>
                    <div class="fin-row" style="border-top:1px dashed #ebe5da; margin-top:2px; padding-top:4px;">
                        <div class="lbl-grp">
                            <span class="ar">المتبقي</span>
                            <span class="en">Remaining Amount</span>
                        </div>
                        <span class="amt" style="{{ $invoice->isPaid() ? '' : 'color:#dc2626; font-weight:900;' }}">
                            {{ number_format($invoice->isPaid() ? 0 : ($invoice->remaining_amount ?? $grandTotal), 0) }} SAR
                        </span>
                    </div>
                </div>

                <!-- Status pill -->
                @if($invoice->isPaid())
                    <div class="status-badge-pill paid">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <div class="status-badge-text">
                            <span class="top-txt">تم الدفع بنجاح</span>
                            <span class="sub-txt">Payment Completed</span>
                        </div>
                    </div>
                @else
                    <div class="status-badge-pill pending">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        <div class="status-badge-text">
                            <span class="top-txt">بانتظار الدفع</span>
                            <span class="sub-txt">Payment Pending</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Payment Methods Strip -->
        <div class="payment-methods-strip">
            <div class="pm-header">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/>
                </svg>
                <div>وسائل الدفع <span>Payment Methods</span></div>
            </div>
            <div class="pm-logos-row">
                <div class="pm-pill mada"><span class="dot"></span> مدى mada</div>
                <div class="pm-pill visa">VISA</div>
                <div class="pm-pill mc">
                    <span class="c2"></span>
                    <span class="c1"></span>
                    <span>mastercard</span>
                </div>
                <div class="pm-pill apple">
                    <svg viewBox="0 0 384 512"><path d="M318.7 268.7c-.2-36.7 16.4-64.4 50-84.8-18.8-26.9-47.2-41.7-84.7-44.6-35.5-2.8-74.3 20.7-88.5 20.7-15 0-49.4-19.7-76.4-19.7C63.3 141.2 4 184.8 4 273.5c0 26.2 4.8 53.3 14.4 81.2 12.8 36.7 59 126.7 107.2 125.2 25.2-.6 43-17.9 75.8-17.9 31.8 0 48.3 17.9 76.4 17.9 48.6-.7 90.4-82.5 102.6-119.3-65.2-30.7-61.7-90-61.7-91.9zm-56.6-164.2c27.3-32.4 24.8-61.9 24-72.5-24.1 1.4-52 16.4-67.9 34.9-17.5 19.8-27.8 44.3-25.6 71.9 26.1 2 49.9-11.4 69.5-34.3z"/></svg>
                    Pay
                </div>
                <div class="pm-pill stc">stc<span>pay</span></div>
                <div class="pm-pill tabby">tabby</div>
                <div class="pm-pill tamara">تمارا</div>
            </div>
        </div>

        <!-- QR Code & Notes Footer -->
        <div class="footer-grid">
            <!-- Left: QR Code -->
            <div class="qr-box">
                <div id="qrcode"></div>
                <div class="qr-subtext">
                    امسح الرمز للدفع أو للتحقق من الحجز<br>
                    <span style="font-size:7.5px; color:#888;">Scan the QR code to pay or verify your booking</span>
                </div>
            </div>

            <!-- Right: Notes -->
            <div class="notes-box">
                <div class="notes-header">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    <div>ملاحظات <span>Notes</span></div>
                </div>
                <ul class="notes-list">
                    <li>في حال الدفع عند الوصول، يرجى الوصول قبل موعد تسجيل الدخول بساعة.</li>
                    <li>في حال خصم التأمين كاش يتم استرجاعه كاش.</li>
                    <li>في حال خصم التأمين عن طريق التحويل البنكي قد يستغرق الاسترداد حتى 24 ساعة.</li>
                    <li>يرجى الالتزام بمواعيد الدخول والخروج.</li>
                    @if($invoice->notes)
                        <li style="color:#8c734b; font-weight:800;">{{ $invoice->notes }}</li>
                    @endif
                </ul>
            </div>
        </div>

        <!-- Thank you section -->
        <div class="thank-you-bar">
            <div class="thank-you-ar">شكراً لاختياركم سكننا</div>
            <div class="thank-you-en">Thank you for choosing Sindi Residence</div>
        </div>

        <!-- Bottom Versace Greek Key Meander Pattern -->
        <div class="bottom-greek-border"></div>
    </div>

    <!-- ===== QR Code & PNG Generation Scripts ===== -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const qrUrl = '{{ $invoice->telr_transaction_url ?: route("invoices.print", $invoice) }}';
            if (typeof QRCode !== 'undefined') {
                new QRCode(document.getElementById('qrcode'), {
                    text: qrUrl,
                    width: 76,
                    height: 76,
                    colorDark: '#222222',
                    colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel.M
                });
            }
        });

        function downloadInvoicePNG() {
            const btn = document.getElementById('btn-download');
            const originalText = btn.innerHTML;
            btn.innerHTML = '⏳ جارٍ التوليد...';
            btn.disabled = true;

            const page = document.getElementById('sindi-invoice-canvas');

            html2canvas(page, {
                scale: 3,
                useCORS: true,
                backgroundColor: '#ffffff',
                logging: false,
            }).then(canvas => {
                const link = document.createElement('a');
                link.download = 'فاتورة_Sindi_Residence_{{ $invoice->customer_name }}_{{ $sindiInvoiceNo }}.png';
                link.href = canvas.toDataURL('image/png');
                link.click();

                btn.innerHTML = originalText;
                btn.disabled = false;
            }).catch(err => {
                console.error(err);
                alert('حدث خطأ أثناء تحميل الصورة.');
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        }
    </script>
</body>
</html>
