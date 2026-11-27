<x-app-layout>
    <x-slot name="header">فواتير مقدمي الخدمات (قسم مستقل)</x-slot>

    <div class="space-y-5">
        <div class="card">
            <form method="GET" class="grid grid-cols-1 sm:grid-cols-5 gap-3">
                <input type="text" name="search" value="{{ request('search') }}" class="form-input" placeholder="رقم الفاتورة، العميل، مقدم الخدمة...">
                <select name="provider_id" class="form-input">
                    <option value="">كل مقدمي الخدمات</option>
                    @foreach($providers as $p)
                        <option value="{{ $p->id }}" {{ (string) request('provider_id') === (string) $p->id ? 'selected' : '' }}>{{ $p->business_name }}</option>
                    @endforeach
                </select>
                <select name="service_type" class="form-input">
                    <option value="">كل أنواع الخدمات</option>
                    @foreach($serviceTypes as $t)
                        <option value="{{ $t['value'] }}" {{ request('service_type') === $t['value'] ? 'selected' : '' }}>{{ $t['icon'] }} {{ $t['label'] }}</option>
                    @endforeach
                </select>
                <select name="status" class="form-input">
                    <option value="">كل الحالات</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>بانتظار الدفع</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>مدفوعة ومؤكدة</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>فشلت</option>
                </select>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-indigo flex-1">بحث</button>
                    <a href="{{ route('admin.providers.invoices.index') }}" class="btn btn-ghost">إعادة</a>
                </div>
            </form>
        </div>

        <div class="card p-0 overflow-hidden">
            @if($invoices->isEmpty())
                <div class="p-10 text-center text-slate-400 text-sm">لا توجد فواتير مطابقة.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>رقم الفاتورة</th>
                                <th>مقدم الخدمة</th>
                                <th>النوع</th>
                                <th>العميل</th>
                                <th>صافي المحفظة</th>
                                <th>إجمالي العميل</th>
                                <th>الحالة</th>
                                <th>المحفظة</th>
                                <th>التاريخ</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoices as $inv)
                                <tr>
                                    <td class="font-mono text-xs font-extrabold text-indigo-700">{{ $inv->invoice_number }}</td>
                                    <td>
                                        <a href="{{ route('admin.providers.show', $inv->provider) }}" class="font-bold text-slate-900 hover:text-indigo-700">
                                            {{ $inv->provider?->business_name }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold border {{ $inv->serviceTypeEnum()->badgeClasses() }}">
                                            {{ $inv->serviceTypeEnum()->label() }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="font-bold text-xs">{{ $inv->customer_name }}</div>
                                        <div class="text-[11px] text-slate-500" dir="ltr">{{ $inv->customer_phone }}</div>
                                    </td>
                                    <td class="font-black text-emerald-700">{{ number_format((float) $inv->amount, 2) }} ر.س</td>
                                    <td class="text-xs font-bold text-slate-600">{{ number_format((float) $inv->total_payable, 2) }} ر.س</td>
                                    <td>
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $inv->isPaid() ? 'bg-emerald-100 text-emerald-800' : ($inv->status === 'failed' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }}">
                                            {{ $inv->statusLabel() }}
                                        </span>
                                    </td>
                                    <td class="text-xs font-bold">{{ $inv->isWalletCredited() ? '✅ أُضيفت' : '—' }}</td>
                                    <td class="text-xs text-slate-500">{{ $inv->created_at->format('Y-m-d H:i') }}</td>
                                    <td><a href="{{ route('admin.providers.invoices.show', $inv) }}" class="btn btn-indigo btn-sm">عرض</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-slate-100">{{ $invoices->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
