<x-app-layout>
    <x-slot name="header">إدارة مقدمي الخدمات</x-slot>

    <div class="space-y-5">
        <div class="card">
            <form method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                <input type="text" name="search" value="{{ request('search') }}" class="form-input" placeholder="بحث بالاسم، النشاط، الجوال أو البريد...">
                <select name="service_type" class="form-input">
                    <option value="">كل أنواع الخدمات (4)</option>
                    @foreach($serviceTypes as $t)
                        <option value="{{ $t['value'] }}" {{ request('service_type') === $t['value'] ? 'selected' : '' }}>{{ $t['icon'] }} {{ $t['label'] }}</option>
                    @endforeach
                </select>
                <select name="sub_status" class="form-input">
                    <option value="">كل حالات الاشتراك</option>
                    <option value="active" {{ request('sub_status') === 'active' ? 'selected' : '' }}>نشط</option>
                    <option value="inactive" {{ request('sub_status') === 'inactive' ? 'selected' : '' }}>غير مشترك (جديد)</option>
                    <option value="expired" {{ request('sub_status') === 'expired' ? 'selected' : '' }}>منتهي الاشتراك</option>
                    <option value="suspended" {{ request('sub_status') === 'suspended' ? 'selected' : '' }}>موقوف</option>
                </select>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-indigo flex-1">تصفية</button>
                    <a href="{{ route('admin.providers.index') }}" class="btn btn-ghost">إعادة</a>
                </div>
            </form>
        </div>

        <div class="card p-0 overflow-hidden">
            @if($providers->isEmpty())
                <div class="p-10 text-center text-slate-400 text-sm">لا يوجد مقدمي خدمات مطابقين للبحث.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>النشاط / مقدم الخدمة</th>
                                <th>نوع الخدمة</th>
                                <th>التواصل</th>
                                <th>الوثائق</th>
                                <th>الاشتراك الشهري</th>
                                <th>الفواتير</th>
                                <th>رصيد المحفظة</th>
                                <th>إدارة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($providers as $p)
                                @php $docCount = count($p->uploadedDocuments()); @endphp
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.providers.show', $p) }}" class="font-extrabold text-slate-900 hover:text-indigo-700 block">{{ $p->business_name }}</a>
                                        <span class="text-xs text-slate-500">{{ $p->name }}</span>
                                    </td>
                                    <td>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $p->serviceTypeEnum()->badgeClasses() }}">
                                            {{ $p->serviceTypeEnum()->icon() }} {{ $p->service_type_label }}
                                        </span>
                                        @if($p->property_kind)
                                            <div class="text-[11px] text-slate-500 mt-0.5">{{ $p->property_kind }}</div>
                                        @endif
                                    </td>
                                    <td class="text-xs">
                                        <div dir="ltr" class="font-mono text-slate-700">{{ $p->phone }}</div>
                                        <div dir="ltr" class="text-slate-500">{{ $p->email }}</div>
                                    </td>
                                    <td>
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $docCount > 0 ? 'bg-sky-100 text-sky-800' : 'bg-slate-100 text-slate-500' }}">
                                            📎 {{ $docCount }} ملفات
                                        </span>
                                    </td>
                                    <td>
                                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $p->hasActiveSubscription() ? 'bg-emerald-100 text-emerald-800' : ($p->effectiveSubscriptionStatus() === 'expired' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }}">
                                            {{ $p->subscriptionStatusLabel() }}
                                        </span>
                                        @if($p->subscription_ends_at)
                                            <div class="text-[11px] text-slate-500 mt-0.5">حتى {{ $p->subscription_ends_at->format('Y-m-d') }}</div>
                                        @endif
                                    </td>
                                    <td class="text-xs font-bold">
                                        {{ $p->paid_invoices_count }} مدفوعة / {{ $p->invoices_count }}
                                    </td>
                                    <td class="font-black {{ (float) $p->wallet_balance > 0 ? 'text-indigo-700' : 'text-slate-400' }}">
                                        {{ number_format((float) $p->wallet_balance, 2) }} ر.س
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.providers.show', $p) }}" class="btn btn-indigo btn-sm">فتح الملف</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-slate-100">{{ $providers->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
