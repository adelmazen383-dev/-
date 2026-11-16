<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, viewport-fit=cover">
    <meta name="theme-color" content="#064e3b">
    <title>ثقة | منصة توثيق العقود وإدارة الحجوزات</title>
    <meta name="description" content="منصة ثقة لتوثيق عقود الإيجار وإدارة حجوزات الوحدات السكنية والفندقية - حلول دفع إلكتروني آمنة">
    <meta name="keywords" content="توثيق عقود, إدارة حجوزات, وحدات سكنية, فندقية, دفع إلكتروني, Apple Pay, Visa, Mada, تابي, تمارا">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Cairo', sans-serif; -webkit-tap-highlight-color: transparent; }

        html { scroll-behavior: smooth; }

        body {
            background: #064e3b;
            background-image: 
                radial-gradient(circle at 50% 10%, #065f46 0%, transparent 60%),
                radial-gradient(circle at 90% 40%, #047857 0%, transparent 50%),
                radial-gradient(circle at 10% 85%, #022c22 0%, transparent 70%);
            min-height: 100vh;
            color: #f8fafc;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
        }

        /* ─── Sticky Header ─── */
        .header-wrapper {
            position: sticky;
            top: 0;
            z-index: 50;
            background: rgba(6, 78, 59, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(52, 211, 153, 0.2);
            width: 100%;
        }

        .navbar {
            width: 100%;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            max-width: 1200px;
            margin: 0 auto;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: #f8fafc;
        }

        .navbar-brand-logo {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: #ffffff;
            padding: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.25);
            flex-shrink: 0;
        }

        .navbar-brand-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .navbar-brand-text {
            display: flex;
            flex-direction: column;
        }

        .navbar-brand-text span.brand-name {
            font-size: 20px;
            font-weight: 900;
            color: #34d399;
            line-height: 1.1;
        }

        .navbar-brand-text span.brand-sub {
            font-size: 11px;
            color: #a7f3d0;
            font-weight: 600;
        }

        /* Desktop Nav Links */
        .navbar-links {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .navbar-links a.nav-item {
            color: rgba(248, 250, 252, 0.8);
            text-decoration: none;
            font-size: 14.5px;
            font-weight: 600;
            transition: color 0.2s;
            padding: 6px 4px;
        }

        .navbar-links a.nav-item:hover { color: #34d399; }

        /* Login Buttons */
        .header-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-nav-login {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #ffffff !important;
            padding: 8px 18px;
            border-radius: 10px;
            font-weight: 800;
            font-size: 14px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 3px 12px rgba(16, 185, 129, 0.35);
            transition: all 0.25s ease;
            white-space: nowrap;
            border: 1px solid rgba(52, 211, 153, 0.3);
        }

        .btn-nav-login:hover {
            transform: translateY(-1.5px);
            box-shadow: 0 6px 18px rgba(16, 185, 129, 0.45);
            background: linear-gradient(135deg, #34d399 0%, #059669 100%);
        }

        .btn-nav-login svg {
            width: 17px;
            height: 17px;
        }

        /* Mobile Hamburger Toggle */
        .mobile-toggle {
            display: none;
            background: rgba(6, 78, 59, 0.6);
            border: 1px solid rgba(52, 211, 153, 0.3);
            color: #d1fae5;
            padding: 8px;
            border-radius: 10px;
            cursor: pointer;
            align-items: center;
            justify-content: center;
        }

        .mobile-toggle svg { width: 22px; height: 22px; }

        /* Mobile Dropdown Menu */
        .mobile-menu {
            display: none;
            background: rgba(2, 44, 34, 0.98);
            border-bottom: 1px solid rgba(52, 211, 153, 0.25);
            padding: 16px 20px;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }

        .mobile-menu.active {
            display: block;
            animation: slideDown 0.25s ease-out;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .mobile-menu-links {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .mobile-menu-links a {
            color: #d1fae5;
            text-decoration: none;
            font-size: 15px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.04);
        }

        .mobile-menu-links a:hover {
            background: rgba(16, 185, 129, 0.15);
            color: #ffffff;
        }

        .mobile-menu-actions {
            margin-top: 14px;
            padding-top: 14px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* ─── Hero ─── */
        .hero {
            text-align: center;
            padding: 50px 20px 40px;
            max-width: 960px;
            margin: 0 auto;
            position: relative;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(52, 211, 153, 0.35);
            padding: 6px 16px;
            border-radius: 9999px;
            font-size: 13px;
            font-weight: 700;
            color: #6ee7b7;
            margin-bottom: 20px;
        }

        .hero h1 {
            font-size: clamp(26px, 5.5vw, 44px);
            font-weight: 900;
            line-height: 1.35;
            margin-bottom: 18px;
            background: linear-gradient(135deg, #ffffff 0%, #a7f3d0 50%, #34d399 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero p {
            font-size: clamp(14.5px, 2.5vw, 18px);
            color: rgba(248, 250, 252, 0.8);
            line-height: 1.8;
            max-width: 720px;
            margin: 0 auto 30px;
        }

        /* Prominent Hero CTA Buttons */
        .hero-cta-group {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
            flex-wrap: wrap;
            margin-bottom: 34px;
        }

        .btn-cta-primary {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #ffffff;
            padding: 13px 28px;
            border-radius: 12px;
            font-weight: 800;
            font-size: 16px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 8px 24px rgba(16, 185, 129, 0.35);
            border: 1px solid rgba(52, 211, 153, 0.35);
            transition: all 0.25s ease;
        }

        .btn-cta-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(16, 185, 129, 0.5);
            background: linear-gradient(135deg, #34d399 0%, #059669 100%);
        }

        .btn-cta-secondary {
            background: rgba(6, 78, 59, 0.7);
            color: #d1fae5;
            border: 1.5px solid rgba(52, 211, 153, 0.35);
            padding: 13px 24px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 15px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.25s ease;
            backdrop-filter: blur(8px);
        }

        .btn-cta-secondary:hover {
            background: rgba(16, 185, 129, 0.2);
            color: #ffffff;
            border-color: #34d399;
            transform: translateY(-2px);
        }

        .hero-trust-badges {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
            font-size: 12.5px;
            color: #a7f3d0;
            font-weight: 600;
        }

        .hero-trust-badges span {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        /* ─── Features ─── */
        .features-section {
            padding: 40px 16px 50px;
            max-width: 1160px;
            margin: 0 auto;
            width: 100%;
        }

        .section-header {
            text-align: center;
            margin-bottom: 36px;
        }

        .section-header h2 {
            font-size: clamp(22px, 4vw, 30px);
            font-weight: 900;
            color: #34d399;
            margin-bottom: 10px;
        }

        .section-header p {
            color: rgba(248, 250, 252, 0.7);
            font-size: clamp(13.5px, 2vw, 16px);
            max-width: 600px;
            margin: 0 auto;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
        }

        .feature-card {
            background: rgba(6, 78, 59, 0.55);
            border: 1px solid rgba(52, 211, 153, 0.2);
            border-radius: 18px;
            padding: 24px 20px;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            transition: all 0.25s ease;
            display: flex;
            flex-direction: column;
        }

        .feature-card:hover {
            transform: translateY(-3px);
            border-color: rgba(52, 211, 153, 0.45);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.25);
            background: rgba(6, 78, 59, 0.75);
        }

        .feature-icon-wrapper {
            width: 50px;
            height: 50px;
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(52, 211, 153, 0.25);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 14px;
        }

        .feature-card h3 {
            font-size: 17.5px;
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 8px;
        }

        .feature-card p {
            font-size: 13.5px;
            color: rgba(248, 250, 252, 0.7);
            line-height: 1.7;
        }

        /* ─── Properties Showcase ─── */
        .properties-section {
            padding: 30px 16px 50px;
            max-width: 1160px;
            margin: 0 auto;
            width: 100%;
        }

        .properties-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
        }

        .property-card {
            background: rgba(6, 78, 59, 0.55);
            border: 1px solid rgba(52, 211, 153, 0.2);
            border-radius: 18px;
            overflow: hidden;
            transition: all 0.25s ease;
        }

        .property-card:hover {
            transform: translateY(-3px);
            border-color: rgba(52, 211, 153, 0.45);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.25);
        }

        .property-img {
            width: 100%;
            height: 170px;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(2, 44, 34, 0.6));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 44px;
            border-bottom: 1px solid rgba(52, 211, 153, 0.15);
        }

        .property-info {
            padding: 18px;
        }

        .property-info h3 {
            font-size: 16.5px;
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 6px;
        }

        .property-info .location {
            color: #a7f3d0;
            font-size: 12.5px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .property-info .price {
            font-size: 19px;
            font-weight: 900;
            color: #34d399;
        }

        .property-info .price span {
            font-size: 12px;
            font-weight: 500;
            color: rgba(248, 250, 252, 0.6);
        }

        /* ─── Stats ─── */
        .stats-wrapper {
            max-width: 1000px;
            margin: 20px auto 40px;
            padding: 0 16px;
            width: 100%;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
            background: rgba(2, 44, 34, 0.6);
            border: 1px solid rgba(52, 211, 153, 0.25);
            border-radius: 20px;
            padding: 24px 18px;
            backdrop-filter: blur(12px);
        }

        .stat-item {
            text-align: center;
            padding: 8px;
        }

        .stat-item .number {
            font-size: clamp(26px, 4vw, 36px);
            font-weight: 900;
            color: #34d399;
            line-height: 1.1;
            margin-bottom: 4px;
        }

        .stat-item .label {
            font-size: 13px;
            color: rgba(248, 250, 252, 0.7);
            font-weight: 700;
        }

        /* ─── Payment Methods ─── */
        .payment-section {
            text-align: center;
            padding: 30px 16px 50px;
            max-width: 960px;
            margin: 0 auto;
            width: 100%;
        }

        .payment-methods {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 12px;
            margin-top: 24px;
        }

        .payment-badge {
            background: rgba(6, 78, 59, 0.75);
            border: 1px solid rgba(52, 211, 153, 0.3);
            border-radius: 14px;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            font-size: 14.5px;
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.2);
            transition: all 0.2s ease;
        }

        .payment-badge:hover {
            transform: translateY(-2px);
            border-color: #34d399;
        }

        .payment-badge svg {
            width: 32px;
            height: 20px;
            flex-shrink: 0;
        }

        /* ─── Bottom CTA Banner ─── */
        .bottom-cta-banner {
            max-width: 1000px;
            margin: 20px auto 50px;
            padding: 0 16px;
            width: 100%;
        }

        .bottom-cta-card {
            background: linear-gradient(135deg, rgba(6, 78, 59, 0.95), rgba(4, 120, 87, 0.85));
            border: 1.5px solid rgba(52, 211, 153, 0.4);
            border-radius: 22px;
            padding: 36px 24px;
            text-align: center;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.35);
        }

        .bottom-cta-card h3 {
            font-size: clamp(20px, 3.5vw, 28px);
            font-weight: 900;
            color: #ffffff;
            margin-bottom: 10px;
        }

        .bottom-cta-card p {
            font-size: 14.5px;
            color: #a7f3d0;
            max-width: 600px;
            margin: 0 auto 22px;
        }

        /* ─── Footer ─── */
        .footer {
            background: rgba(2, 44, 34, 0.85);
            border-top: 1px solid rgba(52, 211, 153, 0.2);
            padding: 40px 16px 24px;
            margin-top: auto;
            width: 100%;
        }

        .footer-content {
            max-width: 1160px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 2fr 1fr 1.5fr;
            gap: 32px;
        }

        .footer h4 {
            color: #34d399;
            font-size: 16px;
            font-weight: 800;
            margin-bottom: 14px;
        }

        .footer p, .footer a {
            color: rgba(248, 250, 252, 0.7);
            font-size: 13.5px;
            line-height: 1.85;
            text-decoration: none;
        }

        .footer a:hover { color: #34d399; }

        .footer-links {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .footer-contact-item {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 24px;
            margin-top: 30px;
            border-top: 1px solid rgba(52, 211, 153, 0.15);
            color: rgba(248, 250, 252, 0.45);
            font-size: 12.5px;
        }

        /* ─── Responsive Media Queries ─── */
        @media (max-width: 992px) {
            .features-grid { grid-template-columns: repeat(2, 1fr); }
            .properties-grid { grid-template-columns: repeat(2, 1fr); }
            .footer-content { grid-template-columns: 1fr 1fr; }
        }

        @media (max-width: 768px) {
            .navbar-links { display: none; }
            .mobile-toggle { display: inline-flex; }
            .features-grid { grid-template-columns: 1fr; }
            .properties-grid { grid-template-columns: 1fr; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; padding: 18px 12px; }
            .footer-content { grid-template-columns: 1fr; gap: 24px; }
            .hero-cta-group { flex-direction: column; width: 100%; max-width: 320px; margin-left: auto; margin-right: auto; }
            .hero-cta-group a { width: 100%; justify-content: center; }
            .btn-nav-login span.btn-text-full { display: none; }
            .btn-nav-login span.btn-text-short { display: inline; }
        }

        @media (min-width: 769px) {
            .btn-nav-login span.btn-text-short { display: none; }
            .btn-nav-login span.btn-text-full { display: inline; }
        }
    </style>
</head>
<body>

    <!-- Sticky Header -->
    <header class="header-wrapper">
        <nav class="navbar">
            <a href="{{ route('home') }}" class="navbar-brand">
                <div class="navbar-brand-logo">
                    <img src="{{ asset('images/image.png') }}" alt="ثقة">
                </div>
                <div class="navbar-brand-text">
                    <span class="brand-name">ثقة</span>
                    <span class="brand-sub">توثيق العقود والحجوزات</span>
                </div>
            </a>

            <!-- Desktop Nav Links -->
            <div class="navbar-links">
                <a href="#features" class="nav-item">المميزات</a>
                <a href="#properties" class="nav-item">العقارات المدارة</a>
                <a href="#payment" class="nav-item">طرق الدفع</a>
                <a href="{{ route('verify.index') }}" class="nav-item">التحقق من عقد</a>
            </div>

            <!-- Header Action: Always visible Login Button + Mobile Toggle -->
            <div class="header-actions">
                <a href="{{ route('login') }}" class="btn-nav-login">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                    <span class="btn-text-full">تسجيل الدخول</span>
                    <span class="btn-text-short">دخول</span>
                </a>

                <button type="button" class="mobile-toggle" onclick="toggleMobileMenu()" aria-label="فتح القائمة">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>
        </nav>

        <!-- Mobile Dropdown Menu -->
        <div id="mobileMenu" class="mobile-menu">
            <ul class="mobile-menu-links">
                <li><a href="#features" onclick="toggleMobileMenu()">✨ المميزات والخدمات</a></li>
                <li><a href="#properties" onclick="toggleMobileMenu()">🏢 العقارات والوحدات</a></li>
                <li><a href="#payment" onclick="toggleMobileMenu()">💳 طرق الدفع المعتمدة</a></li>
                <li><a href="{{ route('verify.index') }}">🔍 التحقق من صحة عقد</a></li>
                <li><a href="/terms">📜 الشروط والأحكام</a></li>
                <li><a href="/privacy">🔒 سياسة الخصوصية</a></li>
                <li><a href="/refund-policy">🔄 سياسة الاسترجاع والإلغاء</a></li>
            </ul>
            <div class="mobile-menu-actions">
                <a href="{{ route('login') }}" class="btn-cta-primary" style="width: 100%; justify-content: center;">
                    <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                    دخول إلى لوحة التحكم
                </a>
            </div>
        </div>
    </header>

    <!-- Hero -->
    <section class="hero">
        <div class="hero-badge">
            <span>🛡️ نظام إدارة الحجوزات والتوثيق الإلكتروني المعتمد</span>
        </div>
        <h1>منصة توثيق العقود وإدارة الحجوزات الفندقية</h1>
        <p>
            حلول رقمية متكاملة لتوثيق عقود الإيجار وإدارة حجوزات الوحدات السكنية والفندقية، مع دعم بوابات الدفع الإلكتروني المعتمدة رسمياً في المملكة العربية السعودية.
        </p>

        <!-- Prominent Call To Action -->
        <div class="hero-cta-group">
            <a href="{{ route('login') }}" class="btn-cta-primary">
                <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                </svg>
                <span>تسجيل الدخول للنظام</span>
            </a>
            <a href="{{ route('verify.index') }}" class="btn-cta-secondary">
                <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <span>التحقق من صحة عقد</span>
            </a>
        </div>

        <div class="hero-trust-badges">
            <span>✅ دفع إلكتروني مشفر</span>
            <span>✅ متوافق مع Apple Pay ومدى</span>
            <span>✅ توثيق وتوقيع فوري</span>
        </div>
    </section>

    <!-- Stats -->
    <div class="stats-wrapper">
        <div class="stats-grid">
            <div class="stat-item">
                <div class="number">+500</div>
                <div class="label">عقد موثّق</div>
            </div>
            <div class="stat-item">
                <div class="number">+200</div>
                <div class="label">وحدة مدارة</div>
            </div>
            <div class="stat-item">
                <div class="number">+1000</div>
                <div class="label">عميل سعيد</div>
            </div>
            <div class="stat-item">
                <div class="number">+15</div>
                <div class="label">مدينة بالمملكة</div>
            </div>
        </div>
    </div>

    <!-- Features -->
    <section class="features-section" id="features">
        <div class="section-header">
            <h2>مميزات منصة ثقة</h2>
            <p>أدوات متطورة وشاملة تلبي احتياجات إدارة العقود والوحدات والحجوزات</p>
        </div>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon-wrapper">📄</div>
                <h3>توثيق العقود رقمياً</h3>
                <p>إنشاء وإدارة عقود الإيجار إلكترونياً مع التوقيع الرقمي والتوثيق القانوني الكامل وحفظ السجلات بشكل آمن.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon-wrapper">🏠</div>
                <h3>إدارة الوحدات السكنية</h3>
                <p>إدارة شاملة للشقق والأجنحة الفندقية، متابعة التوافر، وحالة النزلاء ومواعيد الدخول والمغادرة في مكان واحد.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon-wrapper">💳</div>
                <h3>دفع إلكتروني فوري</h3>
                <p>سداد إلكتروني معتمد يدعم Apple Pay وبطاقات مدى وVisa وMastercard مع إشعارات تسوية تلقائية.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon-wrapper">📱</div>
                <h3>كروت حجز ذكية</h3>
                <p>كارت حجز تفاعلي يتيح للعميل اختيار وسيلة الدفع المفضلة، تحميل كارت الحجز كصورة، والوصول الفوري للتفاصيل.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon-wrapper">✍️</div>
                <h3>توقيع إلكتروني معتمد</h3>
                <p>توقيع العقود إلكترونياً بالبصمة الرقمية والتحقق بالرسائل النصية والرمز السري في دقائق معدودة.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon-wrapper">📊</div>
                <h3>تقارير وإحصائيات دقيقة</h3>
                <p>لوحة تحكم إدارية متكاملة لمتابعة الفواتير المحصلة، الحجوزات النشطة، والبيانات المالية بدقة متناهية.</p>
            </div>
        </div>
    </section>

    <!-- Properties Showcase -->
    <section class="properties-section" id="properties">
        <div class="section-header">
            <h2>نماذج من العقارات المدارة</h2>
            <p>وحدات سكنية وفندقية راقية ومجهزة بالكامل في أفضل المواقع بالمملكة</p>
        </div>

        <div class="properties-grid">
            <div class="property-card">
                <div class="property-img">🏢</div>
                <div class="property-info">
                    <h3>جناح فندقي فاخر - جدة</h3>
                    <div class="location">📍 شارع الكورنيش، جدة</div>
                    <div class="price">350 <span>ر.س / ليلة</span></div>
                </div>
            </div>
            <div class="property-card">
                <div class="property-img">🏨</div>
                <div class="property-info">
                    <h3>شقة مفروشة عصرية - الرياض</h3>
                    <div class="location">📍 طريق الملك فهد، الرياض</div>
                    <div class="price">5,000 <span>ر.س / شهرياً</span></div>
                </div>
            </div>
            <div class="property-card">
                <div class="property-img">🏘️</div>
                <div class="property-info">
                    <h3>استوديو تنفيذي - الدمام</h3>
                    <div class="location">📍 شارع الملك سعود، الدمام</div>
                    <div class="price">2,800 <span>ر.س / شهرياً</span></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Payment Methods -->
    <section class="payment-section" id="payment">
        <div class="section-header">
            <h2>طرق الدفع المعتمدة</h2>
            <p>بوابات دفع آمنة ومعتمدة تضمن سرعة المعاملات وسهولة التحصيل</p>
        </div>

        <div class="payment-methods">
            <div class="payment-badge">
                <svg viewBox="0 0 38 24" fill="none"><rect width="38" height="24" rx="4" fill="#000"/><text x="19" y="15" text-anchor="middle" fill="#fff" font-size="9" font-weight="bold">Apple Pay</text></svg>
                Apple Pay
            </div>
            <div class="payment-badge">
                <svg viewBox="0 0 38 24" fill="none"><rect width="38" height="24" rx="4" fill="#1A1F71"/><text x="19" y="15" text-anchor="middle" fill="#fff" font-size="10" font-weight="bold">VISA</text></svg>
                Visa
            </div>
            <div class="payment-badge">
                <svg viewBox="0 0 38 24" fill="none"><rect width="38" height="24" rx="4" fill="#EB001B"/><circle cx="15" cy="12" r="7" fill="#EB001B"/><circle cx="23" cy="12" r="7" fill="#F79E1B"/><path d="M19 6.5a7 7 0 0 1 0 11" fill="#FF5F00"/></svg>
                Mastercard
            </div>
            <div class="payment-badge">
                <svg viewBox="0 0 38 24" fill="none"><rect width="38" height="24" rx="4" fill="#0E4C92"/><text x="19" y="15" text-anchor="middle" fill="#fff" font-size="9" font-weight="bold">mada</text></svg>
                مدى
            </div>
            <div class="payment-badge">
                <span style="font-weight:900; color:#3effbf; font-size:16px;">tabby</span>
                تابي
            </div>
            <div class="payment-badge">
                <span style="font-weight:900; color:#ff9f76; font-size:16px;">tamara</span>
                تمارا
            </div>
        </div>
    </section>

    <!-- Bottom CTA Banner -->
    <div class="bottom-cta-banner">
        <div class="bottom-cta-card">
            <h3>جاهز لإدارة وتوثيق وحداتك وعقودك باحترافية؟</h3>
            <p>سجل دخولك الآن للوصول إلى لوحة التحكم واستخراج الفواتير والروابط في ثوانٍ</p>
            <a href="{{ route('login') }}" class="btn-cta-primary">
                <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                </svg>
                <span>دخول لوحة التحكم</span>
            </a>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div>
                <h4>عن منصة ثقة</h4>
                <p>
                    منصة ثقة هي نظام رقمي متكامل لتوثيق عقود الإيجار وإدارة حجوزات الوحدات السكنية والفندقية، وتوفير حلول دفع إلكتروني آمنة ومتوافقة مع المعايير السعودية.
                </p>
                <p style="margin-top: 12px;">
                    <strong style="color: #34d399;">مؤسسة موقع أمر التجارية</strong><br>
                    المملكة العربية السعودية
                </p>
            </div>
            <div>
                <h4>روابط مهمة</h4>
                <ul class="footer-links">
                    <li><a href="{{ route('login') }}">تسجيل الدخول</a></li>
                    <li><a href="{{ route('verify.index') }}">التحقق من صحة عقد</a></li>
                    <li><a href="/terms">الشروط والأحكام</a></li>
                    <li><a href="/privacy">سياسة الخصوصية</a></li>
                    <li><a href="/refund-policy">سياسة الاسترجاع والإلغاء</a></li>
                </ul>
            </div>
            <div>
                <h4>تواصل معنا</h4>
                <div class="footer-contact-item">
                    <span>📧</span>
                    <a href="mailto:info@thiqa.me">info@thiqa.me</a>
                </div>
                <div class="footer-contact-item">
                    <span>📞</span>
                    <a href="tel:+966597819851" style="color: #34d399; font-weight: 700;">+966 59 781 9851</a>
                </div>
                <div class="footer-contact-item">
                    <span>💬</span>
                    <a href="https://wa.me/966597819851" target="_blank" style="color: #34d399; font-weight: 700;">مراسلة عبر واتساب</a>
                </div>
                <p style="margin-top: 10px;">📍 المملكة العربية السعودية</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© {{ date('Y') }} ثقة - منصة توثيق العقود. جميع الحقوق محفوظة. مؤسسة موقع أمر التجارية.</p>
        </div>
    </footer>

    <script>
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            if (menu) {
                menu.classList.toggle('active');
            }
        }
    </script>
</body>
</html>
