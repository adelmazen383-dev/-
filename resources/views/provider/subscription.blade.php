@extends('layouts.provider', ['title' => 'الاشتراك الشهري والباقات', 'header' => 'الاشتراك الشهري والباقات'])

@section('content')
    @php
        $activeSub = $provider->hasActiveSubscription();
        $enabledPlans = array_filter($subConfig['plans'], fn ($p) => ! empty($p['enabled']) && (float) ($p['price'] ?? 0) > 0);
    @endphp

    <div class="space-y-5">
        <!-- Paid & Awaiting Admin Approval Banner -->
        @if($pendingApprovalOrder)
            <div class="rounded-xl p-4 sm:p-5 bg-emerald-50 border-2 border-emerald-500 text-emerald-950 shadow-xs">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-emerald-600 text-white text-[11px] font-black mb-1.5">
                            تم استلام الدفع — بانتظار موافقة الإدارة
                        </span>
                        <h2 class="text-sm sm:text-base font-black text-emerald-950">
                            تم سداد اشتراكك ({{ $pendingApprovalOrder->plan_name }}) بمبلغ {{ number_format((float) $pendingApprovalOrder->amount, 2) }} ر.س بنجاح
                        </h2>
                        <p class="text-xs text-emerald-800 mt-1 leading-relaxed">
                            رقم الطلب: <strong class="font-mono">{{ $pendingApprovalOrder->order_number }}</strong> —
                            طلبك الآن قيد المراجعة لدى الإدارة، وبمجرد الموافقة سيتم تفعيل اشتراكك وفتح إصدار الفواتير فوراً.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Current Subscription Status Strip -->
        <div class="rounded-xl p-4 sm:p-5 bg-[#064e3b] border border-emerald-700/40 text-white shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <div class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-extrabold {{ $activeSub ? 'bg-emerald-400/20 text-emerald-100' : 'bg-amber-400/20 text-amber-200' }} mb-1.5">
                        الحالة الحالية: {{ $provider->subscriptionStatusLabel() }}
                    </div>
                    <h2 class="text-base sm:text-lg font-black">
                        @if($activeSub)
                            اشتراكك نشط حتى {{ $provider->subscription_ends_at->format('Y-m-d') }} (متبقي {{ $provider->subscriptionDaysRemaining() }} يوم)
                        @elseif($pendingApprovalOrder)
                            تم سداد الاشتراك وبانتظار اعتماد الإدارة للتفعيل
                        @else
                            اختر باقتك واشترك مباشرة بالدفع الإلكتروني
                        @endif
                    </h2>
                    <p class="text-xs sm:text-sm text-emerald-100/85 mt-1 leading-relaxed">
                        ادفع قيمة الباقة مباشرة عبر Apple Pay أو مدى أو البطاقات البنكية أو تمارا، وسيتم تفعيل اشتراكك فور موافقة الإدارة.
                    </p>
                </div>
            </div>
        </div>

        <!-- Subscription Plans with Direct "اشترك الآن" Checkout -->
        <div>
            <div class="flex items-center justify-between gap-2 mb-3">
                <h3 class="text-base font-black text-slate-900">باقات الاشتراك المتاحة</h3>
                <span class="text-[11px] font-bold text-slate-500">الأسعار محدّثة مباشرة من الإدارة</span>
            </div>

            @if(empty($enabledPlans))
                <div class="p-card text-center text-sm text-slate-500 py-8">
                    لا توجد باقات مفعّلة للدفع الإلكتروني حالياً. يُرجى التواصل مع الإدارة.
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($enabledPlans as $plan)
                        @php
                            $planPrice = (float) $plan['price'];
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
                                    <span class="text-3xl font-black text-emerald-700 tabular-nums">{{ number_format($planPrice, 2) }}</span>
                                    <span class="text-xs font-bold text-slate-500">ر.س</span>
                                </div>

                                @if(! empty($subConfig['features_list']))
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

                            <div class="space-y-2 pt-2">
                                @if($pendingApprovalOrder)
                                    <div class="w-full text-center py-2.5 px-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 font-extrabold text-xs">
                                        طلبك المدفوع بانتظار موافقة الإدارة
                                    </div>
                                @elseif($telrReady)
                                    <form method="POST" action="{{ route('provider.subscription.checkout') }}">
                                        @csrf
                                        <input type="hidden" name="duration_months" value="{{ $plan['months'] }}">
                                        <input type="hidden" name="method" value="telr">
                                        <button type="submit"
                                                class="w-full py-3 px-4 rounded-xl bg-[#064e3b] hover:bg-emerald-800 text-white font-black text-xs sm:text-sm shadow-sm transition cursor-pointer">
                                            اشترك الآن — ادفع {{ number_format($planPrice, 2) }} ر.س
                                        </button>
                                    </form>

                                    @if($tamaraReady && $planPrice >= 100)
                                        <form method="POST" action="{{ route('provider.subscription.checkout') }}">
                                            @csrf
                                            <input type="hidden" name="duration_months" value="{{ $plan['months'] }}">
                                            <input type="hidden" name="method" value="tamara">
                                            <button type="submit"
                                                    class="w-full py-2.5 px-4 rounded-xl bg-amber-50 hover:bg-amber-100 border border-amber-300 text-amber-950 font-extrabold text-xs transition cursor-pointer">
                                                أو اشترك بالتقسيط عبر تمارا (4 دفعات × {{ number_format($planPrice / 4, 2) }} ر.س)
                                            </button>
                                        </form>
                                    @endif

                                    <p class="text-[11px] text-slate-500 text-center pt-0.5">
                                        دفع إلكتروني مباشر (Apple Pay · مدى · فيزا · ماستركارد) — يتفعل اشتراكك فور موافقة الإدارة
                                    </p>
                                @elseif($tamaraReady && $planPrice >= 100)
                                    <form method="POST" action="{{ route('provider.subscription.checkout') }}">
                                        @csrf
                                        <input type="hidden" name="duration_months" value="{{ $plan['months'] }}">
                                        <input type="hidden" name="method" value="tamara">
                                        <button type="submit"
                                                class="w-full py-3 px-4 rounded-xl bg-[#064e3b] hover:bg-emerald-800 text-white font-black text-xs sm:text-sm shadow-sm transition cursor-pointer">
                                            اشترك الآن عبر تمارا — {{ number_format($planPrice, 2) }} ر.س
                                        </button>
                                    </form>
                                @else
                                    <div class="w-full text-center py-2.5 px-4 rounded-xl bg-slate-100 text-slate-600 font-bold text-xs">
                                        الدفع الإلكتروني غير مفعّل حالياً — تواصل مع الإدارة أدناه
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Subscription Payment Orders Log -->
        @if($subOrders->isNotEmpty())
            <div class="bg-white rounded-xl border border-slate-200/90 overflow-hidden shadow-xs">
                <div class="px-4 py-3 border-b border-slate-100">
                    <h3 class="text-sm font-black text-slate-900">سجل طلبات ومدفوعات الاشتراك</h3>
                </div>
                <div class="divide-y divide-slate-100 text-xs">
                    @foreach($subOrders as $ord)
                        <div class="px-4 py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-mono font-black text-emerald-800">{{ $ord->order_number }}</span>
                                    <span class="font-extrabold text-slate-900">{{ $ord->plan_name }}</span>
                                    <span class="font-black text-emerald-700 tabular-nums">{{ number_format((float) $ord->amount, 2) }} ر.س</span>
                                </div>
                                <div class="text-[11px] text-slate-500 mt-0.5">
                                    {{ $ord->paymentMethodLabel() }} · {{ $ord->created_at->format('Y-m-d H:i') }}
                                    @if($ord->admin_notes)
                                        · ملاحظة الإدارة: <strong class="text-slate-700">{{ $ord->admin_notes }}</strong>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold
                                    {{ $ord->isApproved() ? 'bg-emerald-100 text-emerald-800' : ($ord->isPaidPendingApproval() ? 'bg-amber-100 text-amber-900' : ($ord->isPendingPayment() ? 'bg-slate-100 text-slate-700' : 'bg-rose-100 text-rose-800')) }}">
                                    {{ $ord->statusLabel() }}
                                </span>
                                @if($ord->isPendingPayment())
                                    <form method="POST" action="{{ route('provider.subscription.check-status', $ord) }}">
                                        @csrf
                                        <button type="submit" class="px-3 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-emerald-800 text-[11px] font-extrabold">
                                            تحديث حالة الدفع
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Optional Support & Contact Channels -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div class="p-card">
                <h3 class="text-sm font-black text-slate-900 mb-2">للاستفسار أو الدعم الفني</h3>
                @if(! empty($subConfig['contact_note']))
                    <p class="text-xs text-slate-500 mb-3">{{ $subConfig['contact_note'] }}</p>
                @endif
                <div class="space-y-2 text-xs">
                    @if($waSubscribeUrl)
                        <div class="flex items-center justify-between gap-2 p-2.5 rounded-xl bg-emerald-50 border border-emerald-200">
                            <div>
                                <div class="font-extrabold text-emerald-950">واتساب الإدارة</div>
                                <div class="font-mono text-emerald-800 text-[11px]" dir="ltr">{{ $subConfig['whatsapp'] }}</div>
                            </div>
                            <a href="{{ $waSubscribeUrl }}" target="_blank"
                               class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-extrabold shrink-0">
                                مراسلة
                            </a>
                        </div>
                    @endif
                    @if(! empty($subConfig['phone']))
                        <div class="flex items-center justify-between gap-2 p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                            <div>
                                <div class="font-extrabold text-slate-900">اتصال هاتفي</div>
                                <div class="font-mono text-slate-600 text-[11px]" dir="ltr">{{ $subConfig['phone'] }}</div>
                            </div>
                            <a href="tel:{{ $subConfig['phone'] }}" class="px-3 py-1.5 rounded-lg bg-slate-900 text-white text-xs font-extrabold shrink-0">اتصال</a>
                        </div>
                    @endif
                    @if(! empty($subConfig['email']))
                        <div class="flex items-center justify-between gap-2 p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                            <div>
                                <div class="font-extrabold text-slate-900">البريد الإلكتروني</div>
                                <div class="font-mono text-slate-600 text-[11px]" dir="ltr">{{ $subConfig['email'] }}</div>
                            </div>
                            <a href="mailto:{{ $subConfig['email'] }}" class="px-3 py-1.5 rounded-lg bg-emerald-700 text-white text-xs font-extrabold shrink-0">إرسال</a>
                        </div>
                    @endif
                </div>
            </div>

            @if(! empty($subConfig['bank_iban']) || ! empty($subConfig['bank_name']))
                <div class="p-card">
                    <h3 class="text-sm font-black text-slate-900 mb-3">بيانات التحويل البنكي (بديل اختياري)</h3>
                    <div class="space-y-2 text-xs">
                        @if(! empty($subConfig['bank_name']))
                            <div class="p-2.5 rounded-lg bg-slate-50">
                                <span class="text-slate-400 block">اسم البنك</span>
                                <strong class="text-slate-900">{{ $subConfig['bank_name'] }}</strong>
                            </div>
                        @endif
                        @if(! empty($subConfig['bank_account']))
                            <div class="p-2.5 rounded-lg bg-slate-50">
                                <span class="text-slate-400 block">اسم المستفيد</span>
                                <strong class="text-slate-900">{{ $subConfig['bank_account'] }}</strong>
                            </div>
                        @endif
                        @if(! empty($subConfig['bank_iban']))
                            <div class="p-2.5 rounded-lg bg-slate-50 flex items-center justify-between gap-2">
                                <div class="min-w-0">
                                    <span class="text-slate-400 block">رقم الآيبان (IBAN)</span>
                                    <strong class="font-mono text-emerald-800 break-all" dir="ltr">{{ $subConfig['bank_iban'] }}</strong>
                                </div>
                                <button type="button" onclick="navigator.clipboard.writeText('{{ $subConfig['bank_iban'] }}').then(() => alert('تم نسخ رقم الآيبان'))"
                                        class="px-3 py-1.5 rounded-lg bg-slate-200 hover:bg-slate-300 text-slate-800 text-xs font-bold shrink-0">
                                    نسخ
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Active/Past Approved Subscriptions History -->
        @if($subscriptions->isNotEmpty())
            <div class="p-card">
                <h3 class="text-sm font-black text-slate-900 mb-3">سجل اشتراكاتي المفعّلة</h3>
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
