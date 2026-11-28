@extends('layouts.provider', ['title' => 'حسابي وبياناتي', 'header' => 'بيانات الحساب الموثّقة'])

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <!-- Read-Only Verified Account Info -->
        <div class="lg:col-span-2 space-y-5">
            <div class="p-card">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 mb-4 border-b border-slate-100">
                    <div>
                        <h2 class="text-base font-black text-slate-900">بيانات النشاط والخدمة الموثّقة</h2>
                        <p class="text-xs text-slate-500 mt-0.5">هذه البيانات موثّقة ومقفلة لحماية حسابك ولا يمكن تعديلها إلا عبر التواصل مع الإدارة.</p>
                    </div>
                    <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-extrabold shrink-0">
                        للعرض فقط
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs sm:text-sm">
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-400 block text-[11px] font-bold">اسم صاحب النشاط</span>
                        <strong class="text-slate-900">{{ $provider->name }}</strong>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-400 block text-[11px] font-bold">اسم المنشأة / العلامة التجارية</span>
                        <strong class="text-slate-900">{{ $provider->business_name }}</strong>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-400 block text-[11px] font-bold">البريد الإلكتروني</span>
                        <strong class="text-slate-900 font-mono" dir="ltr">{{ $provider->email }}</strong>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-400 block text-[11px] font-bold">رقم الجوال</span>
                        <strong class="text-slate-900 font-mono" dir="ltr">{{ $provider->phone }}</strong>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-400 block text-[11px] font-bold">نوع الخدمة</span>
                        <strong class="text-indigo-800">{{ $provider->service_type_label }}</strong>
                        @if($provider->property_kind)
                            <span class="text-xs text-slate-500">({{ $provider->property_kind }})</span>
                        @endif
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-400 block text-[11px] font-bold">المدينة</span>
                        <strong class="text-slate-900">{{ $provider->city ?: 'غير محدد' }}</strong>
                    </div>
                    @if($provider->national_id_number)
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-slate-400 block text-[11px] font-bold">رقم الهوية الوطنية</span>
                            <strong class="text-slate-900 font-mono" dir="ltr">{{ $provider->national_id_number }}</strong>
                        </div>
                    @endif
                    @if($provider->commercial_register)
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-slate-400 block text-[11px] font-bold">رقم وثيقة العمل الحر / السجل</span>
                            <strong class="text-slate-900 font-mono" dir="ltr">{{ $provider->commercial_register }}</strong>
                        </div>
                    @endif
                    <div class="sm:col-span-2 p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-400 block text-[11px] font-bold">وصف الخدمة</span>
                        <p class="text-slate-800 font-semibold mt-0.5 leading-relaxed">{{ $provider->service_description ?: 'غير محدد' }}</p>
                    </div>
                </div>

                <!-- Read-Only Bank Info -->
                <div class="mt-4 pt-4 border-t border-slate-100">
                    <h3 class="text-xs font-extrabold text-slate-800 mb-2.5">بيانات الحساب البنكي المسجل للتحويل</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-slate-400 block text-[11px] font-bold">اسم البنك</span>
                            <strong class="text-slate-900">{{ $provider->bank_name ?: 'غير محدد' }}</strong>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-slate-400 block text-[11px] font-bold">اسم صاحب الحساب</span>
                            <strong class="text-slate-900">{{ $provider->bank_account_name ?: 'غير محدد' }}</strong>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-slate-400 block text-[11px] font-bold">رقم الآيبان (IBAN)</span>
                            <strong class="text-indigo-800 font-mono break-all" dir="ltr">{{ $provider->iban ?: 'غير محدد' }}</strong>
                        </div>
                    </div>
                </div>

                <!-- Read-Only Uploaded KYC Documents -->
                <div class="mt-4 pt-4 border-t border-slate-100">
                    <h3 class="text-xs font-extrabold text-slate-800 mb-2.5">الوثائق المرفوعة عند التسجيل (للعرض فقط)</h3>
                    @php $docs = $provider->uploadedDocuments(); @endphp
                    @if(empty($docs))
                        <p class="text-xs text-slate-500">لا توجد وثائق مرفقة.</p>
                    @else
                        <div class="flex flex-wrap gap-2">
                            @foreach($docs as $docKey => $docInfo)
                                <a href="{{ route('provider.profile.document', $docKey) }}" target="_blank"
                                   class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-100 border border-slate-200 text-slate-800 text-xs font-bold hover:bg-slate-200">
                                    {{ $docInfo['label'] }} ↗
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Contact Admin for Edit Requests -->
                <div class="mt-5 p-4 rounded-xl bg-amber-50 border border-amber-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="text-xs text-amber-950 font-bold leading-relaxed">
                        تحتاج تعديل بياناتك الأساسية أو نوع الخدمة أو الحساب البنكي؟ تواصل مع الإدارة ليتم التعديل بعد التحقق.
                    </div>
                    @if($waEditUrl)
                        <a href="{{ $waEditUrl }}" target="_blank"
                           class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-black text-white text-xs font-extrabold text-center shrink-0">
                            طلب تعديل من الإدارة
                        </a>
                    @else
                        <a href="{{ route('provider.subscription') }}"
                           class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-black text-white text-xs font-extrabold text-center shrink-0">
                            التواصل مع الإدارة
                        </a>
                    @endif
                </div>
            </div>

            <!-- Change Password (ONLY self-service editable security control) -->
            <div class="p-card">
                <h2 class="text-base font-black text-slate-900 mb-4">تغيير كلمة المرور</h2>
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

        <!-- Subscription Quick Card -->
        <div class="space-y-5">
            <div class="p-card">
                <h3 class="text-sm font-black text-slate-900 mb-3">حالة الاشتراك الشهري</h3>
                <div class="p-3.5 rounded-xl {{ $provider->hasActiveSubscription() ? 'bg-emerald-50 border border-emerald-200' : 'bg-amber-50 border border-amber-200' }}">
                    <div class="text-sm font-black {{ $provider->hasActiveSubscription() ? 'text-emerald-800' : 'text-amber-900' }}">
                        {{ $provider->subscriptionStatusLabel() }}
                    </div>
                    @if($provider->subscription_ends_at)
                        <div class="text-xs text-slate-700 mt-1">
                            ينتهي في: <strong>{{ $provider->subscription_ends_at->format('Y-m-d') }}</strong>
                        </div>
                    @endif
                    <a href="{{ route('provider.subscription') }}"
                       class="mt-3 block text-center py-2 px-3 rounded-lg bg-[#141311] text-[#e5cfa0] text-xs font-extrabold">
                        {{ $provider->hasActiveSubscription() ? 'عرض الباقات وتجديد الاشتراك' : 'عرض الأسعار والتواصل للاشتراك' }}
                    </a>
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

            <!-- Logout Card -->
            <div class="p-card border-rose-200">
                <h3 class="text-sm font-black text-slate-900 mb-2">تسجيل الخروج من الحساب</h3>
                <p class="text-xs text-slate-500 mb-3">يمكنك إنهاء جلستك الحالية بأمان من هنا أو من الزر العلوي في أي صفحة.</p>
                <form method="POST" action="{{ route('provider.logout') }}">
                    @csrf
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-extrabold transition">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        تسجيل الخروج الآن
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
