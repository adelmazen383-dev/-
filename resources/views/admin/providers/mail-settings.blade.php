<x-app-layout>
    <x-slot name="header">إعدادات البريد الإلكتروني (SMTP) لرموز OTP</x-slot>

    <div class="max-w-3xl mx-auto space-y-5">
        <div class="card {{ $mail['is_live_ready'] ? 'border-emerald-300 bg-emerald-50/40' : 'border-amber-300 bg-amber-50/60' }}">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm font-black {{ $mail['is_live_ready'] ? 'text-emerald-900' : 'text-amber-900' }}">
                        {{ $mail['is_live_ready'] ? 'خدمة إرسال البريد ورموز OTP مهيأة وجاهزة' : 'خدمة SMTP غير مفعّلة بعد (وضع السجل log فقط)' }}
                    </h2>
                    <p class="text-xs text-slate-600 mt-1">
                        المرسل النشط حالياً: <strong class="font-mono">{{ $mail['effective_mailer'] }}</strong>.
                        تُستخدم هذه الإعدادات لإرسال رموز التحقق (OTP) المكونة من 6 أرقام عند نسيان مقدم الخدمة لكلمة المرور.
                    </p>
                </div>
            </div>
        </div>

        <div class="card">
            <h3 class="text-sm font-black text-slate-900 mb-4">إعدادات خادم البريد (SMTP)</h3>
            <form method="POST" action="{{ route('admin.providers.mail.save') }}" class="space-y-4">
                @csrf

                <label class="inline-flex items-center gap-2.5 text-sm font-extrabold text-slate-900 cursor-pointer">
                    <input type="checkbox" name="smtp_enabled" value="1" {{ old('smtp_enabled', $mail['smtp_enabled']) ? 'checked' : '' }}
                           class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                    تفعيل الإرسال عبر خادم SMTP المخصص أدناه
                </label>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                    <div class="sm:col-span-2">
                        <label class="form-label">عنوان الخادم (SMTP Host)</label>
                        <input type="text" name="smtp_host" value="{{ old('smtp_host', $mail['smtp_host']) }}" class="form-input" placeholder="smtp.hostinger.com أو smtp.gmail.com" dir="ltr">
                    </div>
                    <div>
                        <label class="form-label">المنفذ (Port)</label>
                        <input type="number" name="smtp_port" value="{{ old('smtp_port', $mail['smtp_port']) }}" class="form-input" placeholder="587 أو 465" dir="ltr">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                    <div>
                        <label class="form-label">التشفير</label>
                        <select name="smtp_encryption" class="form-input">
                            <option value="tls" {{ old('smtp_encryption', $mail['smtp_encryption']) === 'tls' ? 'selected' : '' }}>TLS (المنفذ 587)</option>
                            <option value="ssl" {{ old('smtp_encryption', $mail['smtp_encryption']) === 'ssl' ? 'selected' : '' }}>SSL (المنفذ 465)</option>
                            <option value="none" {{ old('smtp_encryption', $mail['smtp_encryption']) === 'none' ? 'selected' : '' }}>بدون تشفير</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">اسم المستخدم (البريد)</label>
                        <input type="text" name="smtp_username" value="{{ old('smtp_username', $mail['smtp_username']) }}" class="form-input" placeholder="no-reply@thiqa.me" dir="ltr">
                    </div>
                    <div>
                        <label class="form-label">كلمة مرور SMTP / App Password</label>
                        <input type="password" name="smtp_password" class="form-input" placeholder="{{ $mail['has_password'] ? 'محفوظة مشفّرة (اتركها فارغة للإبقاء)' : 'أدخل كلمة المرور' }}" dir="ltr">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="form-label">بريد المرسل (From Address)</label>
                        <input type="email" name="smtp_from_address" value="{{ old('smtp_from_address', $mail['smtp_from_address']) }}" class="form-input" placeholder="no-reply@thiqa.me" dir="ltr">
                    </div>
                    <div>
                        <label class="form-label">اسم المرسل (From Name)</label>
                        <input type="text" name="smtp_from_name" value="{{ old('smtp_from_name', $mail['smtp_from_name']) }}" class="form-input" placeholder="بوابة مقدمي الخدمات - ثقة">
                    </div>
                </div>

                <button type="submit" class="btn btn-emerald">حفظ إعدادات البريد و OTP</button>
            </form>
        </div>

        <!-- Test SMTP -->
        <div class="card">
            <h3 class="text-sm font-black text-slate-900 mb-2">اختبار إرسال البريد و OTP</h3>
            <p class="text-xs text-slate-500 mb-3">أدخل بريدك الإلكتروني واضغط اختبار للتأكد من وصول رسائل OTP فعلياً إلى البريد الوارد.</p>
            <form method="POST" action="{{ route('admin.providers.mail.test') }}" class="flex flex-col sm:flex-row gap-2.5">
                @csrf
                <input type="email" name="test_email" required class="form-input flex-1" placeholder="your-email@example.com" dir="ltr">
                <button type="submit" class="btn btn-indigo shrink-0">إرسال رسالة اختبار الآن</button>
            </form>
        </div>
    </div>
</x-app-layout>
