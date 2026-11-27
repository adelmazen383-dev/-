@extends('layouts.provider', ['title' => 'حسابي والاشتراك', 'header' => 'بيانات الحساب والاشتراك الشهري'])

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <!-- Main Profile & Bank Form -->
        <div class="lg:col-span-2 space-y-5">
            <div class="p-card">
                <h2 class="text-base font-black text-slate-900 mb-4">بيانات النشاط والخدمة والتحويل البنكي</h2>

                <form method="POST" action="{{ route('provider.profile.update') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">الاسم الكامل</label>
                            <input type="text" name="name" value="{{ old('name', $provider->name) }}" required class="p-input">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">اسم النشاط التجاري</label>
                            <input type="text" name="business_name" value="{{ old('business_name', $provider->business_name) }}" required class="p-input">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">نوع الخدمة</label>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($serviceTypes as $type)
                                <label class="flex items-center gap-2 p-2.5 rounded-xl border-2 cursor-pointer border-slate-200 has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/50">
                                    <input type="radio" name="service_type" value="{{ $type['value'] }}"
                                           {{ old('service_type', $provider->service_type) === $type['value'] ? 'checked' : '' }}
                                           onchange="toggleProfileCustomService()" class="text-indigo-600">
                                    <span class="text-xs font-extrabold">{{ $type['icon'] }} {{ $type['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div id="profile-custom-service" class="{{ old('service_type', $provider->service_type) === 'other' ? '' : 'hidden' }}">
                        <label class="block text-xs font-bold text-slate-700 mb-1">اسم الخدمة المخصصة</label>
                        <input type="text" name="custom_service_name" value="{{ old('custom_service_name', $provider->custom_service_name) }}" class="p-input">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">تفاصيل الخدمة</label>
                        <textarea name="service_description" rows="3" required class="p-input">{{ old('service_description', $provider->service_description) }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">المدينة</label>
                            <input type="text" name="city" value="{{ old('city', $provider->city) }}" class="p-input">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">السجل التجاري / وثيقة العمل الحر</label>
                            <input type="text" name="commercial_register" value="{{ old('commercial_register', $provider->commercial_register) }}" class="p-input">
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-100">
                        <h3 class="text-xs font-extrabold text-slate-800 mb-2.5">🏦 بيانات التحويل البنكي</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">اسم البنك</label>
                                <input type="text" name="bank_name" value="{{ old('bank_name', $provider->bank_name) }}" class="p-input">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">اسم صاحب الحساب</label>
                                <input type="text" name="bank_account_name" value="{{ old('bank_account_name', $provider->bank_account_name) }}" class="p-input">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">IBAN</label>
                                <input type="text" name="iban" value="{{ old('iban', $provider->iban) }}" class="p-input" dir="ltr">
                            </div>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-100">
                        <h3 class="text-xs font-extrabold text-slate-800 mb-2.5">📂 الوثائق المرفوعة (يمكنك عرضها أو إعادة رفع ملف أحدث)</h3>
                        @php $docs = $provider->uploadedDocuments(); @endphp
                        @if(!empty($docs))
                            <div class="flex flex-wrap gap-2 mb-3">
                                @foreach($docs as $docKey => $docInfo)
                                    <a href="{{ route('provider.profile.document', $docKey) }}" target="_blank"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold hover:bg-emerald-100">
                                        📎 {{ $docInfo['label'] }} ↗
                                    </a>
                                @endforeach
                            </div>
                        @endif
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 text-xs">
                            <div>
                                <label class="block font-bold text-slate-600 mb-1">تحديث الهوية (أمام)</label>
                                <input type="file" name="national_id_front" accept=".jpg,.jpeg,.png,.webp,.pdf" class="p-input text-xs">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-600 mb-1">تحديث الهوية (خلف)</label>
                                <input type="file" name="national_id_back" accept=".jpg,.jpeg,.png,.webp,.pdf" class="p-input text-xs">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-600 mb-1">تحديث رخصة السياحة</label>
                                <input type="file" name="tourism_license" accept=".jpg,.jpeg,.png,.webp,.pdf" class="p-input text-xs">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-600 mb-1">تحديث وثيقة العمل الحر / السجل</label>
                                <input type="file" name="commercial_doc" accept=".jpg,.jpeg,.png,.webp,.pdf" class="p-input text-xs">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block font-bold text-slate-600 mb-1">تحديث ترخيص التصوير</label>
                                <input type="file" name="photography_license" accept=".jpg,.jpeg,.png,.webp,.pdf" class="p-input text-xs">
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-sm transition">
                        حفظ التعديلات
                    </button>
                </form>
            </div>

            <!-- Change Password -->
            <div class="p-card">
                <h2 class="text-base font-black text-slate-900 mb-4">🔒 تغيير كلمة المرور</h2>
                <form method="POST" action="{{ route('provider.profile.password') }}" class="space-y-3.5">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">كلمة المرور الحالية</label>
                        <input type="password" name="current_password" required class="p-input">
                        @error('current_password') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">كلمة المرور الجديدة</label>
                            <input type="password" name="password" required class="p-input">
                            @error('password') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">تأكيد كلمة المرور الجديدة</label>
                            <input type="password" name="password_confirmation" required class="p-input">
                        </div>
                    </div>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-extrabold text-xs">
                        تحديث كلمة المرور
                    </button>
                </form>
            </div>
        </div>

        <!-- Subscription Status & History -->
        <div class="space-y-5">
            <div class="p-card">
                <h3 class="text-sm font-black text-slate-900 mb-3">📅 حالة الاشتراك الشهري</h3>
                <div class="p-3.5 rounded-xl {{ $provider->hasActiveSubscription() ? 'bg-emerald-50 border border-emerald-200' : 'bg-amber-50 border border-amber-200' }}">
                    <div class="text-sm font-black {{ $provider->hasActiveSubscription() ? 'text-emerald-800' : 'text-amber-900' }}">
                        {{ $provider->subscriptionStatusLabel() }}
                    </div>
                    @if($provider->subscription_ends_at)
                        <div class="text-xs text-slate-700 mt-1">
                            ينتهي في: <strong>{{ $provider->subscription_ends_at->format('Y-m-d') }}</strong>
                        </div>
                    @endif
                    @if(!$provider->hasActiveSubscription())
                        <p class="text-[11.5px] text-amber-800 mt-2">لتفعيل أو تجديد اشتراكك الشهري، يُرجى التواصل مع الإدارة.</p>
                    @endif
                </div>

                @if($subscriptions->isNotEmpty())
                    <h4 class="text-xs font-extrabold text-slate-600 mt-4 mb-2">سجل الاشتراكات السابقة</h4>
                    <div class="space-y-2">
                        @foreach($subscriptions as $sub)
                            <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-100 text-xs">
                                <div class="font-bold text-slate-800">{{ $sub->plan_name }}</div>
                                <div class="text-[11px] text-slate-500 mt-0.5">
                                    من {{ $sub->starts_at->format('Y-m-d') }} إلى {{ $sub->ends_at->format('Y-m-d') }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function toggleProfileCustomService() {
        const val = document.querySelector('input[name="service_type"]:checked')?.value;
        const wrap = document.getElementById('profile-custom-service');
        if (wrap) wrap.classList.toggle('hidden', val !== 'other');
    }
</script>
@endpush
