<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>نظام العقود | {{ $title ?? 'لوحة التحكم' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * { font-family: 'Cairo', sans-serif; }
        :root {
            --sidebar-width: 280px;
            --sidebar-collapsed: 0px;
        }
        body { background: #f8fafc; color: #1e293b; overflow-x: hidden; }

        /* ===== Sidebar ===== */
        .sidebar {
            width: var(--sidebar-width);
            background: #254a34; /* Dark green matching the logo */
            border-left: 1px solid rgba(255,255,255,0.06);
            position: fixed;
            top: 0;
            right: 0;
            height: 100vh;
            z-index: 40;
            transition: transform 0.3s cubic-bezier(0.4,0,0.2,1);
            overflow-y: auto;
        }
        .sidebar::-webkit-scrollbar { width: 4px; }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 4px; }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 20px;
            border-radius: 12px;
            margin: 2px 12px;
            color: #d1fae5;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        .sidebar-link:hover { background: rgba(255,255,255,0.1); color: #ffffff; }
        .sidebar-link.active {
            background: linear-gradient(135deg, rgba(255,255,255,0.15) 0%, rgba(255,255,255,0.05) 100%);
            color: #ffffff;
            border: 1px solid rgba(255,255,255,0.2);
        }
        .sidebar-link svg { width: 20px; height: 20px; flex-shrink: 0; }

        /* ===== Main Content ===== */
        .main-content {
            margin-right: var(--sidebar-width);
            min-height: 100vh;
            transition: margin 0.3s ease;
        }

        /* ===== Top Bar ===== */
        .topbar {
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid #e2e8f0;
            position: sticky;
            top: 0;
            z-index: 30;
        }

        /* ===== Cards ===== */
        .card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 24px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        }
        .card:hover {
            border-color: rgba(16,185,129,0.3);
            box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.1), 0 4px 6px -2px rgba(16, 185, 129, 0.05);
        }

        /* ===== Stats Cards ===== */
        .stat-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 24px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            opacity: 0.05;
            transform: translate(30%, -30%);
        }
        .stat-card:hover { transform: translateY(-4px); box-shadow: 0 12px 40px rgba(0,0,0,0.3); }
        .stat-card.indigo::before { background: #6366f1; }
        .stat-card.emerald::before { background: #10b981; }
        .stat-card.amber::before { background: #f59e0b; }
        .stat-card.rose::before { background: #f43f5e; }

        /* ===== Table ===== */
        .data-table { width: 100%; border-collapse: separate; border-spacing: 0; }
        .data-table thead th {
            background: #f1f5f9;
            padding: 14px 16px;
            font-size: 13px;
            font-weight: 700;
            color: #475569;
            text-align: right;
            border-bottom: 1px solid #e2e8f0;
        }
        .data-table thead th:first-child { border-radius: 0 12px 0 0; }
        .data-table thead th:last-child { border-radius: 12px 0 0 0; }
        .data-table tbody tr {
            transition: all 0.2s ease;
        }
        .data-table tbody tr:hover { background: #f8fafc; }
        .data-table tbody td {
            padding: 14px 16px;
            font-size: 14px;
            border-bottom: 1px solid #e2e8f0;
            color: #1e293b;
        }

        /* ===== Buttons ===== */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 14px;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
            text-decoration: none;
        }
        .btn-indigo {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(16,185,129,0.3);
        }
        .btn-indigo:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(16,185,129,0.4); }
        .btn-emerald {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(16,185,129,0.3);
        }
        .btn-emerald:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(16,185,129,0.4); }
        .btn-rose {
            background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(244,63,94,0.3);
        }
        .btn-rose:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(244,63,94,0.4); }
        .btn-ghost {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }
        .btn-ghost:hover { background: #e2e8f0; color: #1e293b; }
        .btn-sm { padding: 6px 14px; font-size: 13px; border-radius: 8px; }

        /* ===== Badges ===== */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
        }
        .badge-draft { background: rgba(148,163,184,0.15); color: #94a3b8; }
        .badge-sent { background: rgba(99,102,241,0.15); color: #a5b4fc; }
        .badge-viewed { background: rgba(245,158,11,0.15); color: #fbbf24; }
        .badge-lessee-signed { background: rgba(6,182,212,0.15); color: #22d3ee; }
        .badge-signed { background: rgba(16,185,129,0.15); color: #34d399; }
        .badge-rejected { background: rgba(244,63,94,0.15); color: #fb7185; }
        .badge-cancelled { background: rgba(107,114,128,0.15); color: #9ca3af; }

        /* ===== Form ===== */
        .form-input {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            color: #1e293b;
            padding: 12px 16px;
            width: 100%;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        .form-input:focus {
            outline: none;
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16,185,129,0.15);
        }
        .form-input::placeholder { color: #475569; }
        .form-label { display: block; font-size: 13px; font-weight: 700; color: #94a3b8; margin-bottom: 6px; }
        select.form-input { appearance: none; cursor: pointer; }
        textarea.form-input { resize: vertical; min-height: 80px; }

        /* ===== Alerts ===== */
        .alert {
            padding: 14px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            font-weight: 600;
            animation: slideDown 0.4s ease;
        }
        .alert-success { background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.2); color: #34d399; }
        .alert-error { background: rgba(244,63,94,0.1); border: 1px solid rgba(244,63,94,0.2); color: #fb7185; }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

        /* ===== Mobile ===== */
        .mobile-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 35; }
        @media (max-width: 1024px) {
            .sidebar { transform: translateX(100%); }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-right: 0; }
            .mobile-overlay.open { display: block; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Mobile Overlay -->
    <div id="mobile-overlay" class="mobile-overlay" onclick="toggleSidebar()"></div>

    <!-- Sidebar -->
    <aside id="sidebar" class="sidebar">
        <!-- Logo -->
        <div class="px-6 py-8 border-b border-slate-200 flex items-center justify-center">
            <img src="{{ asset('images/logo.png') }}" alt="توثيق العقود" class="max-w-[180px] w-full object-contain">
        </div>

        <!-- Nav Links -->
        <nav class="mt-6 px-2">
            <p class="px-5 text-xs font-bold text-slate-600 uppercase tracking-wider mb-3">القائمة الرئيسية</p>

            <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                لوحة التحكم
            </a>
            <a href="{{ route('clients.index') }}" class="sidebar-link {{ request()->routeIs('clients.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                العملاء
            </a>
            <a href="{{ route('contracts.index') }}" class="sidebar-link {{ request()->routeIs('contracts.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                العقود
            </a>

            @can('manageUsers', App\Models\Contract::class)
            <a href="{{ route('users.index') }}" class="sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                المديرين والموظفين
            </a>
            @endcan

            <p class="px-5 text-xs font-bold text-slate-600 uppercase tracking-wider mb-3 mt-8">روابط سريعة</p>

            <a href="{{ route('verify.index') }}" target="_blank" class="sidebar-link">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                صفحة التحقق العامة
            </a>
        </nav>

        <!-- User Info -->
        <div class="absolute bottom-0 right-0 left-0 p-4 border-t border-slate-200 bg-slate-50/50">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-emerald-500 flex items-center justify-center text-slate-800 font-bold text-sm">
                        {{ mb_substr(Auth::user()->name ?? 'م', 0, 1) }}
                    </div>
                    <div>
                        <p class="text-slate-800 text-sm font-semibold leading-tight">{{ Auth::user()->name ?? 'مستخدم' }}</p>
                        <p class="text-slate-600 text-xs">{{ Auth::user()->email ?? '' }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-slate-600 hover:text-red-400 transition" title="تسجيل الخروج">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <header class="topbar">
            <div class="flex items-center justify-between px-6 py-4">
                <div class="flex items-center gap-4">
                    <button onclick="toggleSidebar()" class="lg:hidden text-slate-600 hover:text-slate-800 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <div>
                        <h1 class="text-xl font-bold text-slate-800">{{ $header ?? 'لوحة التحكم' }}</h1>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-slate-600 hidden sm:inline">{{ now()->locale('ar')->translatedFormat('l j F Y') }}</span>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="p-6">
            @if(session('success'))
                <div class="alert alert-success">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-error">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ session('error') }}
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
            document.getElementById('mobile-overlay').classList.toggle('open');
        }
    </script>
    @stack('scripts')
</body>
</html>
