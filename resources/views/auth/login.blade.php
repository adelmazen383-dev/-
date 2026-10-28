<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <h2 class="text-2xl font-black text-white mb-1">مرحباً بعودتك 👋</h2>
    <p class="text-slate-300 text-sm mb-6">سجّل دخولك للوصول إلى لوحة التحكم</p>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email -->
        <div class="mb-5">
            <label for="email" class="block text-sm font-bold text-white mb-2">البريد الإلكتروني</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                   class="input-field" placeholder="admin@example.com">
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mb-5">
            <div class="flex items-center justify-between mb-2">
                <label for="password" class="block text-sm font-bold text-white">كلمة المرور</label>
                <button type="button" onclick="togglePasswordVisibility()" class="text-xs text-indigo-300 hover:text-white flex items-center gap-1 transition focus:outline-none" id="toggle-pw-btn">
                    <span id="pw-text">إظهار كلمة المرور</span>
                </button>
            </div>
            <div class="relative">
                <input id="password" type="password" name="password" required autocomplete="current-password"
                       class="input-field pr-4 pl-11" placeholder="••••••••">
                <button type="button" onclick="togglePasswordVisibility()" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 hover:text-white transition focus:outline-none" title="إظهار / إخفاء كلمة المرور">
                    <svg id="eye-icon-open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <svg id="eye-icon-closed" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember + Forgot -->
        <div class="flex items-center justify-between mb-6">
            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                <input id="remember_me" type="checkbox" name="remember"
                       class="rounded bg-white/10 border-white/20 text-indigo-500 focus:ring-indigo-500 focus:ring-offset-0">
                <span class="mr-2 text-sm text-slate-200 font-medium">تذكرني</span>
            </label>
            @if (Route::has('password.request'))
                <a class="text-sm text-indigo-300 hover:text-white transition" href="{{ route('password.request') }}">
                    نسيت كلمة المرور؟
                </a>
            @endif
        </div>

        <button type="submit" class="btn-primary">
            تسجيل الدخول
        </button>

        @if (Route::has('register'))
            <p class="text-center mt-5 text-slate-300 text-sm">
                ليس لديك حساب؟
                <a href="{{ route('register') }}" class="text-indigo-400 hover:text-indigo-300 font-bold transition">
                    إنشاء حساب جديد
                </a>
            </p>
        @endif
    </form>

    <script>
        function togglePasswordVisibility() {
            const pw = document.getElementById('password');
            const eyeOpen = document.getElementById('eye-icon-open');
            const eyeClosed = document.getElementById('eye-icon-closed');
            const pwText = document.getElementById('pw-text');
            if (pw.type === 'password') {
                pw.type = 'text';
                if (eyeOpen) eyeOpen.classList.add('hidden');
                if (eyeClosed) eyeClosed.classList.remove('hidden');
                if (pwText) pwText.innerText = 'إخفاء كلمة المرور';
            } else {
                pw.type = 'password';
                if (eyeOpen) eyeOpen.classList.remove('hidden');
                if (eyeClosed) eyeClosed.classList.add('hidden');
                if (pwText) pwText.innerText = 'إظهار كلمة المرور';
            }
        }
    </script>
</x-guest-layout>
