<x-app-layout>
    <x-slot name="header">إدارة العملاء</x-slot>

    <!-- Action Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
        <p class="text-slate-600 text-sm">إجمالي العملاء: <span class="text-slate-800 font-bold">{{ $clients->total() }}</span></p>
        <a href="{{ route('clients.create') }}" class="btn btn-indigo">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            إضافة عميل جديد
        </a>
    </div>

    <!-- Table Card -->
    <div class="card">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>اسم العميل</th>
                        <th>رقم الهوية</th>
                        <th>النوع</th>
                        <th>رقم الجوال</th>
                        <th>عدد العقود</th>
                        <th>تاريخ التسجيل</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clients as $client)
                    <tr>
                        <td class="text-slate-600 font-mono">{{ $client->id }}</td>
                        <td class="text-slate-800 font-semibold">{{ $client->name }}</td>
                        <td class="font-mono text-indigo-400">{{ $client->national_id }}</td>
                        <td>
                            @if($client->type === 'lessor')
                                <span class="badge badge-viewed">مالك</span>
                            @else
                                <span class="badge badge-signed">مستأجر</span>
                            @endif
                        </td>
                        <td dir="ltr" class="text-slate-700">{{ $client->phone }}</td>
                        <td>
                            <span class="badge badge-sent">{{ $client->contracts_count ?? $client->contracts()->count() }}</span>
                        </td>
                        <td class="text-slate-600 text-sm">{{ $client->created_at->format('Y/m/d') }}</td>
                        <td>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('clients.edit', $client) }}" class="btn btn-ghost btn-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    تعديل
                                </a>
                                <form action="{{ route('clients.destroy', $client) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا العميل؟')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-rose btn-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        حذف
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-12">
                            <svg class="w-16 h-16 text-slate-700 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <p class="text-slate-600 font-semibold">لا يوجد عملاء مسجلين</p>
                            <a href="{{ route('clients.create') }}" class="btn btn-indigo btn-sm mt-4 inline-flex">إضافة أول عميل</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($clients->hasPages())
            <div class="mt-6 flex justify-center">
                {{ $clients->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
