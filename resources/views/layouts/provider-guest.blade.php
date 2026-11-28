<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#064e3b">
    <title>{{ $title ?? 'بوابة مقدمي الخدمات' }} | منصة ثقة</title>
    <meta name="description" content="{{ $metaDescription ?? 'بوابة مقدمي الخدمات في منصة ثقة (thiqa.me) — سجّل الآن لإصدار فواتير إلكترونية وتحصيل مدفوعات عملائك عبر Apple Pay ومدى وفيزا وتابي وتمارا في السعودية.' }}">
    <meta name="keywords" content="منصة ثقة, بوابة مقدمي الخدمات, تسجيل مقدم خدمة, فواتير إلكترونية, تابي, تمارا, Apple Pay, مدى, توثيق العقود">
    <meta name="robots" content="{{ $metaRobots ?? 'index, follow, max-image-preview:large' }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="alternate" hreflang="ar-SA" href="{{ url()->current() }}">
    <link rel="alternate" hreflang="x-default" href="{{ url()->current() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/image.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/image.png') }}">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="ar_SA">
    <meta property="og:site_name" content="منصة ثقة">
    <meta property="og:title" content="{{ $title ?? 'بوابة مقدمي الخدمات' }} | منصة ثقة">
    <meta property="og:description" content="{{ $metaDescription ?? 'نظام متكامل لمقدمي الخدمات في السعودية لإصدار الفواتير الإلكترونية وتحصيل المدفوعات وإدارة المحفظة ضمن منصة ثقة.' }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('images/og-thiqa.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title ?? 'بوابة مقدمي الخدمات' }} | منصة ثقة">
    <meta name="twitter:image" content="{{ asset('images/og-thiqa.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * { font-family: 'Cairo', sans-serif; box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            background: #064e3b;
            background-image:
                radial-gradient(circle at 50% 0%, #065f46 0%, transparent 60%),
                radial-gradient(circle at 10% 90%, #047857 0%, transparent 50%),
                radial-gradient(circle at 90% 80%, #022c22 0%, transparent 60%);
            color: #0f172a;
            overflow-x: hidden;
        }
        .p-input {
            width: 100%;
            background: #ffffff;
            border: 1.5px solid #cbd5e1;
            border-radius: 11px;
            padding: 11px 14px;
            font-size: 14px;
            color: #0f172a;
            transition: all 0.15s ease;
        }
        .p-input:focus {
            outline: none;
            border-color: #059669;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.16);
        }
        .p-btn-primary {
            width: 100%;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #ffffff;
            border: none;
            border-radius: 12px;
            padding: 12px 18px;
            font-size: 14.5px;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 6px 18px rgba(16, 185, 129, 0.35);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .p-btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 24px rgba(16, 185, 129, 0.45);
            background: linear-gradient(135deg, #34d399 0%, #059669 100%);
        }
    </style>
    @stack('styles')
</head>
<body class="flex flex-col items-center justify-center p-4 sm:p-6">
    <div class="w-full {{ $maxWidth ?? 'max-w-md' }} my-4">
        <!-- Top Navigation Strip Matching Core Site -->
        <div class="flex items-center justify-between gap-2 mb-5 px-1">
            <a href="{{ route('home') }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-emerald-950/60 border border-emerald-400/25 text-emerald-100 hover:bg-emerald-800/50 hover:text-white text-xs font-bold transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                الموقع الرئيسي
            </a>
            <a href="{{ route('login') }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-emerald-500/20 border border-emerald-400/35 text-emerald-100 hover:bg-emerald-500 hover:text-white text-xs font-extrabold transition">
                تسجيل الدخول
            </a>
        </div>

        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-white border border-emerald-400/30 shadow-lg mb-3 p-2">
                <img src="{{ asset('images/image.png') }}" alt="شعار منصة ثقة" class="w-full h-full object-contain">
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight">منصة ثقة — بوابة مقدمي الخدمات</h1>
            <p class="text-xs sm:text-sm text-emerald-200/85 mt-1">نظام متكامل لإصدار الفواتير الإلكترونية وتحصيل المدفوعات وإدارة المحفظة</p>
        </div>

        <div class="bg-white rounded-2xl shadow-2xl border border-emerald-200/40 p-5 sm:p-7">
            @if(session('success'))
                <div class="mb-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-xs sm:text-sm font-bold">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 text-xs sm:text-sm font-bold">
                    {{ session('error') }}
                </div>
            @endif
            @if(session('warning'))
                <div class="mb-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 text-xs sm:text-sm font-bold">
                    {{ session('warning') }}
                </div>
            @endif

            @yield('content')
        </div>

        <p class="text-center text-xs text-emerald-200/60 mt-5">
            جميع الحقوق محفوظة &copy; {{ date('Y') }} منصة ثقة — مؤسسة موقع أمر التجارية
        </p>
    </div>

    @stack('scripts')
</body>
</html>
