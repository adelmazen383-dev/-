<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>منصة توثيق العقود | {{ $title ?? 'لوحة التحكم' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * { font-family: 'Cairo', sans-serif; box-sizing: border-box; }
        :root {
            --sidebar-width: 250px;
        }
        body {
            background: #f8fafc;
            color: #1e293b;
            overflow-x: hidden;
            font-size: 13.5px;
            line-height: 1.5;
        }

        /* ===== Sidebar ===== */
        .sidebar {
            width: var(--sidebar-width);
            background: #064e3b; /* Dark emerald */
            border-left: 1px solid rgba(16, 185, 129, 0.15);
            position: fixed;
            top: 0;
            right: 0;
            height: 100vh;
            z-index: 40;
            transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }
        .sidebar::-webkit-scrollbar { width: 3px; }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 3px; }

        .sidebar-brand {
            padding: 16px 14px;
            border-bottom: 1px solid rgba(16, 185, 129, 0.2);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .sidebar-brand-logo {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: #ffffff;
            padding: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
            flex-shrink: 0;
        }
        .sidebar-brand-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .sidebar-brand-text {
            overflow: hidden;
        }
        .sidebar-brand-title {
            color: #ffffff;
            font-size: 13.5px;
            font-weight: 800;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .sidebar-brand-sub {
            color: #a7f3d0;
            font-size: 11px;
            font-weight: 600;
            display: block;
        }

        .sidebar-nav {
            padding: 12px 6px;
            flex: 1;
            overflow-y: auto;
        }
        .sidebar-nav-title {
            padding: 0 10px;
            font-size: 11px;
            font-weight: 800;
            color: #6ee7b7;
            opacity: 0.8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 12px 0 6px;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            border-radius: 8px;
            margin: 2px 4px;
            color: #d1fae5;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.15s ease;
            text-decoration: none;
        }
        .sidebar-link:hover {
            background: rgba(255, 255, 255, 0.08);
            color: #ffffff;
        }
        .sidebar-link.active {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.3) 0%, rgba(16, 185, 129, 0.15) 100%);
            color: #ffffff;
            font-weight: 700;
            border: 1px solid rgba(16, 185, 129, 0.35);
        }
        .sidebar-link svg {
            width: 17px;
            height: 17px;
            flex-shrink: 0;
            opacity: 0.9;
        }

        .sidebar-user {
            padding: 10px 12px;
            border-top: 1px solid rgba(16, 185, 129, 0.2);
            background: rgba(2, 44, 34, 0.4);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        /* ===== Main Content ===== */
        .main-content {
            margin-right: var(--sidebar-width);
            min-height: 100vh;
            transition: margin 0.25s ease;
            display: flex;
            flex-direction: column;
        }

        /* ===== Top Bar ===== */
        .topbar {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            position: sticky;
            top: 0;
            z-index: 30;
            box-shadow: 0 1px 2px rgba(0,0,0,0.03);
        }
        .topbar-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 20px;
        }

        /* ===== Cards (Clean, sleek, balanced - NO oversized borders) ===== */
        .card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 18px 20px;
            transition: all 0.2s ease;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.03);
        }
        .card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 3px 8px -1px rgba(0, 0, 0, 0.05);
        }

        /* ===== Stats Cards ===== */
        .stat-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px 18px;
            position: relative;
            overflow: hidden;
            transition: all 0.2s ease;
            box-shadow: 0 1px 2px rgba(0,0,0,0.03);
        }
        .stat-card:hover {
            transform: translateY(-2px);
            border-color: #cbd5e1;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        }

        /* ===== Table ===== */
        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        .data-table thead th {
            background: #f8fafc;
            padding: 9px 12px;
            font-size: 12px;
            font-weight: 700;
            color: #475569;
            text-align: right;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
        }
        .data-table thead th:first-child { border-radius: 0 8px 0 0; }
        .data-table thead th:last-child { border-radius: 8px 0 0 0; }
        .data-table tbody tr {
            transition: background 0.15s ease;
        }
        .data-table tbody tr:hover { background: #f8fafc; }
        .data-table tbody td {
            padding: 10px 12px;
            font-size: 13px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            vertical-align: middle;
        }

        /* ===== Buttons ===== */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 7px 14px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 13px;
            transition: all 0.15s ease;
            cursor: pointer;
            border: none;
            text-decoration: none;
            line-height: 1.4;
        }
        .btn-indigo {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(16, 185, 129, 0.25);
        }
        .btn-indigo:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.35);
            color: #ffffff;
        }
        .btn-emerald {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #ffffff;
        }
        .btn-emerald:hover { transform: translateY(-1px); color: #ffffff; }
        .btn-rose {
            background: #ef4444;
            color: #ffffff;
        }
        .btn-rose:hover { background: #dc2626; color: #ffffff; }
        .btn-ghost {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }
        .btn-ghost:hover { background: #e2e8f0; color: #1e293b; }
        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
            border-radius: 6px;
            gap: 4px;
        }

        /* ===== Badges ===== */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 9px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 700;
            line-height: 1.3;
        }
        .badge-draft { background: rgba(148,163,184,0.15); color: #64748b; }
        .badge-sent { background: rgba(99,102,241,0.12); color: #6366f1; }
        .badge-viewed { background: rgba(245,158,11,0.12); color: #d97706; }
        .badge-lessee-signed { background: rgba(6,182,212,0.12); color: #0891b2; }
        .badge-signed { background: rgba(16,185,129,0.12); color: #059669; }
        .badge-rejected { background: rgba(244,63,94,0.12); color: #e11d48; }
        .badge-cancelled { background: rgba(107,114,128,0.12); color: #6b7280; }

        /* ===== Forms ===== */
        .form-input {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            color: #1e293b;
            padding: 8px 12px;
            width: 100%;
            font-size: 13px;
            transition: all 0.15s ease;
        }
        .form-input:focus {
            outline: none;
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16,185,129,0.12);
        }
        .form-input::placeholder { color: #94a3b8; }
        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            color: #475569;
            margin-bottom: 4px;
        }
        select.form-input { appearance: none; cursor: pointer; }
        textarea.form-input { resize: vertical; min-height: 70px; }

        /* ===== Alerts ===== */
        .alert {
            padding: 10px 16px;
            border-radius: 10px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            font-weight: 600;
        }
        .alert-success { background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; }
        .alert-error { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }

        /* ===== Mobile ===== */
        .mobile-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 35; }
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
        <!-- Logo & Brand Header: Compact, Small Logo, Clean -->
        <div class="sidebar-brand">
            <div class="sidebar-brand-logo">
                <img src="{{ asset('images/image.png') }}" alt="توثيق العقود">
            </div>
            <div class="sidebar-brand-text">
                <h2 class="sidebar-brand-title">توثيق العقود</h2>
                <span class="sidebar-brand-sub">لوحة التحكم</span>
            </div>
        </div>

        <!-- Nav Links / Sections (وتحته الأجزاء) -->
        <nav class="sidebar-nav">
            <p class="sidebar-nav-title">القائمة الرئيسية</p>

            @if(Auth::user()->hasRole('admin'))
            <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                لوحة التحكم
            </a>
            @endif

            <a href="{{ route('clients.index') }}" class="sidebar-link {{ request()->routeIs('clients.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                العملاء
            </a>

            <a href="{{ route('contracts.index') }}" class="sidebar-link {{ request()->routeIs('contracts.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                العقود
            </a>

            <a href="{{ route('invoices.index') }}" class="sidebar-link {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                الفواتير (Telr)
            </a>

            @can('manageUsers', App\Models\Contract::class)
            <a href="{{ route('users.index') }}" class="sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                المديرين والموظفين
            </a>
            @endcan

            <p class="sidebar-nav-title">روابط سريعة</p>

            <a href="{{ route('verify.index') }}" target="_blank" class="sidebar-link">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                صفحة التحقق العامة
            </a>
        </nav>

        <!-- User Info -->
        <div class="sidebar-user">
            <div class="flex items-center gap-2.5 overflow-hidden">
                <div class="w-8 h-8 rounded-full bg-emerald-400 flex items-center justify-center text-emerald-950 font-bold text-xs shrink-0">
                    {{ mb_substr(Auth::user()->name ?? 'م', 0, 1) }}
                </div>
                <div class="overflow-hidden">
                    <p class="text-white text-xs font-bold leading-tight truncate">{{ Auth::user()->name ?? 'مستخدم' }}</p>
                    <p class="text-emerald-300/70 text-[11px] truncate">{{ Auth::user()->email ?? '' }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                @csrf
                <button type="submit" class="text-emerald-300/70 hover:text-red-400 transition p-1" title="تسجيل الخروج">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <header class="topbar">
            <div class="topbar-content">
                <div class="flex items-center gap-3">
                    <button onclick="toggleSidebar()" class="lg:hidden text-slate-600 hover:text-slate-800 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <div>
                        <h1 class="text-base sm:text-lg font-bold text-slate-800">{{ $header ?? 'لوحة التحكم' }}</h1>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-500 font-medium hidden sm:inline">{{ now()->locale('ar')->translatedFormat('l j F Y') }}</span>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="p-4 sm:p-6 flex-1">
            @if(session('success'))
                <div class="alert alert-success">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-error">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('error') }}</span>
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
