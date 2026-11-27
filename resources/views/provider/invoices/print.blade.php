<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>فاتورة {{ $invoice->invoice_number }} | {{ $provider->business_name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>
        * { font-family: 'Cairo', sans-serif; box-sizing: border-box; }
        body { background: #f1f5f9; margin: 0; padding: 20px 12px; }
        @media print {
            body { background: #ffffff; padding: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="max-w-3xl mx-auto">
        <div class="no-print flex items-center justify-between mb-4">
            <button onclick="window.print()" class="px-5 py-2.5 rounded-xl bg-slate-900 text-white text-sm font-extrabold cursor-pointer">
                🖨️ طباعة / حفظ PDF
            </button>
            <a href="{{ url()->previous() }}" class="px-4 py-2 rounded-xl bg-white border border-slate-300 text-slate-700 text-xs font-bold">
                رجوع
            </a>
        </div>

        @include($invoice->templateView(), ['invoice' => $invoice, 'provider' => $provider])
    </div>
</body>
</html>
