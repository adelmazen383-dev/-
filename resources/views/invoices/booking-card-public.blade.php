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

        /* ===== Pay CTA ===== */
        .online-pay-cta {
            width: 100%;
            max-width: 720px;
            margin-top: 14px;
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            border-radius: 12px;
            padding: 16px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
            text-align: center;
            flex-wrap: wrap;
        }
        .online-pay-cta .cta-text h3 {
            color: #ffffff;
            font-size: 16px;
            font-weight: 900;
            margin-bottom: 2px;
        }
        .online-pay-cta .cta-text p {
            color: #d1fae5;
            font-size: 12px;
            font-weight: 600;
        }
        .online-pay-cta .cta-btn {
            background: #ffffff;
            color: #065f46;
            font-size: 14px;
            font-weight: 900;
            padding: 10px 28px;
            border-radius: 10px;
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
            transition: all 0.2s ease;
            white-space: nowrap;
        }
        .online-pay-cta .cta-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(0,0,0,0.2);
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
                            <li>يوجد تأمين مسترد بقيمة {{ number_format($invoice->deposit_amount ?? 500, 0) }} ريال، يجب دفعه عند الاستلام.</li>
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

    <!-- ===== Pay CTA ===== -->
    @if($invoice->isPending() && $invoice->telr_transaction_url)
    <div class="online-pay-cta">
        <div class="cta-text">
            <h3>سداد الفاتورة إلكترونياً بأمان</h3>
            <p>المبلغ: {{ number_format($invoice->amount, 0) }} ريال سعودي (مدى / Apple Pay / فيزا)</p>
        </div>
        <a href="{{ $invoice->telr_transaction_url }}" target="_blank" class="cta-btn">
            💳 ادفع الآن
        </a>
    </div>
    @endif

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
