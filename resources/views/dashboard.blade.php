<x-app-layout>
    <x-slot name="header">لوحة التحكم</x-slot>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <div class="stat-card indigo">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-400 text-sm font-semibold">إجمالي العقود</p>
                    <p class="text-3xl font-black text-white mt-1">{{ $totalContracts }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-indigo-500/10 flex items-center justify-center">
                    <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
            </div>
        </div>
        <div class="stat-card emerald">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-400 text-sm font-semibold">عقود موقّعة</p>
                    <p class="text-3xl font-black text-emerald-400 mt-1">{{ $signedContracts }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-500/10 flex items-center justify-center">
                    <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>
        <div class="stat-card amber">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-400 text-sm font-semibold">بانتظار التوقيع</p>
                    <p class="text-3xl font-black text-amber-400 mt-1">{{ $pendingContracts }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-amber-500/10 flex items-center justify-center">
                    <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>
        <div class="stat-card rose">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-400 text-sm font-semibold">إجمالي العملاء</p>
                    <p class="text-3xl font-black text-rose-400 mt-1">{{ $totalCustomers }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-rose-500/10 flex items-center justify-center">
                    <svg class="w-6 h-6 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Summary + Chart -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Financial Card -->
        <div class="card">
            <h3 class="text-lg font-bold text-white mb-5 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                ملخص مالي
            </h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center p-3 rounded-lg bg-white/5">
                    <span class="text-slate-400 text-sm">إجمالي الإيجارات (موقّعة)</span>
                    <span class="text-emerald-400 font-bold text-lg">{{ number_format($totalRentSigned) }} ر.س</span>
                </div>
                <div class="flex justify-between items-center p-3 rounded-lg bg-white/5">
                    <span class="text-slate-400 text-sm">إجمالي الإيجارات (كل العقود)</span>
                    <span class="text-slate-300 font-bold text-lg">{{ number_format($totalRentAll) }} ر.س</span>
                </div>
                <div class="flex justify-between items-center p-3 rounded-lg bg-white/5">
                    <span class="text-slate-400 text-sm">نسبة التوقيع</span>
                    <span class="text-indigo-400 font-bold text-lg">{{ $totalContracts > 0 ? round(($signedContracts / $totalContracts) * 100) : 0 }}%</span>
                </div>
            </div>
            <div class="mt-4 flex gap-2">
                <a href="{{ route('export.contracts') }}" class="btn btn-ghost btn-sm flex-1 justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    تصدير العقود CSV
                </a>
                <a href="{{ route('export.customers') }}" class="btn btn-ghost btn-sm flex-1 justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    تصدير العملاء CSV
                </a>
            </div>
        </div>

        <!-- Chart Card -->
        <div class="card">
            <h3 class="text-lg font-bold text-white mb-5 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                العقود (آخر 6 أشهر)
            </h3>
            <canvas id="contractsChart" height="200"></canvas>
        </div>
    </div>

    <!-- Expiring Contracts Alert -->
    @if($expiringContracts->count() > 0)
    <div class="card mb-8" style="border: 1px solid rgba(251,191,36,0.3); background: rgba(251,191,36,0.05);">
        <h3 class="text-amber-400 font-bold mb-4 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
            ⚠️ عقود تنتهي خلال 30 يوم ({{ $expiringContracts->count() }})
        </h3>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead><tr><th>رقم العقد</th><th>العميل</th><th>تاريخ الانتهاء</th><th>المتبقي</th><th>إجراء</th></tr></thead>
                <tbody>
                    @foreach($expiringContracts as $exp)
                    <tr>
                        <td class="font-mono text-amber-400 font-bold">{{ $exp->contract_number }}</td>
                        <td>{{ $exp->customer->name ?? '—' }}</td>
                        <td>{{ $exp->end_date->format('Y/m/d') }}</td>
                        <td class="text-amber-300 font-semibold">{{ $exp->end_date->diffForHumans() }}</td>
                        <td><a href="{{ route('contracts.show', $exp) }}" class="btn btn-ghost btn-sm">تفاصيل</a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Recent Contracts -->
    <div class="card">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-bold text-white">آخر العقود</h3>
            <a href="{{ route('contracts.index') }}" class="btn btn-ghost btn-sm">عرض الكل ←</a>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>رقم العقد</th>
                        <th>العميل</th>
                        <th>الحالة</th>
                        <th>قيمة الإيجار</th>
                        <th>التاريخ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentContracts as $contract)
                    <tr>
                        <td class="font-mono text-indigo-400 font-bold">{{ $contract->contract_number }}</td>
                        <td>{{ $contract->customer->name ?? '—' }}</td>
                        <td>
                            <span class="badge {{ $contract->status->badgeClass() }}">{{ $contract->status->label() }}</span>
                        </td>
                        <td class="font-semibold">{{ number_format($contract->rent_amount) }} ر.س</td>
                        <td class="text-slate-500 text-sm">{{ $contract->created_at->diffForHumans() }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-8 text-slate-500">لا توجد عقود حتى الآن</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
    <script>
        new Chart(document.getElementById('contractsChart'), {
            type: 'bar',
            data: {
                labels: @json($monthlyData['labels']),
                datasets: [
                    {
                        label: 'عقود تم إنشاؤها',
                        data: @json($monthlyData['created']),
                        backgroundColor: 'rgba(99, 102, 241, 0.6)',
                        borderColor: 'rgba(99, 102, 241, 1)',
                        borderWidth: 1, borderRadius: 6,
                    },
                    {
                        label: 'عقود تم توقيعها',
                        data: @json($monthlyData['signed']),
                        backgroundColor: 'rgba(16, 185, 129, 0.6)',
                        borderColor: 'rgba(16, 185, 129, 1)',
                        borderWidth: 1, borderRadius: 6,
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: { legend: { labels: { color: '#94a3b8', font: { family: 'Cairo' } } } },
                scales: {
                    x: { ticks: { color: '#64748b', font: { family: 'Cairo' } }, grid: { color: 'rgba(255,255,255,0.05)' } },
                    y: { beginAtZero: true, ticks: { color: '#64748b', stepSize: 1 }, grid: { color: 'rgba(255,255,255,0.05)' } }
                }
            }
        });
    </script>
</x-app-layout>
