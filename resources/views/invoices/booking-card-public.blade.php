<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>كارت حجز الوحدة السكنية | توثيق العقود | {{ $invoice->customer_name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Cairo', sans-serif; }
        body {
            background: #064e3b;
            background-image: radial-gradient(circle at 50% 20%, #065f46 0%, #022c22 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px 12px;
            color: #1e293b;
        }

        /* Top Bar */
        .client-top-bar {
            width: 100%;
            max-width: 720px;
            background: rgba(6, 78, 59, 0.9);
            border: 1px solid rgba(16, 185, 129, 0.3);
            backdrop-filter: blur(12px);
            border-radius: 12px;
            padding: 10px 16px;
            margin-bottom: 16px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            color: #f8fafc;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }
        .client-brand {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .client-brand img {
            height: 28px;
            object-fit: contain;
        }
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 12px;
            border-radius: 16px;
            font-size: 12px;
            font-weight: 700;
        }
        .status-pill.paid { background: rgba(16, 185, 129, 0.25); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.5); }
        .status-pill.pending { background: rgba(245, 158, 11, 0.25); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.5); }

        .btn-client {
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
            transition: all 0.2s ease;
        }
        .btn-client.download {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #ffffff;
        }
        .btn-client.download:hover { transform: translateY(-1px); }

        /* ===== Card Container ===== */
        .card-container {
            width: 100%;
            max-width: 720px;
            display: flex;
            justify-content: center;
        }

        /* ===== The Card ===== */
        .smart-card {
            width: 100%;
            background: #ffffff;
            border: 2px solid #c59b63;
            border-radius: 16px;
            padding: 0;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        /* Top Accent Bar */
        .card-top-accent {
            height: 6px;
            background: linear-gradient(90deg, #064e3b 0%, #c59b63 50%, #064e3b 100%);
        }

        /* Card Inner */
        .card-inner {
            padding: 20px 24px 16px;
            position: relative;
        }

        /* Watermark */
        .watermark-bg {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.04;
            pointer-events: none;
            z-index: 0;
            width: 280px;
            height: 280px;
        }
        .watermark-bg img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        /* Header */
        .header-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 2;
            margin-bottom: 16px;
            text-align: center;
        }
        .header-logo {
            height: 44px;
            object-fit: contain;
            margin-bottom: 6px;
        }
        .header-title {
            font-size: 22px;
            font-weight: 900;
            color: #064e3b;
            line-height: 1.3;
        }
        .header-subtitle {
            font-size: 11px;
            font-weight: 700;
            color: #b4852e;
            letter-spacing: 1px;
            margin-top: 1px;
        }

        /* ===== Data Table Rows ===== */
        .data-rows-container {
            display: flex;
            flex-direction: column;
            gap: 8px;
            position: relative;
            z-index: 2;
            margin-bottom: 12px;
        }

        .data-row {
            background: #fafcfb;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            overflow: hidden;
        }

        .data-cell {
            padding: 8px 12px;
            display: flex;
            align-items: center;
            gap: 5px;
            min-height: 38px;
        }
        .data-cell:not(:last-child) {
            border-left: 1px solid #d1d5db;
        }
        .data-cell .cell-label {
            font-size: 12px;
            font-weight: 800;
            color: #065f46;
            white-space: nowrap;
        }
        .data-cell .cell-val {
            font-size: 12.5px;
            font-weight: 700;
            color: #1e293b;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .data-cell .cell-val.ltr {
            direction: ltr;
            text-align: left;
        }

        /* ===== Info Boxes ===== */
        .info-boxes-container {
            display: flex;
            flex-direction: column;
            gap: 8px;
            position: relative;
            z-index: 2;
            margin-bottom: 12px;
        }

        .info-box {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: 10px 14px;
            position: relative;
            overflow: hidden;
        }
        .info-box.deposit-box {
            background: #fffbeb;
            border-color: #fde68a;
        }
        .info-box-watermark {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            opacity: 0.05;
            pointer-events: none;
            white-space: nowrap;
            font-size: 28px;
            font-weight: 900;
            color: #064e3b;
        }
        .info-box-title {
            font-size: 13px;
            font-weight: 800;
            color: #064e3b;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .info-box.deposit-box .info-box-title {
            color: #92400e;
        }
        .info-box-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .info-box-list li {
            font-size: 12px;
            font-weight: 600;
            color: #374151;
            line-height: 1.7;
        }
        .info-box.deposit-box .info-box-list li {
            color: #78350f;
        }

        /* ===== Payment Logos ===== */
        .payment-bar {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 0 4px;
            border-top: 1px dashed #e5e7eb;
            flex-wrap: wrap;
        }

        .pay-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 30px;
            padding: 0 12px;
            border-radius: 6px;
            font-weight: 800;
            font-size: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            border: 1px solid rgba(0,0,0,0.08);
            background: #ffffff;
        }
        .pay-pill.apple {
            color: #000000;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-weight: 700;
            font-size: 13px;
            gap: 3px;
        }
        .pay-pill.apple svg { width: 13px; height: 13px; fill: #000000; }
        .pay-pill.mada {
            color: #00843d;
            font-weight: 900;
            font-size: 12px;
            gap: 4px;
        }
        .pay-pill.mada .circle {
            width: 6px; height: 6px; border-radius: 50%; background: #00843d;
        }
        .pay-pill.stc {
            color: #4f008c;
            font-weight: 900;
            font-size: 13px;
        }
        .pay-pill.stc span { color: #00c48c; margin-left: 2px; }
        .pay-pill.tabby {
            background: #3bffc0;
            color: #111827;
            font-weight: 900;
            font-size: 13px;
        }
        .pay-pill.tamara {
            background: linear-gradient(135deg, #ffd3b6 0%, #ffaaa5 100%);
            color: #2d1810;
            font-weight: 900;
            font-size: 13px;
        }

        /* Footer */
        .card-meta-footer {
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px dashed #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 10px;
            color: #9ca3af;
            font-weight: 600;
            position: relative;
            z-index: 2;
        }

        /* ===== Pay CTA & Interactive Payment Portal ===== */
        .pay-portal-wrapper {
            width: 100%;
            max-width: 720px;
            margin-top: 18px;
            background: #ffffff;
            border-radius: 16px;
            padding: 22px 24px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            border: 1px solid #e5e7eb;
            text-align: center;
        }
        .pay-portal-header h3 {
            font-size: 17px;
            font-weight: 900;
            color: #111827;
            margin-bottom: 4px;
        }
        .pay-portal-header p {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 16px;
        }
        .pay-portal-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 18px;
        }
        @media (max-width: 680px) {
            .pay-portal-grid { grid-template-columns: 1fr; }
        }
        .pay-option-card {
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 12px;
            text-align: right;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
            background: #fafafa;
        }
        .pay-option-card:hover {
            border-color: #10b981;
            transform: translateY(-2px);
        }
        .pay-option-card.selected {
            border-color: #059669;
            background: #f0fdf4;
            box-shadow: 0 4px 14px rgba(5, 150, 105, 0.15);
        }
        .pay-option-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .pay-option-badge {
            font-size: 9.5px;
            font-weight: 800;
            padding: 2px 8px;
            border-radius: 20px;
        }
        .badge-free { background: #dcfce7; color: #15803d; }
        .badge-tabby { background: #3effbf; color: #064e3b; }
        .badge-tamara { background: #ffe4d6; color: #9a3412; }
        .pay-option-radio {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            border: 2px solid #d1d5db;
            position: relative;
        }
        .pay-option-card.selected .pay-option-radio {
            border-color: #059669;
            background: #059669;
        }
        .pay-option-card.selected .pay-option-radio::after {
            content: '';
            position: absolute;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #ffffff;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        .pay-option-title {
            font-size: 13px;
            color: #1f2937;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .brand-tabby {
            background: #3effbf;
            color: #111827;
            font-size: 10px;
            font-weight: 900;
            padding: 1px 6px;
            border-radius: 4px;
        }
        .brand-tamara {
            background: #fed7aa;
            color: #7c2d12;
            font-size: 10px;
            font-weight: 900;
            padding: 1px 6px;
            border-radius: 4px;
        }
        .pay-option-price {
            font-size: 18px;
            font-weight: 900;
            color: #065f46;
            margin-bottom: 4px;
            font-family: monospace, sans-serif;
        }
        .pay-option-price small {
            font-size: 12px;
            font-weight: 700;
        }
        .pay-option-sub {
            font-size: 11px;
            color: #4b5563;
            line-height: 1.4;
        }
        .pay-action-btn {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            color: #ffffff;
            border: none;
            border-radius: 12px;
            padding: 14px 32px;
            font-size: 15px;
            font-weight: 900;
            width: 100%;
            cursor: pointer;
            box-shadow: 0 6px 18px rgba(16, 185, 129, 0.35);
            transition: all 0.2s ease;
        }
        .pay-action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(16, 185, 129, 0.45);
        }

        /* BNPL Modal Overlay & Content */
        .bnpl-modal-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(4px);
            z-index: 99999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }
        .bnpl-modal-box {
            background: #ffffff;
            border-radius: 16px;
            max-width: 440px;
            width: 100%;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            overflow: hidden;
            animation: bnplFadeIn 0.25s ease;
            text-align: right;
            direction: rtl;
        }
        @keyframes bnplFadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
        .bnpl-modal-header {
            padding: 14px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .tabby-hdr { background: #3effbf; color: #064e3b; }
        .tamara-hdr { background: #fed7aa; color: #7c2d12; }
        .bnpl-close-btn {
            background: transparent;
            border: none;
            font-size: 24px;
            line-height: 1;
            cursor: pointer;
            color: inherit;
        }
        .bnpl-modal-body {
            padding: 18px 20px;
        }
        .bnpl-summary-box {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 10px;
            padding: 10px 14px;
            margin-bottom: 14px;
        }
        .bnpl-summary-box.tamara-summary {
            background: #fff7ed;
            border-color: #fed7aa;
        }
        .bnpl-schedule {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 6px;
            margin-top: 10px;
        }
        .bnpl-step {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 8px 4px;
            text-align: center;
        }
        .bnpl-dot {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #e5e7eb;
            color: #4b5563;
            font-size: 11px;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 6px;
        }
        .bnpl-dot.active {
            background: #10b981;
            color: #ffffff;
        }
        .bnpl-dot.tamara-dot.active {
            background: #ea580c;
            color: #ffffff;
        }
        .step-title {
            font-size: 9.5px;
            color: #6b7280;
            display: block;
            margin-bottom: 2px;
        }
        .step-amount {
            font-size: 11px;
            font-weight: 800;
            color: #111827;
            font-family: monospace;
        }

        /* Responsive */
        @media (max-width: 640px) {
            .card-inner { padding: 14px 14px 12px; }
            .header-title { font-size: 18px; }
            .header-logo { height: 36px; }
            .data-row { grid-template-columns: 1fr; }
            .data-cell:not(:last-child) {
                border-left: none;
                border-bottom: 1px solid #d1d5db;
            }
            .payment-bar { gap: 6px; }
            .card-meta-footer { flex-direction: column; gap: 4px; text-align: center; }
            .online-pay-cta { flex-direction: column; text-align: center; }
        }
    </style>
</head>
<body>

    <!-- Customer Top Bar -->
    <div class="client-top-bar">
        <div class="client-brand">
            <img src="{{ asset('images/image.png') }}" alt="توثيق العقود">
            <div>
                <strong style="font-size:13px; color:#ffffff;">منصة توثيق العقود</strong>
                <span style="font-size:11px; color:#a7f3d0; margin-right:6px;">#{{ $invoice->invoice_number }}</span>
            </div>
        </div>

        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
            @if($invoice->isPaid())
                <span class="status-pill paid">✅ تم السداد</span>
            @elseif($invoice->isPending())
                <span class="status-pill pending">⏳ {{ number_format($invoice->amount, 0) }} ريال</span>
            @endif

            <a href="{{ route('invoices.print', $invoice) }}" target="_blank" class="btn-client" style="background:rgba(255,255,255,0.15); color:#ffffff; border:1px solid rgba(255,255,255,0.25); text-decoration:none; display:inline-flex; align-items:center; gap:4px;">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                عرض الفاتورة
            </a>

            <button onclick="downloadCardImage()" class="btn-client download" id="btn-download">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                حفظ كصورة
            </button>
        </div>
    </div>

    <!-- ===== The Smart Card ===== -->
    <div class="card-container">
        <div class="smart-card" id="hotel-smart-card">

            <div class="card-top-accent"></div>

            <div class="card-inner">

                <!-- Watermark -->
                <div class="watermark-bg">
                    <img src="{{ asset('images/image.png') }}" alt="توثيق العقود">
                </div>

                <!-- Header -->
                <div class="header-section">
                    <img src="{{ asset('images/image.png') }}" alt="توثيق العقود" class="header-logo">
                    <h1 class="header-title">تفاصيل حجز الوحدة السكنية</h1>
                    <p class="header-subtitle">TAOTHEEQ AL-OQOOD PLATFORM</p>
                </div>

                <!-- ===== 3 Data Rows ===== -->
                <div class="data-rows-container">

                    <div class="data-row">
                        <div class="data-cell">
                            <span class="cell-label">اسم العميل:</span>
                            <span class="cell-val">{{ $invoice->customer_name }}</span>
                        </div>
                        <div class="data-cell">
                            <span class="cell-label">رقم هوية العميل:</span>
                            <span class="cell-val">{{ $invoice->national_id ?: '—' }}</span>
                        </div>
                        <div class="data-cell">
                            <span class="cell-label">رقم جوال العميل:</span>
                            <span class="cell-val ltr">{{ $invoice->phone ?: '—' }}</span>
                        </div>
                    </div>

                    <div class="data-row">
                        <div class="data-cell">
                            <span class="cell-label">رقم الوحدة:</span>
                            <span class="cell-val" style="color:#065f46; font-weight:800;">{{ $invoice->suite_number ?: $invoice->apartment_number }}</span>
                        </div>
                        <div class="data-cell">
                            <span class="cell-label">تاريخ الدخول:</span>
                            <span class="cell-val">{{ $invoice->check_in_date ? $invoice->check_in_date->format('Y-m-d') : '—' }}</span>
                        </div>
                        <div class="data-cell">
                            <span class="cell-label">تاريخ الخروج:</span>
                            <span class="cell-val">{{ $invoice->check_out_date ? $invoice->check_out_date->format('Y-m-d') : '—' }}</span>
                        </div>
                    </div>

                    <div class="data-row">
                        <div class="data-cell">
                            <span class="cell-label">المبلغ المتفق عليه:</span>
                            <span class="cell-val">{{ number_format($invoice->agreed_amount ?? $invoice->amount, 0) }} ريال</span>
                        </div>
                        <div class="data-cell">
                            <span class="cell-label">المبلغ المطلوب دفعه:</span>
                            <span class="cell-val" style="color:#059669; font-weight:800;">{{ number_format($invoice->amount, 0) }} ريال</span>
                        </div>
                        <div class="data-cell">
                            <span class="cell-label">المبلغ المتبقي:</span>
                            <span class="cell-val" style="color:#b91c1c; font-weight:800;">{{ number_format($invoice->remaining_amount, 0) }} ريال</span>
                        </div>
                    </div>

                </div>

                <!-- ===== Info Boxes ===== -->
                <div class="info-boxes-container">
                    <div class="info-box">
                        <div class="info-box-watermark">توثيق العقود</div>
                        <div class="info-box-title">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            مواعيد الدخول والخروج:
                        </div>
                        <ul class="info-box-list">
                            <li>• وقت الدخول: الساعة ٤:٠٠ مساءً.</li>
                            <li>• وقت الخروج: الساعة ١:٠٠ ظهرًا.</li>
                        </ul>
                    </div>

                    <div class="info-box deposit-box">
                        <div class="info-box-title">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            ملاحظة بخصوص التأمين:
                        </div>
                        <ul class="info-box-list">
                            @if(($invoice->deposit_amount ?? 0) > 0)
                                <li>يوجد تأمين مسترد بقيمة {{ number_format($invoice->deposit_amount, 0) }} ريال، مسترد عند المغادرة.</li>
                            @else
                                <li>لا يوجد تأمين إضافي مطلوب.</li>
                            @endif
                            <li>• في حال دفع التأمين نقدًا، يتم استرجاعه نقدًا عند تسليم الوحدة.</li>
                            <li>• في حال دفع التأمين إلكترونيًا، يتم استرجاعه خلال ٢٤ ساعة من فحص وتسليم الوحدة.</li>
                        </ul>
                    </div>
                </div>



                <!-- Footer -->
                <div class="card-meta-footer">
                    <div>رقم الفاتورة: <strong style="color:#064e3b;">{{ $invoice->invoice_number }}</strong></div>
                    <div>تم التوثيق عبر: <strong style="color:#064e3b;">منصة توثيق العقود</strong></div>
                    <div>تاريخ الإصدار: {{ $invoice->created_at->format('Y-m-d') }}</div>
                </div>

            </div>
        </div>
    </div>

    <!-- ===== Interactive Payment Gateway Selector & Pay CTA ===== -->
    @if($invoice->isPending())
    @php
        $base = $invoice->base_amount;
        $tabbyTotal = round($base * 1.15, 2);
        $tabbyInstallment = round($tabbyTotal / 4, 2);
        $tamaraTotal = round($base * 1.15, 2);
        $tamaraInstallment = round($tamaraTotal / 4, 2);
        $activeMethod = in_array($invoice->payment_method, ['tabby', 'tamara', 'apple_pay', 'standard']) ? $invoice->payment_method : 'apple_pay';
    @endphp
    <div class="pay-portal-wrapper">
        <div class="pay-portal-header">
            <h3>اختر وسيلة الدفع لإتمام الحجز فوراً</h3>
            <p>سداد إلكتروني معتمد ومشفّر بأعلى معايير الأمان البنكي</p>
        </div>

        <div class="pay-portal-grid">
            <!-- 1. Apple Pay / Mada / Visa -->
            <div class="pay-option-card {{ $activeMethod === 'apple_pay' || $activeMethod === 'standard' ? 'selected' : '' }}" onclick="selectMethod('apple_pay', 'Apple Pay / مدى', '{{ number_format($base, 2) }}')" id="method-card-apple_pay">
                <div class="pay-option-top">
                    <div class="pay-option-badge badge-free">0% رسوم إضافية</div>
                    <div class="pay-option-radio"></div>
                </div>
                <div class="pay-option-title flex-title">
                    <svg style="width: 15px; height: 15px; display:inline-block; vertical-align:middle; fill:#000;" viewBox="0 0 24 24"><path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 6.84c.62-.75 1.04-1.8 0.92-2.84-.9.04-1.98.6-2.62 1.34-.57.65-1.06 1.71-.93 2.73 1 .08 2.01-.48 2.63-1.23z"/></svg>
                    <strong>Apple Pay / مدى / بطاقات</strong>
                </div>
                <div class="pay-option-price">{{ number_format($base, 2) }} <small>ر.س</small></div>
                <div class="pay-option-sub">سداد فوري للمبلغ بالكامل</div>
            </div>

            <!-- 2. Tabby -->
            <div class="pay-option-card {{ $activeMethod === 'tabby' ? 'selected' : '' }}" onclick="selectMethod('tabby', 'تابي (4 أقساط)', '{{ number_format($tabbyTotal, 2) }}')" id="method-card-tabby">
                <div class="pay-option-top">
                    <div class="pay-option-badge badge-tabby">قسمها على 4 دفعات</div>
                    <div class="pay-option-radio"></div>
                </div>
                <div class="pay-option-title">
                    <span class="brand-tabby">tabby</span> <strong>تابي</strong>
                </div>
                <div class="pay-option-price">{{ number_format($tabbyTotal, 2) }} <small>ر.س</small></div>
                <div class="pay-option-sub">
                    <strong>{{ number_format($tabbyInstallment, 2) }} ر.س</strong> / شهرياً (4 دفعات)<br>
                    <span style="font-size:10px; color:#065f46; font-weight:700;">(شامل 15% رسوم تابي: +{{ number_format($base * 0.15, 2) }} ر.س)</span>
                </div>
            </div>

            <!-- 3. Tamara -->
            <div class="pay-option-card {{ $activeMethod === 'tamara' ? 'selected' : '' }}" onclick="selectMethod('tamara', 'تمارا (4 أقساط)', '{{ number_format($tamaraTotal, 2) }}')" id="method-card-tamara">
                <div class="pay-option-top">
                    <div class="pay-option-badge badge-tamara">قسمها على 4 دفعات</div>
                    <div class="pay-option-radio"></div>
                </div>
                <div class="pay-option-title">
                    <span class="brand-tamara">تمارا</span> <strong>تمارا</strong>
                </div>
                <div class="pay-option-price">{{ number_format($tamaraTotal, 2) }} <small>ر.س</small></div>
                <div class="pay-option-sub">
                    <strong>{{ number_format($tamaraInstallment, 2) }} ر.س</strong> / شهرياً (4 دفعات)<br>
                    <span style="font-size:10px; color:#9a3412; font-weight:700;">(شامل 15% رسوم تمارا: +{{ number_format($base * 0.15, 2) }} ر.س)</span>
                </div>
            </div>
        </div>

        <div class="pay-portal-action">
            <button type="button" id="pay-submit-btn" onclick="proceedPayment()" class="pay-action-btn">
                💳 متابعة الدفع عبر <span id="selected-method-label">{{ $activeMethod === 'tabby' ? 'تابي (4 أقساط)' : ($activeMethod === 'tamara' ? 'تمارا (4 أقساط)' : 'Apple Pay / مدى') }}</span> (<span id="selected-method-amount">{{ number_format($activeMethod === 'tabby' ? $tabbyTotal : ($activeMethod === 'tamara' ? $tamaraTotal : $base), 2) }}</span> ر.س)
            </button>
        </div>
    </div>
    @endif

    <script>
        let currentMethod = '{{ $activeMethod }}';

        function selectMethod(method, label, amount) {
            currentMethod = method;
            document.querySelectorAll('.pay-option-card').forEach(c => c.classList.remove('selected'));
            const selectedCard = document.getElementById('method-card-' + method);
            if (selectedCard) selectedCard.classList.add('selected');

            const labelEl = document.getElementById('selected-method-label');
            const amountEl = document.getElementById('selected-method-amount');
            if (labelEl) labelEl.textContent = label;
            if (amountEl) amountEl.textContent = amount;
        }

        function proceedPayment() {
            if (currentMethod === 'tabby') {
                @if($invoice->tabby_url)
                    window.location.href = '{{ $invoice->tabby_url }}';
                    return;
                @else
                    openTabbyModal();
                    return;
                @endif
            }
            if (currentMethod === 'tamara') {
                @if($invoice->tamara_url)
                    window.location.href = '{{ $invoice->tamara_url }}';
                    return;
                @else
                    openTamaraModal();
                    return;
                @endif
            }

            const btn = document.getElementById('pay-submit-btn');
            btn.innerHTML = '⏳ جارٍ الانتقال لبوابة الدفع الآمنة...';
            btn.disabled = true;

            const url = '{{ route("booking-card.pay", $invoice->booking_card_token) }}';
            window.location.href = url + '?method=' + encodeURIComponent(currentMethod);
        }

        function openTabbyModal() {
            document.getElementById('tabby-modal').style.display = 'flex';
        }
        function openTamaraModal() {
            document.getElementById('tamara-modal').style.display = 'flex';
        }
        function closeBnplModals() {
            document.getElementById('tabby-modal').style.display = 'none';
            document.getElementById('tamara-modal').style.display = 'none';
        }

        function submitBnpl(method) {
            const phoneInput = document.getElementById(method + '-phone');
            const errorEl = document.getElementById(method + '-error');
            const btn = document.getElementById(method + '-confirm-btn');

            const phone = phoneInput ? phoneInput.value.trim() : '';
            if (!phone || phone.length < 9) {
                if (errorEl) {
                    errorEl.textContent = 'يرجى إدخال رقم جوال صحيح (+966)';
                    errorEl.style.display = 'block';
                }
                return;
            }
            if (errorEl) errorEl.style.display = 'none';

            btn.disabled = true;
            btn.innerHTML = '⏳ جارٍ معالجة وتأكيد الدفع عبر ' + (method === 'tabby' ? 'تابي' : 'تمارا') + '...';

            fetch('{{ route("booking-card.confirm-bnpl", $invoice->booking_card_token) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    method: method,
                    phone: phone
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(data.message || '✅ تم السداد وتأكيد الحجز بنجاح!');
                    window.location.href = data.redirect_url || window.location.href;
                } else {
                    alert('حدث خطأ: ' + (data.error || 'تعذر تأكيد العملية'));
                    btn.disabled = false;
                    btn.innerHTML = 'إعادة المحاولة';
                }
            })
            .catch(err => {
                console.error(err);
                alert('حدث خطأ أثناء معالجة الطلب.');
                btn.disabled = false;
                btn.innerHTML = 'إعادة المحاولة';
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const params = new URLSearchParams(window.location.search);
            if (params.get('open_modal') === 'tabby') {
                selectMethod('tabby', 'تابي (4 أقساط)', '{{ number_format($tabbyTotal, 2) }}');
                openTabbyModal();
            } else if (params.get('open_modal') === 'tamara') {
                selectMethod('tamara', 'تمارا (4 أقساط)', '{{ number_format($tamaraTotal, 2) }}');
                openTamaraModal();
            }
        });
    </script>

    <!-- Tabby Modal -->
    <div id="tabby-modal" class="bnpl-modal-overlay" style="display:none;">
        <div class="bnpl-modal-box">
            <div class="bnpl-modal-header tabby-hdr">
                <div style="display:flex; align-items:center; gap:8px;">
                    <span style="background:#111; color:#3effbf; padding:3px 8px; border-radius:6px; font-weight:900; font-size:14px;">tabby</span>
                    <strong style="font-size:15px; color:#111;">الدفع بالتقسيط عبر تابي</strong>
                </div>
                <button type="button" onclick="closeBnplModals()" class="bnpl-close-btn">&times;</button>
            </div>
            <div class="bnpl-modal-body">
                <p style="font-size:12px; color:#4b5563; margin-bottom:12px;">قسّم فاتورتك على 4 دفعات بدون أي فوائد خفية متوافقة مع الشريعة الإسلامية.</p>

                <div class="bnpl-summary-box">
                    <div style="display:flex; justify-content:space-between; font-size:13px; font-weight:700; color:#111; margin-bottom:4px;">
                        <span>إجمالي الفاتورة شامل التقسيط (+15%):</span>
                        <span style="color:#059669; font-family:monospace; font-size:16px;">{{ number_format($tabbyTotal, 2) }} ر.س</span>
                    </div>
                    <div style="font-size:11px; color:#6b7280;">(المبلغ الأساسي {{ number_format($base, 2) }} ر.س + رسوم تابي {{ number_format($base * 0.15, 2) }} ر.س)</div>
                </div>

                <div class="bnpl-schedule">
                    <div class="bnpl-step">
                        <div class="bnpl-dot active">1</div>
                        <span class="step-title">اليوم</span>
                        <span class="step-amount">{{ number_format($tabbyInstallment, 2) }}</span>
                    </div>
                    <div class="bnpl-step">
                        <div class="bnpl-dot">2</div>
                        <span class="step-title">بعد شهر</span>
                        <span class="step-amount">{{ number_format($tabbyInstallment, 2) }}</span>
                    </div>
                    <div class="bnpl-step">
                        <div class="bnpl-dot">3</div>
                        <span class="step-title">بعد شهرين</span>
                        <span class="step-amount">{{ number_format($tabbyInstallment, 2) }}</span>
                    </div>
                    <div class="bnpl-step">
                        <div class="bnpl-dot">4</div>
                        <span class="step-title">بعد 3 أشهر</span>
                        <span class="step-amount">{{ number_format($tabbyInstallment, 2) }}</span>
                    </div>
                </div>

                <div style="margin-top:14px;">
                    <label style="font-size:12px; font-weight:700; color:#374151; display:block; margin-bottom:4px;">رقم الجوال المسجل في تابي (+966):</label>
                    <input type="tel" id="tabby-phone" value="{{ $invoice->phone }}" dir="ltr" placeholder="05XXXXXXXX" style="width:100%; padding:10px 12px; border:1.5px solid #d1d5db; border-radius:8px; font-size:14px; font-weight:700; text-align:center;">
                </div>

                <div id="tabby-error" style="color:#dc2626; font-size:12px; font-weight:700; margin-top:8px; display:none;"></div>

                <button type="button" id="tabby-confirm-btn" onclick="submitBnpl('tabby')" style="margin-top:16px; width:100%; background:#3effbf; color:#064e3b; border:none; padding:12px; border-radius:10px; font-size:15px; font-weight:900; cursor:pointer;">
                    تأكيد الدفع عبر تابي ({{ number_format($tabbyTotal, 2) }} ر.س)
                </button>
            </div>
        </div>
    </div>

    <!-- Tamara Modal -->
    <div id="tamara-modal" class="bnpl-modal-overlay" style="display:none;">
        <div class="bnpl-modal-box">
            <div class="bnpl-modal-header tamara-hdr">
                <div style="display:flex; align-items:center; gap:8px;">
                    <span style="background:#fed7aa; color:#7c2d12; padding:3px 8px; border-radius:6px; font-weight:900; font-size:14px;">تمارا</span>
                    <strong style="font-size:15px; color:#7c2d12;">الدفع بالتقسيط عبر تمارا</strong>
                </div>
                <button type="button" onclick="closeBnplModals()" class="bnpl-close-btn">&times;</button>
            </div>
            <div class="bnpl-modal-body">
                <p style="font-size:12px; color:#4b5563; margin-bottom:12px;">قسّمها على 4 دفعات مريحة ومتوافقة مع الشريعة عبر تمارا.</p>

                <div class="bnpl-summary-box tamara-summary">
                    <div style="display:flex; justify-content:space-between; font-size:13px; font-weight:700; color:#111; margin-bottom:4px;">
                        <span>إجمالي الفاتورة شامل التقسيط (+15%):</span>
                        <span style="color:#c2410c; font-family:monospace; font-size:16px;">{{ number_format($tamaraTotal, 2) }} ر.س</span>
                    </div>
                    <div style="font-size:11px; color:#6b7280;">(المبلغ الأساسي {{ number_format($base, 2) }} ر.س + رسوم تمارا {{ number_format($base * 0.15, 2) }} ر.س)</div>
                </div>

                <div class="bnpl-schedule">
                    <div class="bnpl-step">
                        <div class="bnpl-dot tamara-dot active">1</div>
                        <span class="step-title">اليوم</span>
                        <span class="step-amount">{{ number_format($tamaraInstallment, 2) }}</span>
                    </div>
                    <div class="bnpl-step">
                        <div class="bnpl-dot tamara-dot">2</div>
                        <span class="step-title">بعد شهر</span>
                        <span class="step-amount">{{ number_format($tamaraInstallment, 2) }}</span>
                    </div>
                    <div class="bnpl-step">
                        <div class="bnpl-dot tamara-dot">3</div>
                        <span class="step-title">بعد شهرين</span>
                        <span class="step-amount">{{ number_format($tamaraInstallment, 2) }}</span>
                    </div>
                    <div class="bnpl-step">
                        <div class="bnpl-dot tamara-dot">4</div>
                        <span class="step-title">بعد 3 أشهر</span>
                        <span class="step-amount">{{ number_format($tamaraInstallment, 2) }}</span>
                    </div>
                </div>

                <div style="margin-top:14px;">
                    <label style="font-size:12px; font-weight:700; color:#374151; display:block; margin-bottom:4px;">رقم الجوال المسجل في تمارا (+966):</label>
                    <input type="tel" id="tamara-phone" value="{{ $invoice->phone }}" dir="ltr" placeholder="05XXXXXXXX" style="width:100%; padding:10px 12px; border:1.5px solid #d1d5db; border-radius:8px; font-size:14px; font-weight:700; text-align:center;">
                </div>

                <div id="tamara-error" style="color:#dc2626; font-size:12px; font-weight:700; margin-top:8px; display:none;"></div>

                <button type="button" id="tamara-confirm-btn" onclick="submitBnpl('tamara')" style="margin-top:16px; width:100%; background:linear-gradient(135deg, #ea580c 0%, #c2410c 100%); color:#ffffff; border:none; padding:12px; border-radius:10px; font-size:15px; font-weight:900; cursor:pointer;">
                    تأكيد الدفع عبر تمارا ({{ number_format($tamaraTotal, 2) }} ر.س)
                </button>
            </div>
        </div>
    </div>

    <!-- Download Script -->
    <script>
        function downloadCardImage() {
            const btn = document.getElementById('btn-download');
            const originalText = btn.innerHTML;
            btn.innerHTML = '⏳ جارٍ التوليد...';
            btn.disabled = true;

            const card = document.getElementById('hotel-smart-card');

            html2canvas(card, {
                scale: 2.5,
                useCORS: true,
                backgroundColor: '#ffffff',
                logging: false,
            }).then(canvas => {
                const link = document.createElement('a');
                link.download = 'كارت_حجز_{{ $invoice->customer_name }}_{{ $invoice->invoice_number }}.png';
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
