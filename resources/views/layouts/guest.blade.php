<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>نظام إدارة العقود | تسجيل الدخول</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * { font-family: 'Cairo', sans-serif; }
        .auth-bg {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            min-height: 100vh;
            position: relative;
            overflow: hidden;
        }
        .auth-bg::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(99,102,241,0.15) 0%, transparent 70%);
            animation: float 8s ease-in-out infinite;
        }
        .auth-bg::after {
            content: '';
            position: absolute;
            bottom: -50%;
            left: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(16,185,129,0.1) 0%, transparent 70%);
            animation: float 10s ease-in-out infinite reverse;
        }
        @keyframes float {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(30px, -30px); }
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.3);
        }
        .input-field {
            background: rgba(255, 255, 255, 0.07);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            color: #e2e8f0;
            padding: 12px 16px;
            width: 100%;
            transition: all 0.3s ease;
            font-size: 15px;
        }
        .input-field:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99,102,241,0.2);
            background: rgba(255, 255, 255, 0.1);
        }
        .input-field::placeholder { color: #64748b; }
        .btn-primary {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 700;
            width: 100%;
            border: none;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(99,102,241,0.4);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99,102,241,0.5);
        }
        .floating-shapes div {
            position: absolute;
            border-radius: 50%;
            opacity: 0.05;
            background: white;
        }
    </style>
</head>
<body class="auth-bg">
    <div class="floating-shapes">
        <div style="width:300px;height:300px;top:10%;right:10%;animation:float 12s infinite;"></div>
        <div style="width:200px;height:200px;bottom:20%;left:15%;animation:float 8s infinite reverse;"></div>
        <div style="width:150px;height:150px;top:50%;left:50%;animation:float 15s infinite;"></div>
    </div>

    <div class="min-h-screen flex flex-col justify-center items-center px-4 relative z-10">
        <!-- Logo -->
        <div class="mb-8 text-center">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 shadow-lg shadow-indigo-500/30 mb-4">
                <svg class="w-10 h-10 text-slate-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <h1 class="text-3xl font-black text-slate-800">نظام العقود</h1>
            <p class="text-slate-600 mt-1 text-sm">إدارة عقود التأجير الإلكترونية</p>
        </div>

        <!-- Card -->
        <div class="glass-card w-full max-w-md p-8">
            {{ $slot }}
        </div>

        <p class="text-slate-600 text-xs mt-8">&copy; {{ date('Y') }} نظام العقود. جميع الحقوق محفوظة.</p>
    </div>
</body>
</html>
