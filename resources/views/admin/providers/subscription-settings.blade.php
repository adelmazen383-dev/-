<x-app-layout>
    <x-slot name="header">إعدادات الاشتراك والأسعار والتواصل — مقدمي الخدمات</x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <div class="rounded-xl bg-[#064e3b] border border-emerald-700/40 text-white px-4 py-3.5 shadow-sm">
            <h2 class="text-sm sm:text-base font-black">لوحة التحكم في باقات الاشتراك وأسعارها وقنوات التواصل</h2>
            <p class="text-xs text-emerald-100/85 mt-0.5">
                أي تعديل تقوم بحفظه هنا يظهر فوراً لمقدمي الخدمات في صفحة «الاشتراك والتواصل» مع زر تواصل مباشر عبر واتساب أو الهاتف لتفعيل اشتراكهم.
            </p>
        </div>

        <form method="POST" action="{{ route('admin.providers.subscription-settings.save') }}" class="space-y-6">
            @csrf

            <!-- 1. Subscription Plans & Prices -->
            <div class="card">
                <h3 class="text-sm sm:text-base font-black text-slate-900 mb-4">1. باقات الاشتراك والأسعار (ر.س)</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach([1 => '1m', 3 => '3m', 6 => '6m', 12 => '12m'] as $months => $key)
                        @php $p = $config['plans'][$months]; @endphp
                        <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/70 space-y-3">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-xs font-black text-slate-800">
                                    {{ $months === 1 ? 'الاشتراك الشهري (شهر واحد)' : "باقة {$months} أشهر" }}
                                </span>
                                <label class="inline-flex items-center gap-2 text-xs font-bold text-emerald-700 cursor-pointer">
                                    <input type="checkbox" name="plan_{{ $key }}_enabled" value="1"
                                           {{ old("plan_{$key}_enabled", $p['enabled']) ? 'checked' : '' }}
                                           class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                    إظهار الباقة
                                </label>
                            </div>
                            <div>
                                <label class="form-label">اسم الباقة الظاهر لمقدم الخدمة</label>
                                <input type="text" name="plan_{{ $key }}_label" value="{{ old("plan_{$key}_label", $p['label']) }}" required class="form-input">
                            </div>
                            <div>
                                <label class="form-label">السعر (ريال سعودي)</label>
                                <input type="number" step="0.01" min="0" max="100000" name="plan_{{ $key }}_price"
                                       value="{{ old("plan_{$key}_price", number_format($p['price'], 2, '.', '')) }}" required
                                       class="form-input font-black text-emerald-800" dir="ltr">
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4">
                    <label class="form-label">مميزات الاشتراك (اكتب كل ميزة في سطر مستقل)</label>
                    <textarea name="features_text" rows="4" class="form-input" placeholder="ميزة واحدة في كل سطر...">{{ old('features_text', $config['features_text']) }}</textarea>
                </div>
            </div>

            <!-- 2. Contact Channels for Subscription Activation -->
            <div class="card">
                <h3 class="text-sm sm:text-base font-black text-slate-900 mb-1">2. قنوات التواصل مع الإدارة لتفعيل الاشتراك</h3>
                <p class="text-xs text-slate-500 mb-4">عندما يسجّل مقدم خدمة جديد، سيظهر له زر مباشر للتواصل معكم عبر هذه البيانات وطلب التفعيل.</p>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                    <div>
                        <label class="form-label">رقم واتساب التفعيل (مهم جداً)</label>
                        <input type="text" name="whatsapp" value="{{ old('whatsapp', $config['whatsapp']) }}" class="form-input" placeholder="05xxxxxxxx أو 9665xxxxxxxx" dir="ltr">
                    </div>
                    <div>
                        <label class="form-label">رقم الجوال للاتصال المباشر</label>
                        <input type="text" name="phone" value="{{ old('phone', $config['phone']) }}" class="form-input" placeholder="05xxxxxxxx" dir="ltr">
                    </div>
                    <div>
                        <label class="form-label">البريد الإلكتروني للدعم</label>
                        <input type="email" name="email" value="{{ old('email', $config['email']) }}" class="form-input" placeholder="support@thiqa.me" dir="ltr">
                    </div>
                </div>

                <div class="mt-4">
                    <label class="form-label">رسالة توجيهية لمقدم الخدمة في صفحة الاشتراك</label>
                    <textarea name="contact_note" rows="2" class="form-input">{{ old('contact_note', $config['contact_note']) }}</textarea>
                </div>
            </div>

            <!-- 3. Optional Bank Transfer Info for Paying Subscription -->
            <div class="card">
                <h3 class="text-sm sm:text-base font-black text-slate-900 mb-1">3. بيانات التحويل البنكي لسداد الاشتراك (اختياري)</h3>
                <p class="text-xs text-slate-500 mb-4">إذا أدخلت بيانات حسابكم البنكي هنا، ستظهر لمقدم الخدمة ليتمكن من التحويل وإرسال الإيصال لكم عبر الواتساب.</p>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                    <div>
                        <label class="form-label">اسم البنك</label>
                        <input type="text" name="bank_name" value="{{ old('bank_name', $config['bank_name']) }}" class="form-input" placeholder="مصرف الراجحي، الأهلي...">
                    </div>
                    <div>
                        <label class="form-label">اسم صاحب الحساب / المؤسسة</label>
                        <input type="text" name="bank_account" value="{{ old('bank_account', $config['bank_account']) }}" class="form-input" placeholder="مؤسسة موقع أمر التجارية">
                    </div>
                    <div>
                        <label class="form-label">رقم الآيبان (IBAN)</label>
                        <input type="text" name="bank_iban" value="{{ old('bank_iban', $config['bank_iban']) }}" class="form-input font-mono" placeholder="SA000000..." dir="ltr">
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('admin.providers.dashboard') }}" class="btn btn-ghost">رجوع</a>
                <button type="submit" class="btn btn-emerald px-6 py-2.5">حفظ إعدادات الاشتراك والأسعار والتواصل</button>
            </div>
        </form>
    </div>
</x-app-layout>
