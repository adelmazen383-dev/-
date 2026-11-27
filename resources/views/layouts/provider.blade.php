<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'لوحة مقدم الخدمة' }} | بوابة مقدمي الخدمات</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * { font-family: 'Cairo', sans-serif; box-sizing: border-box; }
        body {
            background: #f1f5f9;
            color: #0f172a;
            overflow-x: hidden;
            font-size: 13.5px;
        }
        .p-sidebar {
            width: 256px;
            background: linear-gradient(180deg, #0f172a 0%, #1e1b4b 100%);
            position: fixed;
            top: 0;
            right: 0;
            height: 100vh;
            z-index: 40;
            display: flex;
            flex-direction: column;
            transition: transform 0.25s ease;
        }
        .p-nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            margin: 3px 10px;
            border-radius: 10px;
            color: #cbd5e1;
            font-weight: 700;
            font-size: 13.5px;
            text-decoration: none;
            transition: all 0.15s ease;
        }
        .p-nav-link:hover {
            background: rgba(255, 255, 255, 0.08);
            color: #ffffff;
        }
        .p-nav-link.active {
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(79, 70, 229, 0.4);
        }
        .p-main {
            margin-right: 256px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .p-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 18px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        }
        .p-input {
            width: 100%;
            background: #ffffff;
            border: 1.5px solid #cbd5e1;
            border-radius: 10px;
            padding: 9px 12px;
            font-size: 13.5px;
            color: #0f172a;
        }
        .p-input:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
        }
        .p-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
            z-index: 35;
        }
        .p-bottom-nav {
            display: none;
        }
        @media (max-width: 1024px) {
            .p-sidebar { transform: translateX(100%); }
            .p-sidebar.open { transform: translateX(0); }
            .p-overlay.open { display: block; }
            .p-main { margin-right: 0; padding-bottom: 76px; }
            .p-bottom-nav {
                display: grid;
                grid-template-columns: repeat(5, 1fr);
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                background: #ffffff;
                border-top: 1px solid #e2e8f0;
                z-index: 30;
                padding: 6px 4px calc(6px + env(safe-area-inset-bottom));
                box-shadow: 0 -4px 16px rgba(15, 23, 42, 0.06);
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    @php
        $authProvider = Auth::guard('provider')->user();
        $isSubscribed = $authProvider && $authProvider->hasActiveSubscription();
    @endphp

    <div id="p-overlay" class="p-overlay" onclick="toggleProviderSidebar()"></div>

    <aside id="p-sidebar" class="p-sidebar">
        <div class="p-4 border-b border-white/10 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-[#141311] border border-[#c8a464]/40 flex items-center justify-center p-1.5 shrink-0">
                <img src="{{ asset('images/thiqa-gold-logo.png') }}" alt="منصة ثقة" class="w-full h-full object-contain">
            </div>
            <div class="overflow-hidden">
                <div class="text-white font-extrabold text-sm truncate">{{ $authProvider?->business_name ?? 'مقدم خدمة' }}</div>
                <div class="text-amber-300 text-[11px] font-semibold truncate">منصة ثقة · {{ $authProvider?->service_type_label }}</div>
            </div>
        </div>

        <nav class="flex-1 py-3 overflow-y-auto">
            <div class="px-4 text-[10.5px] font-bold text-indigo-300/80 mb-2">لوحة مقدم الخدمة</div>

            <a href="{{ route('provider.dashboard') }}" class="p-nav-link {{ request()->routeIs('provider.dashboard') ? 'active' : '' }}">
                <span>🏠</span> الرئيسية
            </a>

            <a href="{{ route('provider.invoices.index') }}" class="p-nav-link {{ request()->routeIs('provider.invoices.index', 'provider.invoices.show') ? 'active' : '' }}">
                <span>📄</span> فواتيري
            </a>

            @if($isSubscribed)
                <a href="{{ route('provider.invoices.create') }}" class="p-nav-link {{ request()->routeIs('provider.invoices.create') ? 'active' : '' }}">
                    <span>➕</span> إنشاء فاتورة جديدة
                </a>
            @else
                <a href="{{ route('provider.dashboard') }}" class="p-nav-link opacity-60" title="يتطلب اشتراكاً شهرياً نشطاً">
                    <span>🔒</span> إنشاء فاتورة (غير مشترك)
                </a>
            @endif

            <a href="{{ route('provider.wallet') }}" class="p-nav-link {{ request()->routeIs('provider.wallet') ? 'active' : '' }}">
                <span>💳</span> محفظتي والتحويلات
            </a>

            <a href="{{ route('provider.profile') }}" class="p-nav-link {{ request()->routeIs('provider.profile') ? 'active' : '' }}">
                <span>⚙️</span> حسابي والاشتراك
            </a>
        </nav>

        <div class="p-3 border-t border-white/10 bg-black/20">
            <div class="flex items-center justify-between gap-2">
                <div class="overflow-hidden">
                    <div class="text-white text-xs font-bold truncate">{{ $authProvider?->name }}</div>
                    <div class="text-slate-400 text-[10.5px] truncate">{{ $authProvider?->email }}</div>
                </div>
                <form method="POST" action="{{ route('provider.logout') }}">
                    @csrf
                    <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-rose-500/20 text-rose-200 hover:bg-rose-500 hover:text-white text-xs font-bold transition">
                        خروج
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <div class="p-main">
        <header class="sticky top-0 z-20 bg-white/95 backdrop-blur border-b border-slate-200 px-4 py-3 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <button type="button" onclick="toggleProviderSidebar()" class="lg:hidden p-2 rounded-lg bg-slate-100 text-slate-700">
                    ☰
                </button>
                <div class="min-w-0">
                    <h1 class="text-base sm:text-lg font-black text-slate-900 truncate">{{ $header ?? 'لوحة مقدم الخدمة' }}</h1>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                @if($isSubscribed)
                    <span class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-bold">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        اشتراك نشط ({{ $authProvider->subscriptionDaysRemaining() }} يوم)
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-amber-50 border border-amber-200 text-amber-800 text-[11px] font-bold">
                        ⏳ {{ $authProvider?->subscriptionStatusLabel() }}
                    </span>
                @endif
                <a href="{{ route('provider.wallet') }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-indigo-50 border border-indigo-200 text-indigo-800 text-xs font-extrabold">
                    💰 {{ number_format((float) ($authProvider?->wallet_balance ?? 0), 2) }} ر.س
                </a>
            </div>
        </header>

        <main class="p-4 sm:p-6 flex-1 max-w-6xl w-full mx-auto">
            @if(session('success'))
                <div class="mb-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm font-bold">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 text-sm font-bold">
                    {{ session('error') }}
                </div>
            @endif
            @if(session('warning'))
                <div class="mb-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 text-sm font-bold">
                    {{ session('warning') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Mobile Bottom Tab Bar -->
    <nav class="p-bottom-nav">
        <a href="{{ route('provider.dashboard') }}" class="flex flex-col items-center justify-center py-1 text-[10.5px] font-bold {{ request()->routeIs('provider.dashboard') ? 'text-indigo-600' : 'text-slate-500' }}">
            <span class="text-lg leading-none mb-0.5">🏠</span>
            الرئيسية
        </a>
        <a href="{{ route('provider.invoices.index') }}" class="flex flex-col items-center justify-center py-1 text-[10.5px] font-bold {{ request()->routeIs('provider.invoices.index', 'provider.invoices.show') ? 'text-indigo-600' : 'text-slate-500' }}">
            <span class="text-lg leading-none mb-0.5">📄</span>
            فواتيري
        </a>
        <a href="{{ $isSubscribed ? route('provider.invoices.create') : route('provider.dashboard') }}" class="flex flex-col items-center justify-center py-1 text-[10.5px] font-extrabold {{ $isSubscribed ? 'text-indigo-700' : 'text-slate-400' }}">
            <span class="w-9 h-9 -mt-4 rounded-full {{ $isSubscribed ? 'bg-indigo-600 text-white shadow-lg' : 'bg-slate-300 text-slate-600' }} flex items-center justify-center text-lg">
                {{ $isSubscribed ? '+' : '🔒' }}
            </span>
            جديدة
        </a>
        <a href="{{ route('provider.wallet') }}" class="flex flex-col items-center justify-center py-1 text-[10.5px] font-bold {{ request()->routeIs('provider.wallet') ? 'text-indigo-600' : 'text-slate-500' }}">
            <span class="text-lg leading-none mb-0.5">💰</span>
            المحفظة
        </a>
        <a href="{{ route('provider.profile') }}" class="flex flex-col items-center justify-center py-1 text-[10.5px] font-bold {{ request()->routeIs('provider.profile') ? 'text-indigo-600' : 'text-slate-500' }}">
            <span class="text-lg leading-none mb-0.5">⚙️</span>
            حسابي
        </a>
    </nav>

    <script>
        function toggleProviderSidebar() {
            document.getElementById('p-sidebar').classList.toggle('open');
            document.getElementById('p-overlay').classList.toggle('open');
        }
    </script>
    @stack('scripts')
</body>
</html>
