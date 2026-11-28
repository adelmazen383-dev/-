<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#064e3b">
    <title>منصة ثقة | تسجيل الدخول — الإدارة ومقدمي الخدمات</title>
    <meta name="description" content="بوابة تسجيل الدخول الموحدة في منصة ثقة (thiqa.me) للإدارة ومقدمي الخدمات في المملكة العربية السعودية باستخدام البريد الإلكتروني.">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="alternate" hreflang="ar-SA" href="{{ url()->current() }}">
    <link rel="alternate" hreflang="x-default" href="{{ url()->current() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/image.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/image.png') }}">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="ar_SA">
    <meta property="og:site_name" content="منصة ثقة">
    <meta property="og:title" content="منصة ثقة | تسجيل الدخول — الإدارة ومقدمي الخدمات">
    <meta property="og:description" content="بوابة الدخول الموحدة لمنصة ثقة لتوثيق العقود وإدارة الحجوزات وبوابة مقدمي الخدمات في السعودية.">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('images/og-thiqa.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="منصة ثقة | تسجيل الدخول">
    <meta name="twitter:image" content="{{ asset('images/og-thiqa.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * { font-family: 'Cairo', sans-serif; box-sizing: border-box; }
        
        body {
            background: #064e3b;
            background-image: 
                radial-gradient(circle at 50% 0%, #065f46 0%, transparent 60%),
                radial-gradient(circle at 10% 90%, #047857 0%, transparent 50%),
                radial-gradient(circle at 90% 80%, #022c22 0%, transparent 60%);
            min-height: 100vh;
            min-height: 100dvh;
            color: #f8fafc;
            margin: 0;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 16px;
        }

        /* Subtle glowing background orbs */
        .bg-glow-1 {
            position: fixed;
            top: -120px;
            right: -120px;
            width: 420px;
            height: 420px;
            background: radial-gradient(circle, rgba(52, 211, 153, 0.18) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
            animation: floatGlow 14s ease-in-out infinite alternate;
        }

        .bg-glow-2 {
            position: fixed;
            bottom: -150px;
            left: -150px;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.14) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
            animation: floatGlow 18s ease-in-out infinite alternate-reverse;
        }

        @keyframes floatGlow {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(30px, 30px) scale(1.1); }
        }

        .auth-container {
            width: 100%;
            max-width: 440px;
            position: relative;
            z-index: 10;
            margin: auto;
        }

        .top-nav-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            padding: 0 4px;
        }

        .back-home-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #a7f3d0;
            background: rgba(6, 78, 59, 0.6);
            border: 1px solid rgba(52, 211, 153, 0.25);
            padding: 7px 14px;
            border-radius: 9999px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s ease;
            backdrop-filter: blur(8px);
        }

        .back-home-btn:hover {
            background: rgba(16, 185, 129, 0.25);
            color: #ffffff;
            border-color: #34d399;
            transform: translateY(-1px);
        }

        .brand-badge {
            font-size: 12px;
            font-weight: 700;
            color: #6ee7b7;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .glass-card {
            background: rgba(6, 78, 59, 0.72);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(52, 211, 153, 0.22);
            border-radius: 24px;
            box-shadow: 
                0 20px 40px -15px rgba(0, 0, 0, 0.5),
                0 0 50px -10px rgba(16, 185, 129, 0.15);
            padding: 28px 24px;
        }

        @media (min-width: 640px) {
            .glass-card {
                padding: 36px 32px;
            }
        }

        .input-field {
            background: rgba(2, 44, 34, 0.55);
            border: 1.5px solid rgba(52, 211, 153, 0.22);
            border-radius: 12px;
            color: #ffffff !important;
            padding: 13px 16px;
            width: 100%;
            transition: all 0.25s ease;
            font-size: 15px;
        }

        .input-field:focus {
            outline: none;
            border-color: #34d399;
            box-shadow: 0 0 0 3px rgba(52, 211, 153, 0.22);
            background: rgba(2, 44, 34, 0.85);
            color: #ffffff !important;
        }

        .input-field::placeholder { color: #94a3b8; }
        
        .input-field:-webkit-autofill,
        .input-field:-webkit-autofill:hover, 
        .input-field:-webkit-autofill:focus {
            -webkit-text-fill-color: #ffffff !important;
            -webkit-box-shadow: 0 0 0px 1000px #064e3b inset !important;
            transition: background-color 5000s ease-in-out 0s;
        }

        .btn-auth-primary {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #ffffff;
            padding: 13px 24px;
            border-radius: 12px;
            font-weight: 800;
            width: 100%;
            border: none;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.25s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 4px 18px rgba(16, 185, 129, 0.35);
        }

        .btn-auth-primary:hover {
            transform: translateY(-1.5px);
            box-shadow: 0 8px 24px rgba(16, 185, 129, 0.45);
            background: linear-gradient(135deg, #34d399 0%, #059669 100%);
        }

        .btn-auth-primary:active {
            transform: translateY(0);
        }

        .auth-footer {
            text-align: center;
            margin-top: 24px;
            color: rgba(248, 250, 252, 0.5);
            font-size: 12.5px;
        }
    </style>
</head>
<body>
    <div class="bg-glow-1"></div>
    <div class="bg-glow-2"></div>

    <div class="auth-container">
        <!-- Top Nav Bar with Return Home -->
        <div class="top-nav-bar">
            <a href="{{ route('home') }}" class="back-home-btn">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                <span>الموقع الرئيسي</span>
            </a>
            <div class="brand-badge">
                <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>بوابة الدخول الموحدة</span>
            </div>
        </div>

        <!-- Glass Card Content -->
        <div class="glass-card">
            {{ $slot }}
        </div>

        <!-- Copyright -->
        <div class="auth-footer">
            <p>&copy; {{ date('Y') }} منصة ثقة لتوثيق العقود. جميع الحقوق محفوظة.</p>
        </div>
    </div>
</body>
</html>
