<x-app-layout>
    <x-slot name="header">المحافظ والتحويلات — تسوية نهاية اليوم</x-slot>

    <div class="space-y-6">
        <!-- Compact Unified Financial Totals Bar -->
        <div class="bg-white rounded-xl border border-slate-200/90 shadow-xs overflow-hidden">
            <div class="grid grid-cols-1 sm:grid-cols-3 divide-y sm:divide-y-0 divide-slate-100">
                <div class="px-4 py-3.5 sm:border-l border-slate-100">
                    <div class="text-[11.5px] font-bold text-slate-500">أرصدة المحافظ المستحقة حالياً</div>
                    <div class="text-xl font-black text-amber-700 mt-0.5 tabular-nums">{{ number_format($totalOwed, 2) }} <span class="text-xs text-slate-400">ر.س</span></div>
                </div>
                <div class="px-4 py-3.5 sm:border-l border-slate-100">
                    <div class="text-[11.5px] font-bold text-slate-500">إجمالي ما تم تحويله وتصفيره</div>
                    <div class="text-xl font-black text-emerald-700 mt-0.5 tabular-nums">{{ number_format($totalSettled, 2) }} <span class="text-xs text-slate-400">ر.س</span></div>
                </div>
                <div class="px-4 py-3.5">
                    <div class="text-[11.5px] font-bold text-slate-500">إجمالي أرباح مقدمي الخدمات</div>
                    <div class="text-xl font-black text-slate-900 mt-0.5 tabular-nums">{{ number_format($totalEarned, 2) }} <span class="text-xs text-slate-400">ر.س</span></div>
                </div>
            </div>
        </div>

        <!-- Providers Ready for Payout & Zeroing -->
        <div class="card">
            <h3 class="text-base font-black text-slate-900 mb-4">محافظ بها رصيد جاهز للتحويل والتصفير ({{ $providersWithBalance->count() }})</h3>

            @if($providersWithBalance->isEmpty())
                <div class="text-center py-8 text-sm font-bold text-emerald-700 bg-emerald-50 rounded-xl">
                    جميع محافظ مقدمي الخدمات مصفّرة حالياً (0.00 ر.س).
                </div>
            @else
                <div class="space-y-3">
                    @foreach($providersWithBalance as $p)
                        <div class="p-4 rounded-xl border border-indigo-200 bg-indigo-50/30 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <a href="{{ route('admin.providers.show', $p) }}" class="text-base font-black text-slate-900 hover:text-indigo-700">{{ $p->business_name }}</a>
                                    <span class="text-xs text-slate-500">({{ $p->name }})</span>
                                </div>
                                <div class="text-xs text-slate-600 mt-1">
                                    البنك: <strong>{{ $p->bank_name ?: 'غير محدد' }}</strong>·
                                    صاحب الحساب: <strong>{{ $p->bank_account_name ?: 'غير محدد' }}</strong>·
                                    IBAN: <strong class="font-mono text-indigo-800" dir="ltr">{{ $p->iban ?: 'غير محدد' }}</strong>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('admin.providers.wallet.settle', $p) }}"
                                  class="flex flex-wrap items-center gap-2 shrink-0"
                                  onsubmit="return confirm('تأكيد تحويل مبلغ {{ number_format((float) $p->wallet_balance, 2) }} ر.س وتصفير محفظة {{ $p->business_name }}؟')">
                                @csrf
                                <input type="hidden" name="settle_mode" value="full">
                                <input type="text" name="reference" placeholder="مرجع الحوالة (اختياري)" class="form-input w-44 text-xs">
                                <button type="submit" class="btn btn-emerald">
                                    تحويل {{ number_format((float) $p->wallet_balance, 2) }} ر.س وتصفير المحفظة
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Complete Wallet Transactions Ledger -->
        <div class="card p-0 overflow-hidden">
            <div class="p-4 border-b border-slate-100">
                <h3 class="text-sm font-black text-slate-900">سجل جميع حركات المحافظ والتحويلات</h3>
            </div>
            @if($transactions->isEmpty())
                <div class="p-8 text-center text-xs text-slate-400">لا توجد حركات مالية مسجلة بعد.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>التاريخ</th>
                                <th>مقدم الخدمة</th>
                                <th>العملية</th>
                                <th>المبلغ</th>
                                <th>الرصيد قبل</th>
                                <th>الرصيد بعد</th>
                                <th>المرجع / التفاصيل</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $tx)
                                <tr>
                                    <td class="text-xs text-slate-500 whitespace-nowrap">{{ $tx->created_at->format('Y-m-d H:i') }}</td>
                                    <td class="font-bold">{{ $tx->provider?->business_name }}</td>
                                    <td>
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $tx->type === 'credit' ? 'bg-emerald-100 text-emerald-800' : 'bg-indigo-100 text-indigo-800' }}">
                                            {{ $tx->typeLabel() }}
                                        </span>
                                    </td>
                                    <td class="font-black {{ $tx->type === 'credit' ? 'text-emerald-700' : 'text-rose-700' }}" dir="ltr">
                                        {{ $tx->type === 'credit' ? '+' : '-' }}{{ number_format((float) $tx->amount, 2) }} ر.س
                                    </td>
                                    <td class="text-xs text-slate-600">{{ number_format((float) $tx->balance_before, 2) }}</td>
                                    <td class="text-xs font-extrabold text-slate-900">{{ number_format((float) $tx->balance_after, 2) }}</td>
                                    <td class="text-xs text-slate-600">
                                        <span class="font-mono font-bold">{{ $tx->reference }}</span>— {{ $tx->notes }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-slate-100">{{ $transactions->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
