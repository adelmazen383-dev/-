<x-app-layout>
    <x-slot name="header">إدارة العقود</x-slot>

    <!-- Filters & Actions -->
    <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4 mb-6">
        <form method="GET" action="{{ route('contracts.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full lg:w-auto">
            <div class="relative">
                <svg class="w-5 h-5 text-slate-500 absolute right-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="بحث برقم العقد أو اسم العميل..."
                       class="form-input pr-10" style="min-width:280px;">
            </div>
            <select name="status" class="form-input" style="min-width:160px;" onchange="this.form.submit()">
                <option value="">كل الحالات</option>
                <option value="draft" {{ request('status')=='draft'?'selected':'' }}>مسودة</option>
                <option value="sent" {{ request('status')=='sent'?'selected':'' }}>مُرسل</option>
                <option value="viewed" {{ request('status')=='viewed'?'selected':'' }}>تمت المشاهدة</option>
                <option value="signed_by_lessee" {{ request('status')=='signed_by_lessee'?'selected':'' }}>موقّع من المستأجر</option>
                <option value="signed" {{ request('status')=='signed'?'selected':'' }}>موقّع</option>
                <option value="rejected" {{ request('status')=='rejected'?'selected':'' }}>مرفوض</option>
                <option value="cancelled" {{ request('status')=='cancelled'?'selected':'' }}>ملغى</option>
            </select>
            <button type="submit" class="btn btn-ghost btn-sm">بحث</button>
        </form>

        <a href="{{ route('contracts.create') }}" class="btn btn-indigo whitespace-nowrap">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            إنشاء عقد جديد
        </a>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>رقم العقد</th>
                        <th>المستأجر</th>
                        <th>بداية العقد</th>
                        <th>نهاية العقد</th>
                        <th>قيمة الإيجار</th>
                        <th>الحالة</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contracts as $contract)
                    <tr>
                        <td class="font-mono text-indigo-400 font-bold">{{ $contract->contract_number }}</td>
                        <td class="text-white font-semibold">{{ $contract->customer->name ?? '—' }}</td>
                        <td class="text-sm">{{ $contract->start_date?->format('Y/m/d') ?? '—' }}</td>
                        <td class="text-sm">{{ $contract->end_date?->format('Y/m/d') ?? '—' }}</td>
                        <td class="font-semibold text-emerald-400">{{ number_format($contract->rent_amount) }} ر.س</td>
                        <td>
                            <span class="badge {{ $contract->status->badgeClass() }}">{{ $contract->status->label() }}</span>
                        </td>
                        <td>
                            <a href="{{ route('contracts.show', $contract) }}" class="btn btn-ghost btn-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                تفاصيل
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-12">
                            <svg class="w-16 h-16 text-slate-700 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <p class="text-slate-500 font-semibold">لا توجد عقود</p>
                            <a href="{{ route('contracts.create') }}" class="btn btn-indigo btn-sm mt-4 inline-flex">إنشاء أول عقد</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($contracts->hasPages())
            <div class="mt-6 flex justify-center">{{ $contracts->links() }}</div>
        @endif
    </div>
</x-app-layout>
