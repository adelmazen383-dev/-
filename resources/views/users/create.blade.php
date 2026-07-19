<x-app-layout>
    <x-slot name="header">إضافة موظف جديد</x-slot>

    <div class="card max-w-2xl mx-auto">
        <form method="POST" action="{{ route('users.store') }}" class="space-y-6">
            @csrf

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">الاسم الرباعي</label>
                <input type="text" name="name" value="{{ old('name') }}" class="form-input w-full" required autofocus>
                @error('name') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">البريد الإلكتروني (لتسجيل الدخول)</label>
                <input type="email" name="email" value="{{ old('email') }}" class="form-input w-full" required autocomplete="username">
                @error('email') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">كلمة المرور</label>
                    <input type="password" name="password" class="form-input w-full" required autocomplete="new-password">
                    @error('password') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">تأكيد كلمة المرور</label>
                    <input type="password" name="password_confirmation" class="form-input w-full" required autocomplete="new-password">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">الصلاحية</label>
                <select name="role" class="form-input w-full" required>
                    <option value="employee" {{ old('role') == 'employee' ? 'selected' : '' }}>موظف عام (إنشاء وعرض العقود فقط)</option>
                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>مدير النظام (كامل الصلاحيات + الإلغاء والحذف)</option>
                </select>
                @error('role') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="pt-4 border-t border-slate-200 flex justify-end gap-3">
                <a href="{{ route('users.index') }}" class="btn btn-ghost">إلغاء</a>
                <button type="submit" class="btn btn-indigo">حفظ الموظف</button>
            </div>
        </form>
    </div>
</x-app-layout>
