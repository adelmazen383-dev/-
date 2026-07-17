<x-app-layout>
    <x-slot name="header">إدارة النظام والموظفين</x-slot>

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold text-white">قائمة الموظفين</h2>
        <a href="{{ route('users.create') }}" class="btn btn-indigo">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            إضافة موظف جديد
        </a>
    </div>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>الاسم</th>
                        <th>البريد الإلكتروني</th>
                        <th>الصلاحية</th>
                        <th>الحالة</th>
                        <th>تاريخ الإضافة</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td class="font-bold text-white">{{ $user->name }}</td>
                        <td class="text-slate-300">{{ $user->email }}</td>
                        <td>
                            @if($user->hasRole('admin'))
                                <span class="badge badge-sent">مدير النظام</span>
                            @else
                                <span class="badge badge-viewed">موظف عام</span>
                            @endif
                        </td>
                        <td>
                            @if($user->is_active)
                                <span class="badge badge-signed">نشط</span>
                            @else
                                <span class="badge badge-rejected">معطل</span>
                            @endif
                        </td>
                        <td class="text-slate-400 text-sm">{{ $user->created_at->format('Y-m-d') }}</td>
                        <td>
                            @if($user->id !== auth()->id())
                                <div class="flex gap-2">
                                    <form method="POST" action="{{ route('users.toggle', $user) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm {{ $user->is_active ? 'btn-ghost' : 'btn-emerald' }}">
                                            {{ $user->is_active ? 'تعطيل' : 'تفعيل' }}
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('هل أنت متأكد من حذف هذا المستخدم نهائياً؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-rose btn-sm">حذف</button>
                                    </form>
                                </div>
                            @else
                                <span class="text-slate-500 text-sm italic">حسابك الحالي</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-6 text-slate-500">لا يوجد مستخدمين.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="mt-4">{{ $users->links() }}</div>
        @endif
    </div>
</x-app-layout>
