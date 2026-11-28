@extends('layouts.provider', ['title' => 'الاشتراك والتواصل مع الإدارة', 'header' => 'الاشتراك الشهري والتواصل مع الإدارة'])

@section('content')
    @php
        $activeSub = $provider->hasActiveSubscription();
        $enabledPlans = array_filter($subConfig['plans'], fn ($p) => !empty($p['enabled']));
    @endphp

    <div class="space-y-6">
        <!-- Current Subscription Status Card -->
        <div class="rounded-xl p-4 sm:p-5 bg-[#064e3b] border border-emerald-700/40 text-white shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <div class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-extrabold {{ $activeSub ? 'bg-emerald-400/20 text-emerald-100' : 'bg-amber-400/20 text-amber-200' }} mb-1.5">
                        الحالة الحالية: {{ $provider->subscriptionStatusLabel() }}
                    </div>
                    <h2 class="text-base sm:text-lg font-black">
                        @if($activeSub)
                            اشتراكك نشط حتى {{ $provider->subscription_ends_at->format('Y-m-d') }} (متبقي {{ $provider->subscriptionDaysRemaining() }} يوم)
                        @else
                            تفعيل الاشتراك الشهري لبدء إصدار الفواتير
                        @endif
                    </h2>
                    @if(!empty($subConfig['contact_note']))
                        <p class="text-xs sm:text-sm text-emerald-100/85 mt-1 leading-relaxed">{{ $subConfig['contact_note'] }}</p>
                    @endif
                </div>

                @if($waSubscribeUrl)
                    <a href="{{ $waSubscribeUrl }}" target="_blank"
                       class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-emerald-400 hover:bg-emerald-300 text-slate-950 font-black text-xs sm:text-sm shadow transition shrink-0">
                        تواصل مع الإدارة عبر واتساب
                    </a>
                @endif
            </div>
        </div>

        <!-- Subscription Plans & Prices -->
        <div>
            <h3 class="text-base font-black text-slate-900 mb-3">باقات الاشتراك والأسعار</h3>

            @if(empty($enabledPlans))
                <div class="p-card text-center text-sm text-slate-500 py-8">
                    تواصل مع الإدارة مباشرة لمعرفة تفاصيل وأسعار الاشتراك الشهري وتفعيل حسابك.
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($enabledPlans as $plan)
                        @php
                            $planWaUrl = \App\Services\ProviderSubscriptionConfigService::buildSubscribeWhatsAppUrl($provider, $plan);
                        @endphp
                        <div class="p-card border {{ $plan['months'] === 1 ? 'border-emerald-500 ring-1 ring-emerald-500/20' : 'border-slate-200' }} flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between gap-2 mb-2">
                                    <h4 class="text-base font-black text-slate-900">{{ $plan['label'] }}</h4>
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-800">
                                        {{ $plan['months'] === 1 ? 'شهر واحد' : $plan['months'] . ' أشهر' }}
                                    </span>
                                </div>

                                <div class="my-3">
                                    <span class="text-3xl font-black text-emerald-700 tabular-nums">{{ number_format((float) $plan['price'], 2) }}</span>
                                    <span class="text-xs font-bold text-slate-500">ر.س</span>
                                </div>

                                @if(!empty($subConfig['features_list']))
                                    <ul class="space-y-1.5 text-xs text-slate-600 mt-3 mb-5 border-t border-slate-100 pt-3">
                                        @foreach($subConfig['features_list'] as $feat)
                                            <li class="flex items-start gap-2">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mt-1.5 shrink-0"></span>
                                                <span>{{ $feat }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>

                            @if($planWaUrl)
                                <a href="{{ $planWaUrl }}" target="_blank"
                                   class="w-full text-center py-2.5 px-4 rounded-xl bg-[#064e3b] hover:bg-emerald-800 text-white font-black text-xs sm:text-sm transition">
                                    طلب تفعيل هذه الباقة عبر واتساب
                                </a>
                            @elseif(!empty($subConfig['phone']))
                                <a href="tel:{{ $subConfig['phone'] }}"
                                   class="w-full text-center py-2.5 px-4 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-black text-xs sm:text-sm transition">
                                    اتصل بالإدارة للاشتراك ({{ $subConfig['phone'] }})
                                </a>
                            @elseif(!empty($subConfig['email']))
                                <a href="mailto:{{ $subConfig['email'] }}"
                                   class="w-full text-center py-2.5 px-4 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-black text-xs sm:text-sm transition">
                                    مراسلة الإدارة عبر البريد
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Direct Contact & Subscription Payment Bank Info -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <div class="p-card">
                <h3 class="text-sm font-black text-slate-900 mb-3">قنوات التواصل المباشر مع الإدارة</h3>
                <div class="space-y-2.5 text-xs sm:text-sm">
                    @if($waSubscribeUrl)
                        <div class="flex items-center justify-between gap-2 p-3 rounded-xl bg-emerald-50 border border-emerald-200">
                            <div>
                                <div class="font-extrabold text-emerald-950">واتساب تفعيل الاشتراكات</div>
                                <div class="font-mono text-emerald-800 text-xs mt-0.5" dir="ltr">{{ $subConfig['whatsapp'] }}</div>
                            </div>
                            <a href="{{ $waSubscribeUrl }}" target="_blank"
                               class="px-3.5 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-extrabold shrink-0">
                                محادثة واتساب
                            </a>
                        </div>
                    @endif

                    @if(!empty($subConfig['phone']))
                        <div class="flex items-center justify-between gap-2 p-3 rounded-xl bg-slate-50 border border-slate-200">
                            <div>
                                <div class="font-extrabold text-slate-900">الاتصال الهاتفي المباشر</div>
                                <div class="font-mono text-slate-600 text-xs mt-0.5" dir="ltr">{{ $subConfig['phone'] }}</div>
                            </div>
                            <a href="tel:{{ $subConfig['phone'] }}"
                               class="px-3.5 py-2 rounded-lg bg-slate-900 text-white text-xs font-extrabold shrink-0">
                                اتصال الآن
                            </a>
                        </div>
                    @endif

                    @if(!empty($subConfig['email']))
                        <div class="flex items-center justify-between gap-2 p-3 rounded-xl bg-slate-50 border border-slate-200">
                            <div>
                                <div class="font-extrabold text-slate-900">البريد الإلكتروني للإدارة</div>
                                <div class="font-mono text-slate-600 text-xs mt-0.5" dir="ltr">{{ $subConfig['email'] }}</div>
                            </div>
                            <a href="mailto:{{ $subConfig['email'] }}"
                               class="px-3.5 py-2 rounded-lg bg-indigo-600 text-white text-xs font-extrabold shrink-0">
                                إرسال بريد
                            </a>
                        </div>
                    @endif

                    @if(!$waSubscribeUrl && empty($subConfig['phone']) && empty($subConfig['email']))
                        <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs font-bold">
                            لم يتم إضافة أرقام التواصل من الإدارة بعد. يُرجى التواصل مع إدارة منصة ثقة لتفعيل اشتراكك.
                        </div>
                    @endif
                </div>
            </div>

            <div class="p-card">
                <h3 class="text-sm font-black text-slate-900 mb-3">بيانات التحويل البنكي للاشتراك</h3>
                @if(!empty($subConfig['bank_iban']) || !empty($subConfig['bank_name']))
                    <div class="space-y-2.5 text-xs">
                        @if(!empty($subConfig['bank_name']))
                            <div class="p-2.5 rounded-lg bg-slate-50">
                                <span class="text-slate-400 block">اسم البنك</span>
                                <strong class="text-slate-900">{{ $subConfig['bank_name'] }}</strong>
                            </div>
                        @endif
                        @if(!empty($subConfig['bank_account']))
                            <div class="p-2.5 rounded-lg bg-slate-50">
                                <span class="text-slate-400 block">اسم المستفيد</span>
                                <strong class="text-slate-900">{{ $subConfig['bank_account'] }}</strong>
                            </div>
                        @endif
                        @if(!empty($subConfig['bank_iban']))
                            <div class="p-2.5 rounded-lg bg-slate-50 flex items-center justify-between gap-2">
                                <div class="min-w-0">
                                    <span class="text-slate-400 block">رقم الآيبان (IBAN)</span>
                                    <strong class="font-mono text-indigo-800 break-all" dir="ltr">{{ $subConfig['bank_iban'] }}</strong>
                                </div>
                                <button type="button" onclick="navigator.clipboard.writeText('{{ $subConfig['bank_iban'] }}').then(() => alert('تم نسخ رقم الآيبان'))"
                                        class="px-3 py-1.5 rounded-lg bg-slate-200 hover:bg-slate-300 text-slate-800 text-xs font-bold shrink-0">
                                    نسخ
                                </button>
                            </div>
                        @endif
                        <p class="text-[11px] text-slate-500 pt-1">بعد التحويل، أرسل إيصال السداد للإدارة عبر الواتساب لتفعيل اشتراكك فوراً.</p>
                    </div>
                @else
                    <p class="text-xs text-slate-500 py-4">تواصل مع الإدارة عبر الواتساب للحصول على تفاصيل سداد الاشتراك وتفعيل الحساب فوراً.</p>
                @endif
            </div>
        </div>

        <!-- Subscription History -->
        @if($subscriptions->isNotEmpty())
            <div class="p-card">
                <h3 class="text-sm font-black text-slate-900 mb-3">سجل اشتراكاتي</h3>
                <div class="space-y-2 text-xs">
                    @foreach($subscriptions as $sub)
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between gap-3">
                            <div>
                                <div class="font-extrabold text-slate-900">{{ $sub->plan_name }}</div>
                                <div class="text-slate-500 mt-0.5">من {{ $sub->starts_at->format('Y-m-d') }} إلى {{ $sub->ends_at->format('Y-m-d') }}</div>
                            </div>
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $sub->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600' }}">
                                {{ $sub->status === 'active' ? 'نشط' : 'منتهي / ملغي' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection
