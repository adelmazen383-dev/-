@extends('layouts.provider', ['title' => 'فواتيري', 'header' => 'إدارة فواتيري'])

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <form method="GET" class="flex flex-wrap items-center gap-2 flex-1">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="ابحث برقم الفاتورة أو اسم العميل..."
                   class="p-input sm:max-w-xs">
            <select name="status" class="p-input sm:w-40" onchange="this.form.submit()">
                <option value="">كل الحالات</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>بانتظار الدفع</option>
                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>مدفوعة</option>
                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>فشلت</option>
            </select>
            <button type="submit" class="px-4 py-2 rounded-xl bg-slate-800 text-white text-xs font-bold">بحث</button>
        </form>

        @if($provider->hasActiveSubscription())
            <a href="{{ route('provider.invoices.create') }}"
               class="inline-flex items-center justify-center gap-1.5 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-extrabold shadow shrink-0">
                ➕ إنشاء فاتورة جديدة
            </a>
        @else
            <span class="px-4 py-2 rounded-xl bg-amber-100 text-amber-900 text-xs font-bold text-center">
                🔒 يلزم اشتراك شهري نشط لإنشاء فواتير
            </span>
        @endif
    </div>

    @if($invoices->isEmpty())
        <div class="p-card text-center py-12 text-slate-400">
            <div class="text-4xl mb-2">🧾</div>
            <p class="text-sm font-bold text-slate-600">لا توجد فواتير مطابقة حالياً</p>
        </div>
    @else
        <div class="grid grid-cols-1 gap-3">
            @foreach($invoices as $inv)
                <div class="p-card flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <a href="{{ route('provider.invoices.show', $inv) }}" class="font-mono text-xs font-black text-indigo-700 hover:underline">
                                {{ $inv->invoice_number }}
                            </a>
                            @if($inv->isPaid())
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">✅ مدفوعة</span>
                            @elseif($inv->status === 'failed')
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800">❌ فشلت</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800">⏳ بانتظار الدفع</span>
                            @endif
                            @if($inv->isWalletCredited())
                                <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-indigo-50 text-indigo-700">💰 مضافة للمحفظة</span>
                            @endif
                        </div>
                        <div class="text-sm font-extrabold text-slate-900 mt-1">{{ $inv->customer_name }} — {{ $inv->title }}</div>
                        <div class="text-xs text-slate-500 mt-0.5">
                            📱 {{ $inv->customer_phone }} · 📅 {{ $inv->created_at->format('Y-m-d H:i') }}
                        </div>
                    </div>

                    <div class="flex items-center justify-between sm:justify-end gap-3 pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-100 shrink-0">
                        <div class="text-right">
                            <div class="text-base font-black text-slate-900">{{ number_format((float) $inv->amount, 2) }} <span class="text-xs font-bold">ر.س</span></div>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <button type="button"
                                    onclick="navigator.clipboard.writeText('{{ $inv->public_payment_url }}').then(() => alert('تم نسخ رابط الدفع للعميل!'))"
                                    class="px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 text-xs font-bold">
                                🔗 نسخ الرابط
                            </button>
                            <a href="{{ route('provider.invoices.show', $inv) }}"
                               class="px-3 py-1.5 rounded-lg bg-slate-900 text-white hover:bg-slate-800 text-xs font-bold">
                                التفاصيل
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">{{ $invoices->links() }}</div>
    @endif
@endsection
