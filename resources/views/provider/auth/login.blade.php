@extends('layouts.provider-guest', ['title' => 'تسجيل الدخول'])

@section('content')
    <div class="mb-5">
        <h2 class="text-lg font-black text-slate-900">تسجيل دخول مقدم الخدمة</h2>
        <p class="text-xs text-slate-500 mt-0.5">ادخل باستخدام البريد الإلكتروني أو رقم الجوال المسجل</p>
    </div>

    <form method="POST" action="{{ route('provider.login.submit') }}" class="space-y-4">
        @csrf

        <div>
            <label for="login" class="block text-xs font-bold text-slate-700 mb-1.5">البريد الإلكتروني أو رقم الجوال</label>
            <input type="text" id="login" name="login" value="{{ old('login') }}" required autofocus
                   class="p-input" placeholder="example@domain.com أو 05xxxxxxxx" dir="ltr">
            @error('login')
                <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="block text-xs font-bold text-slate-700">كلمة المرور</label>
                <a href="{{ route('provider.password.request') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                    نسيت كلمة المرور؟ (OTP)
                </a>
            </div>
            <input type="password" id="password" name="password" required class="p-input" placeholder="••••••••">
            @error('password')
                <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p>
            @enderror
        </div>

        <label class="inline-flex items-center gap-2 text-xs text-slate-600 font-semibold cursor-pointer">
            <input type="checkbox" name="remember" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
            تذكرني على هذا الجهاز
        </label>

        <button type="submit" class="p-btn-primary">
            دخول إلى لوحة مقدم الخدمة
        </button>
    </form>

    <div class="mt-6 pt-5 border-t border-slate-100 text-center">
        <p class="text-xs text-slate-600 font-semibold">
            ليس لديك حساب مقدم خدمة بعد؟
            <a href="{{ route('provider.register') }}" class="text-indigo-600 hover:text-indigo-800 font-black underline mr-1">
                سجّل الآن كمقدم خدمة
            </a>
        </p>
    </div>
@endsection
