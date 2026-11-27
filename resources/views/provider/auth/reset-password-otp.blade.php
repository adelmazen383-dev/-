@extends('layouts.provider-guest', ['title' => 'تأكيد رمز OTP وتعيين كلمة مرور جديدة', 'metaRobots' => 'noindex, nofollow'])

@section('content')
    <div class="mb-5">
        <h2 class="text-lg font-black text-slate-900">أدخل رمز التحقق (OTP)</h2>
        <p class="text-xs text-slate-500 mt-1 leading-relaxed">
            تم إرسال رمز مكوّن من 6 أرقام إلى بريدك:
            <strong class="text-indigo-700 font-mono" dir="ltr">{{ $maskedEmail }}</strong>
            <br>صالح لمدة 10 دقائق.
        </p>
    </div>

    <form method="POST" action="{{ route('provider.password.otp.reset') }}" class="space-y-4">
        @csrf

        <div>
            <label for="otp_code" class="block text-xs font-bold text-slate-700 mb-1.5">رمز التحقق المكوّن من 6 أرقام</label>
            <input type="text" id="otp_code" name="otp_code" value="{{ old('otp_code') }}" required autofocus
                   maxlength="6" minlength="6" inputmode="numeric" pattern="[0-9]{6}" autocomplete="one-time-code"
                   class="p-input text-center text-2xl font-black tracking-[0.45em] font-mono"
                   placeholder="000000" dir="ltr">
            @error('otp_code')
                <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-xs font-bold text-slate-700 mb-1.5">كلمة المرور الجديدة (8 أحرف على الأقل)</label>
            <input type="password" id="password" name="password" required class="p-input" placeholder="••••••••">
            @error('password')
                <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-xs font-bold text-slate-700 mb-1.5">تأكيد كلمة المرور الجديدة</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required class="p-input" placeholder="••••••••">
        </div>

        <button type="submit" class="p-btn-primary">
            تأكيد الرمز وتغيير كلمة المرور
        </button>
    </form>

    <div class="mt-5 pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
        <form method="POST" action="{{ route('provider.password.otp.resend') }}">
            @csrf
            <button type="submit" id="resend-otp-btn"
                    {{ $cooldown > 0 ? 'disabled' : '' }}
                    class="font-extrabold text-indigo-600 hover:text-indigo-800 disabled:text-slate-400 disabled:cursor-not-allowed">
                @if($cooldown > 0)
                    إعادة إرسال الرمز بعد (<span id="cooldown-sec">{{ $cooldown }}</span> ث)
                @else
                    إعادة إرسال رمز جديد
                @endif
            </button>
        </form>

        <a href="{{ route('provider.password.request') }}" class="text-slate-500 hover:text-slate-800 font-semibold">
            تغيير البريد / الجوال
        </a>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        let sec = {{ (int) $cooldown }};
        const btn = document.getElementById('resend-otp-btn');
        const span = document.getElementById('cooldown-sec');
        if (sec > 0 && btn && span) {
            const timer = setInterval(() => {
                sec--;
                if (sec <= 0) {
                    clearInterval(timer);
                    btn.disabled = false;
                    btn.textContent = 'إعادة إرسال رمز جديد';
                } else {
                    span.textContent = sec;
                }
            }, 1000);
        }
    })();
</script>
@endpush
