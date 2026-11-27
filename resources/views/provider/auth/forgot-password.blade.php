@extends('layouts.provider-guest', ['title' => 'استعادة كلمة المرور (OTP)', 'metaRobots' => 'noindex, nofollow'])

@section('content')
    <div class="mb-5">
        <h2 class="text-lg font-black text-slate-900">استعادة كلمة المرور عبر رمز التحقق (OTP)</h2>
        <p class="text-xs text-slate-500 mt-1 leading-relaxed">
            أدخل البريد الإلكتروني أو رقم الجوال المسجل في حسابك، وسنرسل لك رمز تحقق مكوّن من 6 أرقام إلى بريدك الإلكتروني فوراً.
        </p>
    </div>

    <form method="POST" action="{{ route('provider.password.otp.send') }}" class="space-y-4">
        @csrf

        <div>
            <label for="identifier" class="block text-xs font-bold text-slate-700 mb-1.5">البريد الإلكتروني أو رقم الجوال</label>
            <input type="text" id="identifier" name="identifier" value="{{ old('identifier') }}" required autofocus
                   class="p-input" placeholder="example@domain.com أو 05xxxxxxxx" dir="ltr">
            @error('identifier')
                <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="p-btn-primary">
            إرسال رمز التحقق (OTP)
        </button>
    </form>

    <div class="mt-6 pt-4 border-t border-slate-100 text-center">
        <a href="{{ route('provider.login') }}" class="text-xs font-bold text-slate-600 hover:text-indigo-700">
            ← العودة إلى تسجيل الدخول
        </a>
    </div>
@endsection
