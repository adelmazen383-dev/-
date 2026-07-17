@extends('layouts.app')

@section('title', 'قائمة العقود')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">العقود</h2>
        <p class="text-slate-500 text-sm mt-1">إدارة العقود وتتبع حالتها</p>
    </div>
    
    <a href="{{ route('contracts.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg shadow-sm transition flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" /></svg>
        إنشاء عقد جديد
    </a>
</div>

<!-- الفلترة والبحث -->
<div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 mb-6">
    <form action="{{ route('contracts.index') }}" method="GET" class="flex flex-col sm:flex-row gap-4">
        <div class="flex-grow">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="بحث برقم العقد أو اسم العميل..." class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>
        <div class="w-full sm:w-64">
            <select name="status" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">جميع الحالات</option>
                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>مسودة</option>
                <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>مُرسل</option>
                <option value="viewed" {{ request('status') == 'viewed' ? 'selected' : '' }}>تمت المشاهدة</option>
                <option value="signed" {{ request('status') == 'signed' ? 'selected' : '' }}>موقّع</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ملغى</option>
            </select>
        </div>
        <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white font-semibold py-2 px-6 rounded-lg transition shadow-sm">بحث</button>
        <a href="{{ route('contracts.index') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold py-2 px-4 rounded-lg transition text-center">إلغاء الفلتر</a>
    </form>
</div>

<!-- جدول العقود -->
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-right whitespace-nowrap">
            <thead class="bg-slate-50 border-b border-slate-200 text-slate-600">
                <tr>
                    <th class="px-6 py-4 font-semibold text-sm">رقم العقد</th>
                    <th class="px-6 py-4 font-semibold text-sm">العميل</th>
                    <th class="px-6 py-4 font-semibold text-sm">تاريخ الإنشاء</th>
                    <th class="px-6 py-4 font-semibold text-sm">الحالة</th>
                    <th class="px-6 py-4 font-semibold text-sm text-center">الإجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
                @forelse($contracts ?? [] as $contract)
                <tr class="hover:bg-slate-50 transition">
                    <td class="px-6 py-4 font-bold text-slate-800" dir="ltr">{{ $contract->contract_number }}</td>
                    <td class="px-6 py-4">{{ $contract->customer->name ?? 'غير معروف' }}</td>
                    <td class="px-6 py-4">{{ $contract->created_at ? $contract->created_at->format('Y-m-d') : '' }}</td>
                    <td class="px-6 py-4">
                        @if($contract->status == 'signed')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 border border-emerald-200">موقّع</span>
                        @elseif($contract->status == 'draft')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800 border border-slate-200">مسودة</span>
                        @elseif($contract->status == 'sent')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 border border-blue-200">مُرسل</span>
                        @elseif($contract->status == 'viewed')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 border border-purple-200">تمت المشاهدة</span>
                        @elseif($contract->status == 'cancelled')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 border border-red-200">ملغى</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">{{ $contract->status }}</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 flex justify-center gap-3">
                        <a href="{{ route('contracts.show', $contract->id) }}" class="text-blue-600 hover:text-blue-800 font-semibold transition bg-blue-50 px-3 py-1 rounded-md border border-blue-100 hover:bg-blue-100">عرض التفاصيل</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-10 text-center text-slate-500">لا توجد عقود تطابق شروط البحث.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if(isset($contracts) && method_exists($contracts, 'hasPages') && $contracts->hasPages())
    <div class="px-6 py-4 border-t border-slate-200">
        {{ $contracts->links() }}
    </div>
    @endif
</div>
@endsection
