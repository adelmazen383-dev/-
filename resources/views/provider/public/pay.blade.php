<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>فاتورة {{ $invoice->invoice_number }} | {{ $provider->business_name }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])

    <style>
        * { font-family: 'Cairo', sans-serif; box-sizing: border-box; }
        body {
            background: #f1f5f9;
            color: #0f172a;
            margin: 0;
            padding: 16px 12px 40px;
        }
        .pay-card {
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            padding: 14px;
            background: #ffffff;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .pay-card:hover { border-color: #6366f1; }
        .pay-card.selected {
            border-color: #4f46e5;
            background: #eef2ff;
            box-shadow: 0 4px 14px rgba(79, 70, 229, 0.14);
        }
    </style>
</head>
<body>
    @php
        $appleCalc  = $invoice->calculateTotalForMethod('apple_pay');
        $visaCalc   = $invoice->calculateTotalForMethod('standard');
        $tabbyCalc  = $invoice->calculateTotalForMethod('tabby');
        $tamaraCalc = $invoice->calculateTotalForMethod('tamara');

        $tabbyInst  = round($tabbyCalc['total'] / 4, 2);
        $tamaraInst = round($tamaraCalc['total'] / 4, 2);
    @endphp

    <div class="max-w-2xl mx-auto space-y-5">
        @if(session('success'))
            <div class="rounded-2xl bg-emerald-50 border-2 border-emerald-300 text-emerald-900 px-4 py-3.5 text-sm font-extrabold">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="rounded-2xl bg-rose-50 border-2 border-rose-300 text-rose-900 px-4 py-3.5 text-sm font-extrabold">
                {{ session('error') }}
            </div>
        @endif
        @if(session('warning'))
            <div class="rounded-2xl bg-amber-50 border-2 border-amber-300 text-amber-900 px-4 py-3.5 text-sm font-extrabold">
                {{ session('warning') }}
            </div>
        @endif

        <!-- Provider's Service-Specific Invoice Template -->
        @include($invoice->templateView(), [
            'invoice'          => $invoice,
            'provider'         => $provider,
            'showMethodsStrip' => $invoice->isPaid(),
            'attachmentUrl'    => $invoice->quote_attachment_path ? route('provider-pay.attachment', $invoice->public_token) : null,
        ])

        <!-- Paid Confirmation Banner OR Interactive Payment Selector -->
        @if($invoice->isPaid())
            <div class="rounded-2xl bg-emerald-50 border-2 border-emerald-400 p-6 text-center shadow-sm">
                <div class="text-4xl mb-2">✅</div>
                <h2 class="text-lg font-black text-emerald-900">تم سداد الفاتورة بنجاح!</h2>
                <p class="text-xs sm:text-sm text-emerald-800 font-semibold mt-1">
                    تم تأكيد الدفع عبر <strong>{{ $invoice->paymentMethodCleanLabel() }}</strong>
                    بتاريخ {{ $invoice->paid_at?->format('Y-m-d H:i') }}
                </p>
                <button onclick="window.print()" class="mt-4 px-5 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-extrabold cursor-pointer">
                    🖨️ طباعة / حفظ الفاتورة
                </button>
            </div>
        @elseif($invoice->status !== 'cancelled')
            <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-6 shadow-sm">
                <div class="text-center mb-4">
                    <h3 class="text-base sm:text-lg font-black text-slate-900">اختر وسيلة الدفع لإتمام السداد</h3>
                    <p class="text-xs text-slate-500 mt-0.5">دفع إلكتروني آمن ومشفّر بأعلى معايير الحماية البنكية</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-5">
                    <!-- 1. Apple Pay -->
                    <div class="pay-card" id="p-method-apple_pay" onclick="pickProviderMethod('apple_pay', 'Apple Pay', '{{ number_format($appleCalc['total'], 2) }}')">
                        <div class="flex items-center justify-between mb-2">
                            <span id="p-apple-badge" class="px-2.5 py-0.5 rounded-full bg-black text-white text-[11px] font-bold"> دفع فوري</span>
                            <span class="w-4 h-4 rounded-full border-2 border-slate-300 method-dot"></span>
                        </div>
                        <div class="font-black text-sm text-slate-900">Apple Pay</div>
                        <div class="text-xl font-black text-emerald-700 mt-1">{{ number_format($appleCalc['total'], 2) }} <span class="text-xs font-bold">ر.س</span></div>
                        <div class="text-[11px] text-slate-500 mt-0.5">محفظة Apple Wallet · سداد فوري</div>
                    </div>

                    <!-- 2. Mada / Visa / Mastercard -->
                    <div class="pay-card selected" id="p-method-standard" onclick="pickProviderMethod('standard', 'بطاقة مدى / فيزا / ماستركارد', '{{ number_format($visaCalc['total'], 2) }}')">
                        <div class="flex items-center justify-between mb-2">
                            <span class="px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200 text-[11px] font-bold">دفع مباشر</span>
                            <span class="w-4 h-4 rounded-full border-2 border-indigo-600 bg-indigo-600 method-dot"></span>
                        </div>
                        <div class="font-black text-sm text-slate-900">💳 بطاقة مدى / فيزا</div>
                        <div class="text-xl font-black text-emerald-700 mt-1">{{ number_format($visaCalc['total'], 2) }} <span class="text-xs font-bold">ر.س</span></div>
                        <div class="text-[11px] text-slate-500 mt-0.5">مدى · Visa · Mastercard</div>
                    </div>

                    <!-- 3. Tabby -->
                    <div class="pay-card" id="p-method-tabby" onclick="pickProviderMethod('tabby', 'تابي (4 دفعات)', '{{ number_format($tabbyCalc['total'], 2) }}')">
                        <div class="flex items-center justify-between mb-2">
                            <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-900 text-[11px] font-bold">4 دفعات</span>
                            <span class="w-4 h-4 rounded-full border-2 border-slate-300 method-dot"></span>
                        </div>
                        <div class="font-black text-sm text-slate-900 flex items-center gap-1.5">
                            <span class="px-1.5 py-0.5 rounded bg-[#3EFEBA] text-slate-900 text-[10px] font-black">tabby</span>
                            تابي
                        </div>
                        <div class="text-xl font-black text-emerald-700 mt-1">{{ number_format($tabbyCalc['total'], 2) }} <span class="text-xs font-bold">ر.س</span></div>
                        <div class="text-[11px] text-emerald-800 font-bold mt-0.5">{{ number_format($tabbyInst, 2) }} ر.س / شهرياً · 4 دفعات بدون فوائد</div>
                    </div>

                    <!-- 4. Tamara -->
                    <div class="pay-card" id="p-method-tamara" onclick="pickProviderMethod('tamara', 'تمارا (4 دفعات)', '{{ number_format($tamaraCalc['total'], 2) }}')">
                        <div class="flex items-center justify-between mb-2">
                            <span class="px-2.5 py-0.5 rounded-full bg-orange-100 text-orange-900 text-[11px] font-bold">4 دفعات</span>
                            <span class="w-4 h-4 rounded-full border-2 border-slate-300 method-dot"></span>
                        </div>
                        <div class="font-black text-sm text-slate-900 flex items-center gap-1.5">
                            <span class="px-1.5 py-0.5 rounded bg-orange-200 text-orange-950 text-[10px] font-black">تمارا</span>
                            تمارا
                        </div>
                        <div class="text-xl font-black text-emerald-700 mt-1">{{ number_format($tamaraCalc['total'], 2) }} <span class="text-xs font-bold">ر.س</span></div>
                        <div class="text-[11px] text-orange-800 font-bold mt-0.5">{{ number_format($tamaraInst, 2) }} ر.س / شهرياً · 4 دفعات بدون فوائد</div>
                    </div>
                </div>

                <form method="POST" action="{{ route('provider-pay.select', $invoice->public_token) }}" id="p-pay-form">
                    @csrf
                    <input type="hidden" name="method" id="p-selected-method" value="standard">
                    <button type="submit" id="p-pay-submit"
                            class="w-full py-3.5 px-5 rounded-xl bg-[#141311] hover:bg-black text-[#e5cfa0] border border-[#c8a464]/40 font-black text-sm sm:text-base shadow-lg transition cursor-pointer">
                        💳 متابعة الدفع عبر <span id="p-selected-label">بطاقة مدى / فيزا / ماستركارد</span> (<span id="p-selected-amount">{{ number_format($visaCalc['total'], 2) }}</span> ر.س)
                    </button>
                </form>
            </div>
        @endif
    </div>

    <script>
        function pickProviderMethod(method, label, amount) {
            document.getElementById('p-selected-method').value = method;
            document.getElementById('p-selected-label').textContent = label;
            document.getElementById('p-selected-amount').textContent = amount;

            document.querySelectorAll('.pay-card').forEach(el => {
                el.classList.remove('selected');
                const dot = el.querySelector('.method-dot');
                if (dot) {
                    dot.classList.remove('border-indigo-600', 'bg-indigo-600');
                    dot.classList.add('border-slate-300');
                }
            });

            const active = document.getElementById('p-method-' + method);
            if (active) {
                active.classList.add('selected');
                const dot = active.querySelector('.method-dot');
                if (dot) {
                    dot.classList.remove('border-slate-300');
                    dot.classList.add('border-indigo-600', 'bg-indigo-600');
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const isApple = /iPhone|iPad|iPod|Macintosh/.test(navigator.userAgent) && !window.MSStream;
            if (isApple) {
                const badge = document.getElementById('p-apple-badge');
                if (badge) badge.textContent = '🍎 موصى به لجهازك';
                pickProviderMethod('apple_pay', 'Apple Pay', '{{ number_format($appleCalc['total'], 2) }}');
            }
        });
    </script>
</body>
</html>
