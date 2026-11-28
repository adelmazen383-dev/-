@extends('layouts.provider', ['title' => 'إدارة العقود الإلكترونية', 'header' => 'إدارة العقود الإلكترونية'])

@section('content')
    <div class="space-y-4">
        <!-- Compact Filter & Create Action Bar -->
        <div class="bg-white rounded-xl border border-slate-200/90 p-3.5 flex flex-col lg:flex-row lg:items-center justify-between gap-3">
            <form method="GET" action="{{ route('provider.contracts.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 flex-1">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="بحث برقم العقد، اسم المستأجر، الهوية أو الجوال..."
                       class="p-input sm:max-w-xs text-xs">
                <select name="status" class="p-input sm:w-44 text-xs" onchange="this.form.submit()">
                    <option value="">كل الحالات</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>بانتظار التوقيع</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>مسودة</option>
                    <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>مُرسل</option>
                    <option value="viewed" {{ request('status') === 'viewed' ? 'selected' : '' }}>تمت المشاهدة</option>
                    <option value="signed_by_lessee" {{ request('status') === 'signed_by_lessee' ? 'selected' : '' }}>موقّع من المستأجر</option>
                    <option value="signed" {{ request('status') === 'signed' ? 'selected' : '' }}>موقّع ومكتمل</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>مرفوض</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>ملغى</option>
                </select>
                <button type="submit" class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-extrabold transition">
                    بحث
                </button>
            </form>

            @if($provider->hasActiveSubscription())
                <a href="{{ route('provider.contracts.create') }}"
                   class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-lg bg-[#064e3b] hover:bg-emerald-800 text-white text-xs font-black shadow-xs transition shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    إنشاء عقد جديد
                </a>
            @else
                <a href="{{ route('provider.subscription') }}"
                   class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-lg bg-amber-100 text-amber-900 text-xs font-extrabold shrink-0">
                    فعّل الاشتراك لإنشاء عقد
                </a>
            @endif
        </div>

        <!-- Contracts Table -->
        <div class="bg-white rounded-xl border border-slate-200/90 overflow-hidden shadow-xs">
            @if($contracts->isEmpty())
                <div class="text-center py-12 px-4">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <p class="text-sm font-black text-slate-800">لا توجد عقود إلكترونية حتى الآن</p>
                    <p class="text-xs text-slate-500 mt-1">يمكنك إنشاء عقد إيجار إلكتروني موثّق وإرسال رابط التوقيع للمستأجر مباشرة.</p>
                    @if($provider->hasActiveSubscription())
                        <a href="{{ route('provider.contracts.create') }}"
                           class="inline-flex items-center gap-1.5 mt-4 px-4 py-2 rounded-lg bg-[#064e3b] hover:bg-emerald-800 text-white text-xs font-extrabold">
                            إنشاء أول عقد
                        </a>
                    @endif
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-extrabold">
                            <tr>
                                <th class="py-3 px-4">رقم العقد</th>
                                <th class="py-3 px-4">المستأجر</th>
                                <th class="py-3 px-4">بداية العقد</th>
                                <th class="py-3 px-4">نهاية العقد</th>
                                <th class="py-3 px-4">قيمة الإيجار</th>
                                <th class="py-3 px-4">الحالة</th>
                                <th class="py-3 px-4">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($contracts as $contract)
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="py-3 px-4 font-mono font-black text-emerald-800 whitespace-nowrap">{{ $contract->contract_number }}</td>
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-slate-900">{{ $contract->lessee_name }}</div>
                                        <div class="text-[11px] text-slate-400 font-mono" dir="ltr">{{ $contract->lessee_phone }}</div>
                                    </td>
                                    <td class="py-3 px-4 text-slate-600 whitespace-nowrap">{{ $contract->start_date?->format('Y/m/d H:i') ?? '—' }}</td>
                                    <td class="py-3 px-4 text-slate-600 whitespace-nowrap">{{ $contract->end_date?->format('Y/m/d H:i') ?? '—' }}</td>
                                    <td class="py-3 px-4 font-black text-emerald-700 whitespace-nowrap tabular-nums">{{ number_format((float) $contract->rent_amount, 2) }} ر.س</td>
                                    <td class="py-3 px-4">
                                        <div class="flex flex-col gap-1 items-start">
                                            <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold
                                                {{ $contract->status === \App\Enums\ContractStatus::SIGNED ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : (in_array($contract->status, [\App\Enums\ContractStatus::REJECTED, \App\Enums\ContractStatus::CANCELLED]) ? 'bg-rose-50 text-rose-800 border border-rose-200' : 'bg-amber-50 text-amber-800 border border-amber-200') }}">
                                                {{ $contract->status->label() }}
                                            </span>
                                            @if($contract->isExpired())
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700">منتهي</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700">فعال</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <div class="flex items-center gap-1.5">
                                            <a href="{{ route('provider.contracts.show', $contract) }}"
                                               class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-emerald-50 hover:text-emerald-800 text-slate-700 text-xs font-bold transition">
                                                تفاصيل
                                            </a>
                                            @if($provider->hasActiveSubscription())
                                                <form method="POST" action="{{ route('provider.contracts.destroy', $contract) }}"
                                                      onsubmit="return confirm('هل أنت متأكد من حذف هذا العقد نهائياً؟')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-600 text-rose-700 hover:text-white text-xs font-bold transition" title="حذف العقد">
                                                        حذف
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($contracts->hasPages())
                    <div class="p-4 border-t border-slate-100">{{ $contracts->links() }}</div>
                @endif
            @endif
        </div>
    </div>
@endsection
