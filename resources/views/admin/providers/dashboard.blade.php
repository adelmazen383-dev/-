<x-app-layout>
    <x-slot name="header">بوابة مقدمي الخدمات</x-slot>

    <div class="space-y-4">
        <!-- Slim Executive Action Header (Deep Emerald Thiqa Identity) -->
        <div class="rounded-xl bg-[#064e3b] border border-emerald-700/40 text-white px-4 py-3 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <span class="w-9 h-9 rounded-lg bg-white/10 border border-emerald-400/25 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </span>
                <div class="min-w-0">
                    <h2 class="text-sm sm:text-base font-black tracking-tight truncate">إدارة مقدمي الخدمات والاشتراكات والمحافظ</h2>
                    <p class="text-[11.5px] text-emerald-200/80 hidden sm:block">متابعة الوثائق، تفعيل الاشتراكات الشهرية، الفواتير، وتسوية المحافظ</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-1.5 shrink-0">
                <button type="button"
                        onclick="navigator.clipboard.writeText('{{ route('provider.register') }}').then(() => alert('تم نسخ رابط تسجيل مقدمي الخدمات'))"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 border border-white/15 text-white text-xs font-bold transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    رابط التسجيل
                </button>
                <a href="{{ route('admin.providers.subscription-settings.index') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 border border-white/15 text-white text-xs font-bold transition">
                    الأسعار والتواصل
                </a>
                <a href="{{ route('login') }}" target="_blank"
                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-emerald-500 hover:bg-emerald-400 text-slate-950 text-xs font-black transition">
                    بوابة الدخول
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
            </div>
        </div>

        <!-- Paid Subscription Orders Awaiting Admin Approval -->
        @if($pendingApprovalOrders->isNotEmpty())
            <div class="bg-amber-50/90 rounded-xl border-2 border-amber-400 overflow-hidden shadow-xs">
                <div class="px-4 py-3 border-b border-amber-200 flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse"></span>
                        <h3 class="text-xs sm:text-sm font-black text-amber-950">
                            طلبات اشتراك مدفوعة إلكترونياً بانتظار موافقتك ({{ $pendingApprovalOrders->count() }})
                        </h3>
                    </div>
                    <span class="text-[11px] font-bold text-amber-800">راجع الوثائق ثم اضغط موافقة للتفعيل الفوري</span>
                </div>
                <div class="divide-y divide-amber-200/70 bg-white">
                    @foreach($pendingApprovalOrders as $ord)
                        <div class="px-4 py-3 flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <a href="{{ route('admin.providers.show', $ord->provider) }}" class="text-sm font-black text-slate-900 hover:text-emerald-700">
                                        {{ $ord->provider?->business_name }}
                                    </a>
                                    <span class="text-xs text-slate-500">({{ $ord->provider?->name }})</span>
                                    <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-black tabular-nums">
                                        مدفوع {{ number_format((float) $ord->amount, 2) }} ر.س
                                    </span>
                                </div>
                                <div class="text-[11.5px] text-slate-600 mt-0.5">
                                    الباقة: <strong>{{ $ord->plan_name }}</strong> ({{ $ord->duration_months }} شهر) ·
                                    {{ $ord->paymentMethodLabel() }} ·
                                    رقم الطلب: <span class="font-mono font-bold">{{ $ord->order_number }}</span>
                                    @if($ord->paid_at) · وقت الدفع: {{ $ord->paid_at->format('Y-m-d H:i') }} @endif
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-2 shrink-0">
                                <a href="{{ route('admin.providers.show', $ord->provider) }}" class="btn btn-ghost btn-sm">
                                    مراجعة الوثائق
                                </a>
                                <form method="POST" action="{{ route('admin.providers.sub-orders.approve', $ord) }}"
                                      onsubmit="return confirm('تأكيد الموافقة وتفعيل اشتراك {{ $ord->provider?->business_name }} ({{ $ord->plan_name }}) الآن؟')">
                                    @csrf
                                    <button type="submit" class="btn btn-emerald btn-sm">
                                        موافقة وتفعيل الاشتراك
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.providers.sub-orders.reject', $ord) }}"
                                      onsubmit="return confirm('هل أنت متأكد من رفض طلب الاشتراك هذا؟')">
                                    @csrf
                                    <button type="submit" class="btn btn-rose btn-sm">
                                        رفض
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Unified Compact Executive Metrics Strip (Zero Bulky Squares) -->
        <div class="bg-white rounded-xl border border-slate-200/90 shadow-xs overflow-hidden">
            <div class="grid grid-cols-2 lg:grid-cols-4 divide-y lg:divide-y-0 divide-slate-100">
                <a href="{{ route('admin.providers.index') }}" class="px-4 py-3.5 hover:bg-slate-50/80 transition block border-l border-slate-100">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-[11.5px] font-bold text-slate-500">مقدمو الخدمات</span>
                        <span class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </span>
                    </div>
                    <div class="text-xl font-black text-slate-900 mt-1 tabular-nums">{{ $totalProviders }}</div>
                    <div class="text-[11px] font-semibold text-slate-500 mt-0.5">
                        <span class="text-emerald-700">نشط {{ $activeSubscribers }}</span>
                        <span class="mx-1 text-slate-300">|</span>
                        <span>غير نشط {{ $pendingSubscribers }}</span>
                    </div>
                </a>

                <a href="{{ route('admin.providers.wallets.index') }}" class="px-4 py-3.5 hover:bg-emerald-50/40 transition block lg:border-l border-slate-100">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-[11.5px] font-bold text-slate-500">مستحقات المحافظ</span>
                        <span class="w-7 h-7 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </span>
                    </div>
                    <div class="text-xl font-black text-slate-900 mt-1 tabular-nums">
                        {{ number_format($totalWalletOwed, 2) }} <span class="text-[11px] font-bold text-slate-400">ر.س</span>
                    </div>
                    <div class="text-[11px] font-bold text-emerald-700 mt-0.5">تسوية وتصفير المحافظ ←</div>
                </a>

                <div class="px-4 py-3.5 border-l border-slate-100">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-[11.5px] font-bold text-slate-500">تم تحويله للمحافظ</span>
                        <span class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                    </div>
                    <div class="text-xl font-black text-emerald-700 mt-1 tabular-nums">
                        {{ number_format($totalSettled, 2) }} <span class="text-[11px] font-bold text-emerald-500">ر.س</span>
                    </div>
                    <div class="text-[11px] font-semibold text-slate-500 mt-0.5 tabular-nums">إجمالي المدفوع: {{ number_format($paidVolume, 2) }} ر.س</div>
                </div>

                <a href="{{ route('admin.providers.invoices.index') }}" class="px-4 py-3.5 hover:bg-slate-50/80 transition block">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-[11.5px] font-bold text-slate-500">إجمالي الفواتير</span>
                        <span class="w-7 h-7 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </span>
                    </div>
                    <div class="text-xl font-black text-slate-900 mt-1 tabular-nums">{{ $totalInvoices }}</div>
                    <div class="text-[11px] font-semibold mt-0.5">
                        <span class="text-emerald-700">مدفوعة {{ $paidInvoices }}</span>
                        <span class="mx-1 text-slate-300">|</span>
                        <span class="text-amber-600">معلقة {{ $pendingInvoices }}</span>
                    </div>
                </a>
            </div>
        </div>

        <!-- Compact Sector Filter Pills Bar (Replaces 4 Bulky Cards) -->
        <div class="flex items-center gap-2 overflow-x-auto pb-0.5">
            <span class="text-[11.5px] font-extrabold text-slate-500 shrink-0 pl-1">القطاعات:</span>
            <a href="{{ route('admin.providers.index') }}"
               class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#064e3b] text-white text-xs font-bold shrink-0 shadow-xs">
                الكل
                <span class="px-1.5 py-0.2 rounded-full bg-white/20 text-[11px] font-black tabular-nums">{{ $totalProviders }}</span>
            </a>
            @foreach($byServiceType as $item)
                <a href="{{ route('admin.providers.index', ['service_type' => $item['enum']->value]) }}"
                   class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white hover:bg-emerald-50 border border-slate-200 hover:border-emerald-300 text-slate-700 hover:text-emerald-900 text-xs font-bold shrink-0 transition">
                    <span>{{ $item['enum']->label() }}</span>
                    <span class="px-1.5 py-0.2 rounded-full {{ $item['count'] > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500' }} text-[11px] font-black tabular-nums">{{ $item['count'] }}</span>
                </a>
            @endforeach
        </div>

        <!-- Actionable Lists: Wallets + Recent Providers -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <!-- Wallets Ready for Payout -->
            <div class="bg-white rounded-xl border border-slate-200/90 shadow-xs overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between gap-2">
                    <h3 class="text-xs sm:text-sm font-black text-slate-900">محافظ جاهزة للتحويل والتصفير</h3>
                    <a href="{{ route('admin.providers.wallets.index') }}" class="text-xs font-bold text-emerald-700 hover:underline">مركز التسويات ←</a>
                </div>
                @if($providersWithBalance->isEmpty())
                    <div class="px-4 py-7 text-center text-xs font-semibold text-slate-400">
                        جميع المحافظ مصفّرة حالياً (0.00 ر.س)
                    </div>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach($providersWithBalance as $p)
                            <div class="px-4 py-3 flex items-center justify-between gap-3 hover:bg-slate-50/70 transition">
                                <div class="min-w-0">
                                    <a href="{{ route('admin.providers.show', $p) }}" class="text-xs sm:text-sm font-extrabold text-slate-900 hover:text-emerald-700 truncate block">
                                        {{ $p->business_name }}
                                    </a>
                                    <div class="text-[11px] text-slate-500 truncate mt-0.5">{{ $p->service_type_label }} · {{ $p->iban ?: 'بدون IBAN' }}</div>
                                </div>
                                <div class="text-left shrink-0">
                                    <div class="text-sm font-black text-emerald-700 tabular-nums">{{ number_format((float) $p->wallet_balance, 2) }} ر.س</div>
                                    <a href="{{ route('admin.providers.show', $p) }}" class="text-[11px] font-bold text-slate-600 hover:text-emerald-700">تحويل وتصفير ←</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Recent Service Providers -->
            <div class="bg-white rounded-xl border border-slate-200/90 shadow-xs overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between gap-2">
                    <h3 class="text-xs sm:text-sm font-black text-slate-900">أحدث مقدمي الخدمات المسجلين</h3>
                    <a href="{{ route('admin.providers.index') }}" class="text-xs font-bold text-emerald-700 hover:underline">عرض الكل ←</a>
                </div>
                @if($recentProviders->isEmpty())
                    <div class="px-4 py-7 text-center text-xs font-semibold text-slate-400">
                        لا يوجد مقدمو خدمات مسجلون بعد
                    </div>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach($recentProviders as $p)
                            <a href="{{ route('admin.providers.show', $p) }}" class="px-4 py-3 flex items-center justify-between gap-3 hover:bg-slate-50/80 transition">
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="w-8 h-8 rounded-lg bg-emerald-50 border border-emerald-200/70 text-emerald-800 text-xs font-black flex items-center justify-center shrink-0">
                                        {{ mb_substr($p->business_name ?: $p->name, 0, 1) }}
                                    </span>
                                    <div class="min-w-0">
                                        <div class="text-xs sm:text-sm font-extrabold text-slate-900 truncate">
                                            {{ $p->business_name }}
                                            <span class="text-[11px] font-normal text-slate-400">({{ $p->name }})</span>
                                        </div>
                                        <div class="text-[11px] text-slate-500 truncate">{{ $p->service_type_label }} · <span dir="ltr">{{ $p->phone }}</span></div>
                                    </div>
                                </div>
                                <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold shrink-0 {{ $p->hasActiveSubscription() ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-amber-50 text-amber-800 border border-amber-200' }}">
                                    {{ $p->subscriptionStatusLabel() }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Provider Invoices -->
        <div class="bg-white rounded-xl border border-slate-200/90 shadow-xs overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between gap-2">
                <h3 class="text-xs sm:text-sm font-black text-slate-900">أحدث فواتير مقدمي الخدمات</h3>
                <a href="{{ route('admin.providers.invoices.index') }}" class="text-xs font-bold text-emerald-700 hover:underline">كل الفواتير ←</a>
            </div>
            @if($recentInvoices->isEmpty())
                <div class="px-4 py-7 text-center text-xs font-semibold text-slate-400">
                    لا توجد فواتير منشأة من مقدمي الخدمات حتى الآن
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>رقم الفاتورة</th>
                                <th>مقدم الخدمة</th>
                                <th>العميل</th>
                                <th>صافي المحفظة</th>
                                <th>الحالة</th>
                                <th>التاريخ</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentInvoices as $inv)
                                <tr>
                                    <td class="font-mono text-xs font-bold text-emerald-800">{{ $inv->invoice_number }}</td>
                                    <td class="font-bold text-slate-800">{{ $inv->provider?->business_name }}</td>
                                    <td>{{ $inv->customer_name }}</td>
                                    <td class="font-black tabular-nums">{{ number_format((float) $inv->amount, 2) }} ر.س</td>
                                    <td>
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $inv->isPaid() ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : ($inv->status === 'failed' ? 'bg-rose-50 text-rose-800 border border-rose-200' : 'bg-amber-50 text-amber-800 border border-amber-200') }}">
                                            {{ $inv->statusLabel() }}
                                        </span>
                                    </td>
                                    <td class="text-xs text-slate-500 whitespace-nowrap">{{ $inv->created_at->format('Y-m-d H:i') }}</td>
                                    <td>
                                        <a href="{{ route('admin.providers.invoices.show', $inv) }}" class="btn btn-ghost btn-sm">عرض</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
