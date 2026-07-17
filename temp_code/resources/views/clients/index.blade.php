@extends('layouts.app')

@section('title', 'قائمة العملاء')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">العملاء</h2>
        <p class="text-slate-500 text-sm mt-1">إدارة بيانات العملاء المسجلين في النظام</p>
    </div>
    
    <a href="{{ route('clients.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg shadow-sm transition flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
        </svg>
        إضافة عميل جديد
    </a>
</div>

<!-- جدول العملاء -->
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-right whitespace-nowrap">
            <thead class="bg-slate-50 border-b border-slate-200 text-slate-600">
                <tr>
                    <th class="px-6 py-4 font-semibold text-sm">#</th>
                    <th class="px-6 py-4 font-semibold text-sm">اسم العميل</th>
                    <th class="px-6 py-4 font-semibold text-sm">رقم الهوية</th>
                    <th class="px-6 py-4 font-semibold text-sm">رقم الجوال</th>
                    <th class="px-6 py-4 font-semibold text-sm text-center">الإجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
                @forelse($clients ?? [] as $client)
                <tr class="hover:bg-slate-50 transition">
                    <td class="px-6 py-4">{{ $client->id }}</td>
                    <td class="px-6 py-4 font-bold text-slate-800">{{ $client->name }}</td>
                    <td class="px-6 py-4">{{ $client->national_id }}</td>
                    <td class="px-6 py-4" dir="ltr">{{ $client->phone }}</td>
                    <td class="px-6 py-4 flex justify-center gap-3">
                        <a href="{{ route('clients.edit', $client->id) }}" class="text-blue-500 hover:text-blue-700 transition">تعديل</a>
                        <!-- نموذج الحذف -->
                        <form action="{{ route('clients.destroy', $client->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700 transition">حذف</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-10 text-center text-slate-500">لا يوجد عملاء مسجلين حالياً.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- الترقيم (Pagination) -->
    @if(isset($clients) && method_exists($clients, 'hasPages') && $clients->hasPages())
    <div class="px-6 py-4 border-t border-slate-200">
        {{ $clients->links() }}
    </div>
    @endif
</div>
@endsection
