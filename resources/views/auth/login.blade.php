<x-guest-layout>
    <!-- Brand & Header -->
    <div class="text-center mb-6">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-white p-2.5 shadow-xl shadow-emerald-950/40 mb-3 border border-emerald-400/30">
            <img src="{{ asset('images/image.png') }}" alt="ثقة" class="w-full h-full object-contain">
        </div>
        <h1 class="text-2xl font-black text-white">تسجيل الدخول</h1>
        <p class="text-emerald-200/80 text-xs sm:text-sm mt-1">لوحة إدارة منصة ثقة لتوثيق العقود والحجوزات</p>
    </div>

    <!-- Session Status / Alerts -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    @if (session('warning'))
        <div class="mb-5 p-3.5 rounded-xl bg-amber-500/15 border border-amber-400/40 text-amber-200 text-xs sm:text-sm leading-relaxed flex items-start gap-2.5">
            <svg class="w-5 h-5 text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <span>{{ session('warning') }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" id="login-form">
        @csrf

        <!-- Email -->
        <div class="mb-4">
            <label for="email" class="block text-xs sm:text-sm font-bold text-emerald-100 mb-1.5">
                البريد الإلكتروني
            </label>
            <div class="relative">
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                       class="input-field pl-10" placeholder="admin@thiqa.me">
                <div class="absolute left-3 top-1/2 -translate-y-1/2 text-emerald-300/60 pointer-events-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                    </svg>
                </div>
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <!-- Password -->
        <div class="mb-4">
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="block text-xs sm:text-sm font-bold text-emerald-100">
                    كلمة المرور
                </label>
                <button type="button" onclick="togglePasswordVisibility()" class="text-xs text-emerald-300 hover:text-white transition focus:outline-none" id="pw-text">
                    إظهار
                </button>
            </div>
            <div class="relative">
                <input id="password" type="password" name="password" required autocomplete="current-password"
                       class="input-field pl-10" placeholder="••••••••">
                <button type="button" onclick="togglePasswordVisibility()" class="absolute left-3 top-1/2 -translate-y-1/2 text-emerald-300/70 hover:text-white transition focus:outline-none" title="إظهار / إخفاء كلمة المرور">
                    <svg id="eye-icon-open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <svg id="eye-icon-closed" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <!-- Remember + Forgot -->
        <div class="flex items-center justify-between mb-5 pt-1">
            <label for="remember_me" class="inline-flex items-center cursor-pointer select-none">
                <input id="remember_me" type="checkbox" name="remember" checked
                       class="rounded bg-emerald-950/60 border-emerald-400/40 text-emerald-500 focus:ring-emerald-400 focus:ring-offset-0 w-4 h-4">
                <span class="mr-2 text-xs sm:text-sm text-emerald-100 font-medium">تذكر تسجيل الدخول</span>
            </label>
            @if (Route::has('password.request'))
                <a class="text-xs sm:text-sm text-emerald-300 hover:text-white transition font-medium" href="{{ route('password.request') }}">
                    نسيت كلمة المرور؟
                </a>
            @endif
        </div>

        <!-- Submit Button -->
        <button type="submit" id="submit-btn" class="btn-auth-primary">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
            </svg>
            <span id="btn-text">دخول إلى النظام</span>
        </button>
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
                if (pwText) pwText.innerText = 'إخفاء';
            } else {
                pw.type = 'password';
                if (eyeOpen) eyeOpen.classList.remove('hidden');
                if (eyeClosed) eyeClosed.classList.add('hidden');
                if (pwText) pwText.innerText = 'إظهار';
            }
        }

        // Prevent double submit and give responsive loading feedback
        const loginForm = document.getElementById('login-form');
        const submitBtn = document.getElementById('submit-btn');
        const btnText = document.getElementById('btn-text');

        if (loginForm && submitBtn) {
            loginForm.addEventListener('submit', function() {
                submitBtn.disabled = true;
                submitBtn.style.opacity = '0.75';
                if (btnText) btnText.innerText = 'جارٍ التحقق والدخول...';
            });
        }

        // Anti-419: Auto-reload if page is restored from mobile browser back-forward cache (bfcache)
        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                window.location.reload();
            }
        });
    </script>
</x-guest-layout>
