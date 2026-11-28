@extends('layouts.provider', ['title' =>'محفظتي والتحويلات', 'header' =>'محفظتي والتحويلات المالية'])

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="rounded-2xl bg-gradient-to-br from-indigo-600 to-violet-700 text-white p-5 shadow-lg">
            <div class="text-xs font-bold text-indigo-100">الرصيد الحالي في المحفظة</div>
            <div class="text-3xl font-black mt-2">{{ number_format((float) $provider->wallet_balance, 2) }} <span class="text-sm font-bold">ر.س</span></div>
            <p class="text-[11.5px] text-indigo-100 mt-2">يتم تحويل هذا الرصيد إلى حسابك البنكي وتصفير المحفظة عند التسوية مع الإدارة</p>
        </div>

        <div class="p-card flex flex-col justify-between">
            <div class="text-xs font-bold text-slate-500">إجمالي ما تم تحصيله (أرباحك)</div>
            <div class="text-2xl font-black text-slate-900 mt-2">{{ number_format((float) $provider->total_earned, 2) }} <span class="text-xs">ر.س</span></div>
            <div class="text-[11px] text-emerald-600 font-bold mt-2">مجموع صافي الفواتير المدفوعة والمؤكدة</div>
        </div>

        <div class="p-card flex flex-col justify-between">
            <div class="text-xs font-bold text-slate-500">إجمالي ما تم تحويله لحسابك</div>
            <div class="text-2xl font-black text-emerald-700 mt-2">{{ number_format((float) $provider->total_settled, 2) }} <span class="text-xs">ر.س</span></div>
            <div class="text-[11px] text-slate-500 mt-2">مجموع التحويلات والتسويات المكتملة</div>
        </div>
    </div>

    <!-- Bank Account Info -->
    <div class="p-card mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h3 class="text-sm font-black text-slate-900">بيانات الحساب البنكي للتحويل</h3>
            @if($provider->iban)
                <p class="text-xs text-slate-600 mt-1">
                    البنك: <strong>{{ $provider->bank_name ?: 'غير محدد' }}</strong>·
                    صاحب الحساب: <strong>{{ $provider->bank_account_name ?: $provider->name }}</strong>·
                    IBAN: <strong class="font-mono" dir="ltr">{{ $provider->iban }}</strong>
                </p>
            @else
                <p class="text-xs text-amber-700 font-bold mt-1">لم تقم بإضافة رقم الآيبان (IBAN) بعد. أضفه من صفحة حسابك لتسهيل تحويل مستحقاتك.</p>
            @endif
        </div>
        <a href="{{ route('provider.profile') }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold shrink-0 text-center">
            عرض الحساب / طلب تعديل
        </a>
    </div>

    <!-- Wallet Ledger -->
    <div class="p-card">
        <h3 class="text-base font-black text-slate-900 mb-4">سجل حركات المحفظة</h3>

        @if($transactions->isEmpty())
            <div class="text-center py-10 text-slate-400 text-sm">
                لا توجد حركات مالية في المحفظة حتى الآن.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs sm:text-sm">
                    <thead>
                        <tr class="bg-slate-50 text-slate-600 border-b border-slate-200">
                            <th class="py-2.5 px-3 font-extrabold">التاريخ</th>
                            <th class="py-2.5 px-3 font-extrabold">النوع</th>
                            <th class="py-2.5 px-3 font-extrabold">المبلغ</th>
                            <th class="py-2.5 px-3 font-extrabold">الرصيد بعد العملية</th>
                            <th class="py-2.5 px-3 font-extrabold">المرجع / التفاصيل</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($transactions as $tx)
                            <tr class="hover:bg-slate-50">
                                <td class="py-3 px-3 text-slate-500 whitespace-nowrap">{{ $tx->created_at->format('Y-m-d H:i') }}</td>
                                <td class="py-3 px-3 whitespace-nowrap">
                                    @if($tx->type === 'credit')
                                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">إيداع فاتورة</span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-100 text-indigo-800">تحويل وتصفير</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 font-black whitespace-nowrap {{ $tx->type === 'credit' ? 'text-emerald-700' : 'text-rose-700' }}" dir="ltr">
                                    {{ $tx->type === 'credit' ? '+' : '-' }}{{ number_format((float) $tx->amount, 2) }} ر.س
                                </td>
                                <td class="py-3 px-3 font-bold text-slate-800 whitespace-nowrap">{{ number_format((float) $tx->balance_after, 2) }} ر.س</td>
                                <td class="py-3 px-3 text-slate-600">
                                    @if($tx->reference)
                                        <span class="font-mono text-[11px] font-bold text-slate-700">{{ $tx->reference }}</span>—
                                    @endif
                                    {{ $tx->notes }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $transactions->links() }}</div>
        @endif
    </div>
@endsection
