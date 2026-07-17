<x-guest-layout>
    <h2 class="text-2xl font-bold text-white mb-1">إنشاء حساب جديد ✨</h2>
    <p class="text-slate-400 text-sm mb-6">أنشئ حسابك للبدء في إدارة العقود</p>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div class="mb-4">
            <label for="name" class="block text-sm font-semibold text-slate-300 mb-2">الاسم الكامل</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                   class="input-field" placeholder="أحمد محمد">
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email -->
        <div class="mb-4">
            <label for="email" class="block text-sm font-semibold text-slate-300 mb-2">البريد الإلكتروني</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                   class="input-field" placeholder="you@example.com">
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mb-4">
            <label for="password" class="block text-sm font-semibold text-slate-300 mb-2">كلمة المرور</label>
            <input id="password" type="password" name="password" required autocomplete="new-password"
                   class="input-field" placeholder="••••••••">
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mb-6">
            <label for="password_confirmation" class="block text-sm font-semibold text-slate-300 mb-2">تأكيد كلمة المرور</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                   class="input-field" placeholder="••••••••">
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <button type="submit" class="btn-primary">
            إنشاء الحساب
        </button>

        <p class="text-center mt-5 text-slate-400 text-sm">
            لديك حساب بالفعل؟
            <a href="{{ route('login') }}" class="text-indigo-400 hover:text-indigo-300 font-semibold transition">
                تسجيل الدخول
            </a>
        </p>
    </form>
</x-guest-layout>
