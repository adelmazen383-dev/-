<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
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
            background: linear-gradient(180deg, #064e3b 0%, #022c22 100%);
            border-left: 1px solid rgba(16, 185, 129, 0.2);
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
            color: #d1fae5;
            font-weight: 700;
            font-size: 13.5px;
            text-decoration: none;
            transition: all 0.15s ease;
        }
        .p-nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
        }
        .p-nav-link.active {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
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
            border-color: #059669;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.16);
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
        <div class="p-4 border-b border-emerald-400/20 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-white border border-emerald-400/30 flex items-center justify-center p-1 shrink-0 shadow">
                <img src="{{ asset('images/image.png') }}" alt="منصة ثقة" class="w-full h-full object-contain">
            </div>
            <div class="overflow-hidden">
                <div class="text-white font-extrabold text-sm truncate">{{ $authProvider?->business_name ?? 'مقدم خدمة' }}</div>
                <div class="text-emerald-300 text-[11px] font-semibold truncate">منصة ثقة · {{ $authProvider?->service_type_label }}</div>
            </div>
        </div>

        <nav class="flex-1 py-3 overflow-y-auto">
            <div class="px-4 text-[10.5px] font-bold text-emerald-300/80 mb-2">لوحة مقدم الخدمة</div>

            <a href="{{ route('provider.dashboard') }}" class="p-nav-link {{ request()->routeIs('provider.dashboard') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                الرئيسية
            </a>

            <a href="{{ route('provider.invoices.index') }}" class="p-nav-link {{ request()->routeIs('provider.invoices.index', 'provider.invoices.show') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                فواتيري
            </a>

            @if($isSubscribed)
                <a href="{{ route('provider.invoices.create') }}" class="p-nav-link {{ request()->routeIs('provider.invoices.create') ? 'active' : '' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    إنشاء فاتورة جديدة
                </a>
            @else
                <a href="{{ route('provider.subscription') }}" class="p-nav-link opacity-75" title="يتطلب اشتراكاً شهرياً نشطاً">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    إنشاء فاتورة (يتطلب اشتراك)
                </a>
            @endif

            <a href="{{ route('provider.subscription') }}" class="p-nav-link {{ request()->routeIs('provider.subscription') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                الاشتراك والتواصل
            </a>

            <a href="{{ route('provider.wallet') }}" class="p-nav-link {{ request()->routeIs('provider.wallet') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                محفظتي والتحويلات
            </a>

            <a href="{{ route('provider.profile') }}" class="p-nav-link {{ request()->routeIs('provider.profile') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                حسابي والاشتراك
            </a>
        </nav>

        <div class="p-3 border-t border-white/10 bg-black/20 space-y-2.5">
            <div class="overflow-hidden">
                <div class="text-white text-xs font-bold truncate">{{ $authProvider?->name }}</div>
                <div class="text-slate-400 text-[10.5px] truncate" dir="ltr">{{ $authProvider?->email }}</div>
            </div>
            <form method="POST" action="{{ route('provider.logout') }}">
                @csrf
                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg bg-rose-500/20 text-rose-200 hover:bg-rose-600 hover:text-white text-xs font-extrabold transition">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    تسجيل الخروج
                </button>
            </form>
        </div>
    </aside>

    <div class="p-main">
        <header class="sticky top-0 z-20 bg-white/95 backdrop-blur border-b border-slate-200 px-4 py-3 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <button type="button" onclick="toggleProviderSidebar()" aria-label="القائمة" class="lg:hidden p-2 rounded-lg bg-slate-100 text-slate-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div class="min-w-0">
                    <h1 class="text-base sm:text-lg font-black text-slate-900 truncate">{{ $header ?? 'لوحة مقدم الخدمة' }}</h1>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                @if($isSubscribed)
                    <span class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-bold">
                        اشتراك نشط ({{ $authProvider->subscriptionDaysRemaining() }} يوم)
                    </span>
                @else
                    <a href="{{ route('provider.subscription') }}" class="hidden sm:inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-amber-50 border border-amber-200 text-amber-800 hover:bg-amber-100 text-[11px] font-bold">
                        {{ $authProvider?->subscriptionStatusLabel() }}
                    </a>
                @endif
                <a href="{{ route('provider.wallet') }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-indigo-50 border border-indigo-200 text-indigo-800 text-xs font-extrabold">
                    {{ number_format((float) ($authProvider?->wallet_balance ?? 0), 2) }} ر.س
                </a>
                <form method="POST" action="{{ route('provider.logout') }}" class="inline-flex">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-600 text-rose-700 hover:text-white border border-rose-200 text-xs font-extrabold transition"
                            title="تسجيل الخروج">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span class="hidden sm:inline">تسجيل الخروج</span>
                        <span class="sm:hidden">خروج</span>
                    </button>
                </form>
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
        <a href="{{ route('provider.dashboard') }}" class="flex flex-col items-center justify-center py-1 text-[10.5px] font-bold {{ request()->routeIs('provider.dashboard') ? 'text-emerald-700' : 'text-slate-500' }}">
            <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            الرئيسية
        </a>
        <a href="{{ route('provider.invoices.index') }}" class="flex flex-col items-center justify-center py-1 text-[10.5px] font-bold {{ request()->routeIs('provider.invoices.index', 'provider.invoices.show') ? 'text-emerald-700' : 'text-slate-500' }}">
            <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            فواتيري
        </a>
        <a href="{{ $isSubscribed ? route('provider.invoices.create') : route('provider.subscription') }}" class="flex flex-col items-center justify-center py-1 text-[10.5px] font-extrabold {{ $isSubscribed ? 'text-emerald-700' : 'text-amber-700' }}">
            <span class="w-9 h-9 -mt-4 rounded-full {{ $isSubscribed ? 'bg-emerald-600 text-white shadow-lg' : 'bg-slate-300 text-slate-600' }} flex items-center justify-center">
                @if($isSubscribed)
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                @else
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                @endif
            </span>
            جديدة
        </a>
        <a href="{{ route('provider.wallet') }}" class="flex flex-col items-center justify-center py-1 text-[10.5px] font-bold {{ request()->routeIs('provider.wallet') ? 'text-emerald-700' : 'text-slate-500' }}">
            <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            المحفظة
        </a>
        <a href="{{ route('provider.profile') }}" class="flex flex-col items-center justify-center py-1 text-[10.5px] font-bold {{ request()->routeIs('provider.profile') ? 'text-emerald-700' : 'text-slate-500' }}">
            <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
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
