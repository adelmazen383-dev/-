<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'بوابة مقدمي الخدمات' }} | منصة الفواتير المستقلة</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * { font-family: 'Cairo', sans-serif; box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            background: radial-gradient(circle at top right, #1e1b4b 0%, #0f172a 48%, #020617 100%);
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
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.14);
        }
        .p-btn-primary {
            width: 100%;
            background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
            color: #ffffff;
            border: none;
            border-radius: 12px;
            padding: 12px 18px;
            font-size: 14.5px;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 6px 18px rgba(79, 70, 229, 0.35);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .p-btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 24px rgba(79, 70, 229, 0.45);
        }
    </style>
    @stack('styles')
</head>
<body class="flex flex-col items-center justify-center p-4 sm:p-6">
    <div class="w-full {{ $maxWidth ?? 'max-w-md' }} my-4">
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-indigo-500/20 border border-indigo-400/30 text-white text-2xl shadow-lg mb-3">
                🧾
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight">بوابة مقدمي الخدمات</h1>
            <p class="text-xs sm:text-sm text-slate-300 mt-1">نظام مستقل لإصدار الفواتير وتحصيل المدفوعات وإدارة المحفظة</p>
        </div>

        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200/80 p-5 sm:p-7">
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

        <p class="text-center text-xs text-slate-400 mt-5">
            جميع الحقوق محفوظة &copy; {{ date('Y') }} — بوابة مقدمي الخدمات
        </p>
    </div>

    @stack('scripts')
</body>
</html>
