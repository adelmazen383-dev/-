<x-app-layout>
    <x-slot name="header">بوابة مقدمي الخدمات — لوحة المتابعة (قسم مستقل)</x-slot>

    <div class="space-y-6">
        <!-- Isolated Section Header Banner -->
        <div class="rounded-2xl bg-gradient-to-l from-indigo-950 via-slate-900 to-indigo-900 text-white p-5 shadow-md flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-indigo-500/30 border border-indigo-400/30 text-indigo-100 text-[11px] font-bold mb-1.5">
                    🧩 قسم مستقل تماماً عن عقود وفواتير الموقع الأساسية
                </span>
                <h2 class="text-lg sm:text-xl font-black">إدارة مقدمي الخدمات والفواتير والمحافظ والاشتراكات</h2>
                <p class="text-xs sm:text-sm text-slate-300 mt-1">
                    متابعة كاملة لمقدمي الخدمات (تنسيق، تصوير، أجنحة فندقية مخدومة، أخرى)، مراجعة الوثائق، تفعيل الاشتراكات الشهرية، وتسوية المحافظ.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2 shrink-0">
                <button type="button"
                        onclick="navigator.clipboard.writeText('{{ route('provider.register') }}').then(() => alert('تم نسخ رابط تسجيل مقدمي الخدمات!'))"
                        class="px-3.5 py-2 rounded-xl bg-white/15 hover:bg-white/25 text-white text-xs font-extrabold transition">
                    🔗 نسخ رابط التسجيل
                </button>
                <a href="{{ route('provider.login') }}" target="_blank"
                   class="px-3.5 py-2 rounded-xl bg-indigo-500 hover:bg-indigo-400 text-white text-xs font-extrabold transition">
                    بوابة الدخول ↗
                </a>
            </div>
        </div>

        <!-- KPIs -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <a href="{{ route('admin.providers.index') }}" class="stat-card block">
                <div class="text-xs font-bold text-slate-500">إجمالي مقدمي الخدمات</div>
                <div class="text-2xl font-black text-slate-900 mt-1">{{ $totalProviders }}</div>
                <div class="text-[11px] mt-1 font-bold text-emerald-600">نشط: {{ $activeSubscribers }} · غير نشط: {{ $pendingSubscribers }}</div>
            </a>

            <a href="{{ route('admin.providers.wallets.index') }}" class="stat-card block border-indigo-200 bg-indigo-50/30">
                <div class="text-xs font-bold text-indigo-700">أرصدة المحافظ المستحقة للتحويل</div>
                <div class="text-2xl font-black text-indigo-900 mt-1">{{ number_format($totalWalletOwed, 2) }} <span class="text-xs">ر.س</span></div>
                <div class="text-[11px] text-indigo-600 font-bold mt-1">جاهزة للتسوية والتصفير ←</div>
            </a>

            <div class="stat-card">
                <div class="text-xs font-bold text-slate-500">إجمالي ما تم تحويله للمحافظ</div>
                <div class="text-2xl font-black text-emerald-700 mt-1">{{ number_format($totalSettled, 2) }} <span class="text-xs">ر.س</span></div>
                <div class="text-[11px] text-slate-500 mt-1">إجمالي الفواتير المدفوعة: {{ number_format($paidVolume, 2) }} ر.س</div>
            </div>

            <a href="{{ route('admin.providers.invoices.index') }}" class="stat-card block">
                <div class="text-xs font-bold text-slate-500">فواتير مقدمي الخدمات</div>
                <div class="text-2xl font-black text-slate-900 mt-1">{{ $totalInvoices }}</div>
                <div class="text-[11px] font-bold mt-1">
                    <span class="text-emerald-600">مدفوعة: {{ $paidInvoices }}</span> ·
                    <span class="text-amber-600">معلقة: {{ $pendingInvoices }}</span>
                </div>
            </a>
        </div>

        <!-- 4 Service Types Summary -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            @foreach($byServiceType as $item)
                <a href="{{ route('admin.providers.index', ['service_type' => $item['enum']->value]) }}"
                   class="card hover:border-indigo-300 flex items-center justify-between gap-2 py-3.5">
                    <div>
                        <div class="text-xs font-extrabold text-slate-800">{{ $item['enum']->icon() }} {{ $item['enum']->label() }}</div>
                        <div class="text-[11px] text-slate-500 mt-0.5">مقدمي خدمة</div>
                    </div>
                    <div class="text-xl font-black text-indigo-700">{{ $item['count'] }}</div>
                </a>
            @endforeach
        </div>

        <!-- Wallets Ready for Payout + Recent Providers -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <div class="card">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-black text-slate-900">💰 محافظ بها رصيد جاهز للتحويل والتصفير</h3>
                    <a href="{{ route('admin.providers.wallets.index') }}" class="text-xs font-bold text-indigo-600 hover:underline">مركز التسويات ←</a>
                </div>
                @if($providersWithBalance->isEmpty())
                    <p class="text-center py-6 text-xs text-slate-400">جميع المحافظ مصفّرة حالياً (0.00 ر.س)</p>
                @else
                    <div class="space-y-2.5">
                        @foreach($providersWithBalance as $p)
                            <div class="flex items-center justify-between gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100">
                                <div class="min-w-0">
                                    <a href="{{ route('admin.providers.show', $p) }}" class="text-sm font-extrabold text-slate-900 hover:text-indigo-700 truncate block">
                                        {{ $p->business_name }}
                                    </a>
                                    <div class="text-[11px] text-slate-500">{{ $p->service_type_label }} · {{ $p->iban ?: 'بدون IBAN' }}</div>
                                </div>
                                <div class="text-left shrink-0">
                                    <div class="text-sm font-black text-indigo-700">{{ number_format((float) $p->wallet_balance, 2) }} ر.س</div>
                                    <a href="{{ route('admin.providers.show', $p) }}" class="text-[11px] font-bold text-emerald-700 hover:underline">تحويل وتصفير ←</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="card">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-black text-slate-900">🆕 أحدث مقدمي الخدمات المسجلين</h3>
                    <a href="{{ route('admin.providers.index') }}" class="text-xs font-bold text-indigo-600 hover:underline">عرض الكل ←</a>
                </div>
                @if($recentProviders->isEmpty())
                    <p class="text-center py-6 text-xs text-slate-400">لا يوجد مقدمي خدمات مسجلين بعد</p>
                @else
                    <div class="space-y-2.5">
                        @foreach($recentProviders as $p)
                            <a href="{{ route('admin.providers.show', $p) }}" class="flex items-center justify-between gap-3 p-3 rounded-xl border border-slate-100 hover:border-indigo-200 hover:bg-indigo-50/30 transition">
                                <div class="min-w-0">
                                    <div class="text-sm font-extrabold text-slate-900 truncate">{{ $p->business_name }} <span class="text-xs font-normal text-slate-500">({{ $p->name }})</span></div>
                                    <div class="text-[11px] text-slate-500 mt-0.5">{{ $p->serviceTypeEnum()->icon() }} {{ $p->service_type_label }} · {{ $p->phone }}</div>
                                </div>
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold shrink-0 {{ $p->hasActiveSubscription() ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $p->subscriptionStatusLabel() }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Provider Invoices -->
        <div class="card">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-black text-slate-900">🧾 أحدث فواتير مقدمي الخدمات</h3>
                <a href="{{ route('admin.providers.invoices.index') }}" class="text-xs font-bold text-indigo-600 hover:underline">كل الفواتير ←</a>
            </div>
            @if($recentInvoices->isEmpty())
                <p class="text-center py-6 text-xs text-slate-400">لا توجد فواتير منشأة من مقدمي الخدمات حتى الآن</p>
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
                                    <td class="font-mono text-xs font-bold text-indigo-700">{{ $inv->invoice_number }}</td>
                                    <td class="font-bold">{{ $inv->provider?->business_name }}</td>
                                    <td>{{ $inv->customer_name }}</td>
                                    <td class="font-black">{{ number_format((float) $inv->amount, 2) }} ر.س</td>
                                    <td>
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $inv->isPaid() ? 'bg-emerald-100 text-emerald-800' : ($inv->status === 'failed' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }}">
                                            {{ $inv->statusLabel() }}
                                        </span>
                                    </td>
                                    <td class="text-xs text-slate-500">{{ $inv->created_at->format('Y-m-d H:i') }}</td>
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
