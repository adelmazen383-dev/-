@php
    $details = $invoice->service_details ?? [];
    $customType = $details['custom_service_type'] ?? ($provider->custom_service_name ?: 'خدمة مخصصة');
@endphp
<div class="bg-white rounded-2xl border border-sky-200 shadow-sm overflow-hidden">
    <!-- Header (Other / Custom Service Template) -->
    <div class="bg-gradient-to-l from-slate-900 via-sky-900 to-blue-900 text-white p-5 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-white/15 text-[11px] font-bold mb-1.5">
                    ✨ فاتورة خدمة — {{ $customType }}
                </div>
                <h2 class="text-lg sm:text-xl font-black">{{ $provider->business_name }}</h2>
                <p class="text-xs text-sky-100 mt-0.5">{{ $provider->name }} · {{ $provider->phone }}</p>
            </div>
            <div class="text-right sm:text-left">
                <div class="text-[11px] text-sky-200 font-bold">رقم الفاتورة</div>
                <div class="font-mono text-base font-black">{{ $invoice->invoice_number }}</div>
                <div class="text-[11px] text-sky-200 mt-0.5">{{ $invoice->created_at->format('Y-m-d') }}</div>
            </div>
        </div>
    </div>

    <div class="p-5 sm:p-6 space-y-5">
        <!-- Customer & Status -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 bg-sky-50/70 border border-sky-100 rounded-xl p-3.5 text-xs">
            <div>
                <div class="text-slate-500 font-bold">بيانات العميل</div>
                <div class="text-sm font-black text-slate-900 mt-0.5">{{ $invoice->customer_name }}</div>
                <div class="text-slate-600 mt-0.5" dir="ltr">{{ $invoice->customer_phone }}</div>
            </div>
            <div class="sm:text-left">
                <div class="text-slate-500 font-bold">حالة الفاتورة</div>
                <div class="mt-1">
                    @if($invoice->isPaid())
                        <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 font-extrabold text-xs">✅ مدفوعة</span>
                    @else
                        <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-800 font-extrabold text-xs">⏳ {{ $invoice->statusLabel() }}</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Custom Service Details -->
        <div>
            <h3 class="text-sm font-black text-slate-900 mb-2">{{ $invoice->title }}</h3>
            @if($invoice->description)
                <p class="text-xs text-slate-600 leading-relaxed mb-3">{{ $invoice->description }}</p>
            @endif

            @if(!empty($details))
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 text-xs">
                    @if(!empty($details['custom_service_type']))
                        <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-100">
                            <span class="text-slate-400 block text-[11px] font-bold">نوع الخدمة</span>
                            <span class="font-extrabold text-slate-800">{{ $details['custom_service_type'] }}</span>
                        </div>
                    @endif
                    @if(!empty($details['execution_date']))
                        <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-100">
                            <span class="text-slate-400 block text-[11px] font-bold">تاريخ التنفيذ</span>
                            <span class="font-extrabold text-slate-800">{{ $details['execution_date'] }}</span>
                        </div>
                    @endif
                    @if(!empty($details['location']))
                        <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-100">
                            <span class="text-slate-400 block text-[11px] font-bold">الموقع / المدينة</span>
                            <span class="font-extrabold text-slate-800">{{ $details['location'] }}</span>
                        </div>
                    @endif
                    @if(!empty($details['deliverables']))
                        <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-100">
                            <span class="text-slate-400 block text-[11px] font-bold">تفاصيل التسليم</span>
                            <span class="font-extrabold text-slate-800">{{ $details['deliverables'] }}</span>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <!-- Amount Summary -->
        <div class="rounded-xl bg-slate-900 text-white p-4 flex items-center justify-between">
            <div>
                <div class="text-xs text-sky-300 font-bold">قيمة الفاتورة</div>
                @if($invoice->notes)
                    <div class="text-[11px] text-slate-300 mt-0.5">{{ $invoice->notes }}</div>
                @endif
            </div>
            <div class="text-2xl font-black">{{ number_format((float) $invoice->amount, 2) }} <span class="text-xs font-bold">ر.س</span></div>
        </div>
    </div>
</div>
