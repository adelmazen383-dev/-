<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام إدارة العقود | @yield('title', 'لوحة التحكم')</title>
    
    <!-- Google Fonts: Cairo -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS (Vite) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f8fafc;
        }
    </style>
    @stack('styles')
</head>
<body class="antialiased text-slate-800">

    <!-- Navigation Bar -->
    <nav class="bg-white shadow-sm border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- القائمة اليمنى (اللوجو والروابط) -->
                <div class="flex">
                    <div class="shrink-0 flex items-center">
                        <a href="{{ url('/dashboard') }}" class="text-2xl font-bold text-blue-600">
                            نظام العقود
                        </a>
                    </div>
                    
                    <div class="hidden sm:-my-px sm:mr-8 sm:flex sm:space-x-8 sm:space-x-reverse">
                        <a href="{{ url('/dashboard') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->is('dashboard') ? 'border-blue-600 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }} text-sm font-semibold transition duration-150 ease-in-out">
                            الرئيسية
                        </a>
                        
                        @hasanyrole('admin|employee|supervisor')
                        <a href="{{ route('clients.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('clients.*') ? 'border-blue-600 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }} text-sm font-semibold transition duration-150 ease-in-out">
                            العملاء
                        </a>
                        
                        <a href="{{ route('contracts.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('contracts.*') ? 'border-blue-600 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }} text-sm font-semibold transition duration-150 ease-in-out">
                            العقود
                        </a>
                        @endhasanyrole

                        @role('admin')
                        <a href="{{ route('templates.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('templates.*') ? 'border-blue-600 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }} text-sm font-semibold transition duration-150 ease-in-out">
                            قوالب العقود
                        </a>
                        @endrole
                    </div>
                </div>

                <!-- القائمة اليسرى (بيانات المستخدم) -->
                <div class="hidden sm:flex sm:items-center sm:ml-6">
                    <div class="flex items-center gap-4">
                        <span class="text-sm font-medium text-slate-700">{{ Auth::user()->name ?? 'مستخدم تجريبي' }}</span>
                        
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-sm text-red-500 hover:text-red-700 font-semibold transition">
                                تسجيل الخروج
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Page Content -->
    <main class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- رسائل النجاح أو الخطأ -->
            @if(session('success'))
                <div class="mb-6 bg-emerald-50 border-r-4 border-emerald-500 p-4 rounded-md shadow-sm">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-emerald-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="mr-3">
                            <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 bg-red-50 border-r-4 border-red-500 p-4 rounded-md shadow-sm">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="mr-3">
                            <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- محتوى الصفحة المتغير -->
            @yield('content')
            
        </div>
    </main>

    @stack('scripts')
</body>
</html>
