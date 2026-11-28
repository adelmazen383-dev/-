@extends('layouts.provider', ['title' => 'لوحة مقدم الخدمة', 'header' => 'لوحة التحكم الرئيسية'])

@section('content')
    @php
        $activeSub = $provider->hasActiveSubscription();
        $subStatus = $provider->effectiveSubscriptionStatus();
    @endphp

    <div class="space-y-4">
        <!-- Slim Executive Subscription Status Strip -->
        @if($activeSub)
            <div class="rounded-xl bg-[#064e3b] border border-emerald-700/40 text-white px-4 py-3.5 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-emerald-400/20 text-emerald-200 text-[11px] font-bold">اشتراك نشط</span>
                        <span class="text-xs text-emerald-200/90">صالح حتى <strong>{{ $provider->subscription_ends_at->format('Y-m-d') }}</strong> (متبقي {{ $provider->subscriptionDaysRemaining() }} يوم)</span>
                    </div>
                    <h2 class="text-sm sm:text-base font-black mt-1 truncate">مرحباً {{ $provider->business_name }} — خدمة إصدار الفواتير مفعّلة</h2>
                </div>
                <div class="flex flex-wrap items-center gap-2 shrink-0">
                    @if($provider->isServicedSuites())
                        <a href="{{ route('provider.contracts.index') }}"
                           class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-lg bg-white/10 hover:bg-white/20 border border-white/20 text-white font-extrabold text-xs sm:text-sm transition">
                            العقود الإلكترونية
                        </a>
                    @endif
                    <a href="{{ route('provider.invoices.create') }}"
                       class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-lg bg-emerald-400 hover:bg-emerald-300 text-slate-950 font-black text-xs sm:text-sm shadow transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        إنشاء فاتورة جديدة
                    </a>
                </div>
            </div>
        @else
            <div class="rounded-xl bg-[#064e3b] border border-emerald-700/50 text-white px-4 py-3.5 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="min-w-0">
                    <div class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-amber-400/20 border border-amber-300/30 text-amber-200 text-[11px] font-bold">
                        {{ $provider->subscriptionStatusLabel() }}
                    </div>
                    <h2 class="text-sm sm:text-base font-black mt-1">
                        @if($subStatus === 'expired')
                            انتهت مدة اشتراكك الشهري
                        @elseif($subStatus === 'suspended')
                            تم إيقاف اشتراكك مؤقتاً
                        @else
                            فعّل اشتراكك الشهري لبدء إصدار الفواتير
                        @endif
                    </h2>
                    <p class="text-xs text-emerald-100/80 mt-0.5 leading-relaxed">
                        {{ $subConfig['contact_note'] ?: 'تواصل مع الإدارة لتفعيل اشتراكك الشهري وفتح خدمة إصدار الفواتير فوراً.' }}
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2 shrink-0">
                    @if(!empty($waSubscribeUrl))
                        <a href="{{ $waSubscribeUrl }}" target="_blank"
                           class="inline-flex items-center justify-center px-3.5 py-2 rounded-lg bg-emerald-400 hover:bg-emerald-300 text-slate-950 font-black text-xs shadow transition">
                            تواصل واتساب للاشتراك
                        </a>
                    @endif
                    <a href="{{ route('provider.subscription') }}"
                       class="inline-flex items-center justify-center px-3.5 py-2 rounded-lg bg-white/10 hover:bg-white/20 border border-white/20 text-white font-extrabold text-xs transition">
                        الباقات والأسعار والتواصل
                    </a>
                </div>
            </div>
        @endif

        <!-- Unified Compact Metrics Bar (Replaces 4 Bulky Cards) -->
        <div class="bg-white rounded-xl border border-slate-200/90 shadow-xs overflow-hidden">
            <div class="grid grid-cols-2 lg:grid-cols-4 divide-y lg:divide-y-0 divide-slate-100">
                <a href="{{ route('provider.wallet') }}" class="px-4 py-3.5 hover:bg-slate-50 transition block border-l border-slate-100">
                    <div class="text-[11.5px] font-bold text-slate-500">رصيد المحفظة الحالي</div>
                    <div class="text-lg sm:text-xl font-black text-emerald-700 mt-0.5 tabular-nums">
                        {{ number_format((float) $provider->wallet_balance, 2) }} <span class="text-[11px] font-bold text-slate-400">ر.س</span>
                    </div>
                    <div class="text-[10.5px] text-slate-400 mt-0.5">جاهز للتحويل لحسابك</div>
                </a>

                <div class="px-4 py-3.5 lg:border-l border-slate-100">
                    <div class="text-[11.5px] font-bold text-slate-500">إجمالي ما تم تحويله</div>
                    <div class="text-lg sm:text-xl font-black text-slate-900 mt-0.5 tabular-nums">
                        {{ number_format((float) $provider->total_settled, 2) }} <span class="text-[11px] font-bold text-slate-400">ر.س</span>
                    </div>
                    <div class="text-[10.5px] text-slate-400 mt-0.5">تم تسويته وتصفيره</div>
                </div>

                <a href="{{ route('provider.invoices.index', ['status' => 'paid']) }}" class="px-4 py-3.5 hover:bg-slate-50 transition block border-l border-slate-100">
                    <div class="text-[11.5px] font-bold text-slate-500">الفواتير المدفوعة</div>
                    <div class="text-lg sm:text-xl font-black text-slate-900 mt-0.5 tabular-nums">{{ $paidInvoices }}</div>
                    <div class="text-[10.5px] text-emerald-700 font-bold mt-0.5">من أصل {{ $totalInvoices }} فاتورة</div>
                </a>

                <a href="{{ route('provider.invoices.index', ['status' => 'pending']) }}" class="px-4 py-3.5 hover:bg-slate-50 transition block">
                    <div class="text-[11.5px] font-bold text-slate-500">بانتظار الدفع</div>
                    <div class="text-lg sm:text-xl font-black text-amber-600 mt-0.5 tabular-nums">{{ $pendingInvoices }}</div>
                    <div class="text-[10.5px] text-slate-400 mt-0.5">معلقة لحين سداد العميل</div>
                </a>
            </div>
        </div>

        <!-- Recent Invoices & Recent Wallet Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200/90 shadow-xs overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-xs sm:text-sm font-black text-slate-900">أحدث الفواتير</h3>
                    <a href="{{ route('provider.invoices.index') }}" class="text-xs font-bold text-emerald-700 hover:underline">عرض الكل ←</a>
                </div>

                @if($recentInvoices->isEmpty())
                    <div class="text-center py-8 px-4 text-slate-400">
                        <p class="text-xs font-bold text-slate-600">لا توجد فواتير بعد</p>
                        <p class="text-[11.5px] mt-1">
                            {{ $activeSub ? 'ابدأ بإنشاء أول فاتورة وأرسل رابط الدفع لعميلك.' : 'فعّل اشتراكك الشهري أولاً لبدء إصدار الفواتير.' }}
                        </p>
                    </div>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach($recentInvoices as $inv)
                            <a href="{{ route('provider.invoices.show', $inv) }}"
                               class="px-4 py-3 flex items-center justify-between gap-3 hover:bg-slate-50/80 transition">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-mono text-xs font-extrabold text-emerald-800">{{ $inv->invoice_number }}</span>
                                        @if($inv->isPaid())
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">مدفوعة</span>
                                        @elseif($inv->status === 'failed')
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-800 border border-rose-200">فشلت</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">بانتظار الدفع</span>
                                        @endif
                                    </div>
                                    <div class="text-xs sm:text-sm font-bold text-slate-900 mt-0.5 truncate">{{ $inv->customer_name }} — {{ $inv->title }}</div>
                                    <div class="text-[10.5px] text-slate-400">{{ $inv->created_at->format('Y-m-d H:i') }}</div>
                                </div>
                                <div class="text-left shrink-0">
                                    <div class="text-sm font-black text-slate-900 tabular-nums">{{ number_format((float) $inv->amount, 2) }} ر.س</div>
                                    @if($inv->isWalletCredited())
                                        <div class="text-[10px] font-bold text-emerald-700">أُضيفت للمحفظة</div>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="bg-white rounded-xl border border-slate-200/90 shadow-xs overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-xs sm:text-sm font-black text-slate-900">حركات المحفظة الأخيرة</h3>
                    <a href="{{ route('provider.wallet') }}" class="text-xs font-bold text-emerald-700 hover:underline">التفاصيل ←</a>
                </div>

                @if($recentTx->isEmpty())
                    <div class="text-center py-8 px-4 text-slate-400 text-xs">
                        لا توجد حركات مالية في المحفظة حتى الآن
                    </div>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach($recentTx as $tx)
                            <div class="px-4 py-3">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-xs font-extrabold {{ $tx->type === 'credit' ? 'text-emerald-700' : 'text-slate-700' }}">
                                        {{ $tx->typeLabel() }}
                                    </span>
                                    <span class="text-xs font-black tabular-nums {{ $tx->type === 'credit' ? 'text-emerald-700' : 'text-rose-700' }}" dir="ltr">
                                        {{ $tx->type === 'credit' ? '+' : '-' }}{{ number_format((float) $tx->amount, 2) }}
                                    </span>
                                </div>
                                @if($tx->notes)
                                    <div class="text-[11px] text-slate-500 mt-0.5 truncate">{{ $tx->notes }}</div>
                                @endif
                                <div class="text-[10px] text-slate-400 mt-0.5">{{ $tx->created_at->format('Y-m-d H:i') }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
