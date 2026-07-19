<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <h2 class="text-2xl font-bold text-slate-800 mb-1">مرحباً بعودتك 👋</h2>
    <p class="text-slate-600 text-sm mb-6">سجّل دخولك للوصول إلى لوحة التحكم</p>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email -->
        <div class="mb-4">
            <label for="email" class="block text-sm font-semibold text-slate-700 mb-2">البريد الإلكتروني</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                   class="input-field" placeholder="admin@example.com">
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mb-4">
            <label for="password" class="block text-sm font-semibold text-slate-700 mb-2">كلمة المرور</label>
            <input id="password" type="password" name="password" required autocomplete="current-password"
                   class="input-field" placeholder="••••••••">
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember + Forgot -->
        <div class="flex items-center justify-between mb-6">
            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                <input id="remember_me" type="checkbox" name="remember"
                       class="rounded bg-slate-100 border-white/20 text-indigo-500 focus:ring-indigo-500 focus:ring-offset-0">
                <span class="mr-2 text-sm text-slate-600">تذكرني</span>
            </label>
            @if (Route::has('password.request'))
                <a class="text-sm text-indigo-400 hover:text-indigo-300 transition" href="{{ route('password.request') }}">
                    نسيت كلمة المرور؟
                </a>
            @endif
        </div>

        <button type="submit" class="btn-primary">
            تسجيل الدخول
        </button>

        @if (Route::has('register'))
            <p class="text-center mt-5 text-slate-600 text-sm">
                ليس لديك حساب؟
                <a href="{{ route('register') }}" class="text-indigo-400 hover:text-indigo-300 font-semibold transition">
                    إنشاء حساب جديد
                </a>
            </p>
        @endif
    </form>
</x-guest-layout>
