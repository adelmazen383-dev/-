@extends('layouts.provider', ['title' => 'لوحة مقدم الخدمة', 'header' => 'لوحة التحكم الرئيسية'])

@section('content')
    @php
        $activeSub = $provider->hasActiveSubscription();
        $subStatus = $provider->effectiveSubscriptionStatus();
    @endphp

    <!-- Subscription Banner -->
    @if($activeSub)
        <div class="mb-5 rounded-2xl bg-gradient-to-l from-emerald-600 to-teal-700 text-white p-4 sm:p-5 shadow-md flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-white/15 text-xs font-bold mb-1.5">
                    ✅ اشتراك شهري نشط
                </div>
                <h2 class="text-base sm:text-lg font-black">مرحباً {{ $provider->business_name }} — خدمة إصدار الفواتير مفعّلة!</h2>
                <p class="text-xs sm:text-sm text-emerald-100 mt-0.5">
                    صالح حتى <strong>{{ $provider->subscription_ends_at->format('Y-m-d') }}</strong>
                    (متبقي {{ $provider->subscriptionDaysRemaining() }} يوم)
                </p>
            </div>
            <a href="{{ route('provider.invoices.create') }}"
               class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-white text-emerald-800 font-black text-sm shadow hover:bg-emerald-50 transition shrink-0">
                ➕ إنشاء فاتورة جديدة
            </a>
        </div>
    @else
        <div class="mb-5 rounded-2xl bg-gradient-to-l from-amber-500 to-orange-600 text-white p-4 sm:p-5 shadow-md flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-black/20 text-xs font-bold mb-1.5">
                    🔒 {{ $provider->subscriptionStatusLabel() }}
                </div>
                <h2 class="text-base sm:text-lg font-black">
                    @if($subStatus === 'expired')
                        انتهت مدة اشتراكك الشهري
                    @elseif($subStatus === 'suspended')
                        تم إيقاف اشتراكك مؤقتاً
                    @else
                        تفعيل الاشتراك الشهري مطلوب لإصدار الفواتير
                    @endif
                </h2>
                <p class="text-xs sm:text-sm text-amber-50 mt-1 leading-relaxed">
                    بمجرد تفعيل الاشتراك الشهري مع الإدارة، سيتم فتح زر إنشاء الفواتير فوراً وإضافة أرباح الفواتير المدفوعة إلى محفظتك تلقائياً.
                </p>
            </div>
            <a href="{{ route('provider.profile') }}"
               class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-white text-amber-900 font-black text-xs sm:text-sm shadow hover:bg-amber-50 transition shrink-0">
                ⚙️ تفاصيل الاشتراك وبياناتي
            </a>
        </div>
    @endif

    <!-- KPI Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6">
        <a href="{{ route('provider.wallet') }}" class="p-card hover:border-indigo-300 transition block">
            <div class="text-xs font-bold text-slate-500">رصيد المحفظة الحالي</div>
            <div class="text-xl sm:text-2xl font-black text-indigo-700 mt-1">
                {{ number_format((float) $provider->wallet_balance, 2) }} <span class="text-xs font-bold">ر.س</span>
            </div>
            <div class="text-[11px] text-slate-500 mt-1">جاهز للتحويل لحسابك</div>
        </a>

        <div class="p-card">
            <div class="text-xs font-bold text-slate-500">إجمالي ما تم تحويله</div>
            <div class="text-xl sm:text-2xl font-black text-emerald-700 mt-1">
                {{ number_format((float) $provider->total_settled, 2) }} <span class="text-xs font-bold">ر.س</span>
            </div>
            <div class="text-[11px] text-slate-500 mt-1">تم تسويته وتصفيره</div>
        </div>

        <a href="{{ route('provider.invoices.index', ['status' => 'paid']) }}" class="p-card hover:border-emerald-300 transition block">
            <div class="text-xs font-bold text-slate-500">الفواتير المدفوعة</div>
            <div class="text-xl sm:text-2xl font-black text-slate-900 mt-1">{{ $paidInvoices }}</div>
            <div class="text-[11px] text-emerald-600 font-bold mt-1">من أصل {{ $totalInvoices }} فاتورة</div>
        </a>

        <a href="{{ route('provider.invoices.index', ['status' => 'pending']) }}" class="p-card hover:border-amber-300 transition block">
            <div class="text-xs font-bold text-slate-500">فواتير بانتظار الدفع</div>
            <div class="text-xl sm:text-2xl font-black text-amber-600 mt-1">{{ $pendingInvoices }}</div>
            <div class="text-[11px] text-slate-500 mt-1">معلقة لحين سداد العميل</div>
        </a>
    </div>

    <!-- Recent Invoices & Recent Wallet Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="lg:col-span-2 p-card">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm sm:text-base font-black text-slate-900">أحدث الفواتير</h3>
                <a href="{{ route('provider.invoices.index') }}" class="text-xs font-bold text-indigo-600 hover:underline">عرض الكل ←</a>
            </div>

            @if($recentInvoices->isEmpty())
                <div class="text-center py-8 text-slate-400">
                    <div class="text-3xl mb-2">📄</div>
                    <p class="text-sm font-bold text-slate-600">لا توجد فواتير بعد</p>
                    <p class="text-xs mt-1">
                        {{ $activeSub ? 'ابدأ بإنشاء أول فاتورة وأرسل رابط الدفع لعميلك.' : 'فعّل اشتراكك الشهري أولاً لبدء إصدار الفواتير.' }}
                    </p>
                </div>
            @else
                <div class="space-y-2.5">
                    @foreach($recentInvoices as $inv)
                        <a href="{{ route('provider.invoices.show', $inv) }}"
                           class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 p-3 rounded-xl border border-slate-100 hover:border-indigo-200 hover:bg-indigo-50/30 transition">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-mono text-xs font-extrabold text-indigo-700">{{ $inv->invoice_number }}</span>
                                    @if($inv->isPaid())
                                        <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-emerald-100 text-emerald-800">✅ مدفوعة</span>
                                    @elseif($inv->status === 'failed')
                                        <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-rose-100 text-rose-800">❌ فشلت</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-amber-100 text-amber-800">⏳ بانتظار الدفع</span>
                                    @endif
                                </div>
                                <div class="text-sm font-bold text-slate-900 mt-0.5 truncate">{{ $inv->customer_name }} — {{ $inv->title }}</div>
                                <div class="text-[11px] text-slate-400">{{ $inv->created_at->format('Y-m-d H:i') }}</div>
                            </div>
                            <div class="text-left sm:text-left shrink-0">
                                <div class="text-sm font-black text-slate-900">{{ number_format((float) $inv->amount, 2) }} ر.س</div>
                                @if($inv->isWalletCredited())
                                    <div class="text-[10.5px] font-bold text-emerald-600">أُضيفت للمحفظة ✓</div>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="p-card">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm sm:text-base font-black text-slate-900">حركات المحفظة الأخيرة</h3>
                <a href="{{ route('provider.wallet') }}" class="text-xs font-bold text-indigo-600 hover:underline">التفاصيل ←</a>
            </div>

            @if($recentTx->isEmpty())
                <div class="text-center py-8 text-slate-400 text-xs">
                    لا توجد حركات مالية في المحفظة حتى الآن
                </div>
            @else
                <div class="space-y-2.5">
                    @foreach($recentTx as $tx)
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-extrabold {{ $tx->type === 'credit' ? 'text-emerald-700' : 'text-indigo-700' }}">
                                    {{ $tx->type === 'credit' ? '➕ ' : '🏦 ' }}{{ $tx->typeLabel() }}
                                </span>
                                <span class="text-sm font-black {{ $tx->type === 'credit' ? 'text-emerald-700' : 'text-rose-700' }}" dir="ltr">
                                    {{ $tx->type === 'credit' ? '+' : '-' }}{{ number_format((float) $tx->amount, 2) }}
                                </span>
                            </div>
                            <div class="text-[11px] text-slate-500 mt-1">{{ $tx->notes }}</div>
                            <div class="text-[10px] text-slate-400 mt-1">{{ $tx->created_at->format('Y-m-d H:i') }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
