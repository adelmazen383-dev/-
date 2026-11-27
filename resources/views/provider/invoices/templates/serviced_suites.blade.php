@php
    $details = $invoice->service_details ?? [];
@endphp
<div class="bg-white rounded-2xl border border-emerald-200 shadow-sm overflow-hidden">
    <!-- Header (Serviced Hotel Suites / Property Template) -->
    <div class="bg-gradient-to-l from-emerald-950 via-emerald-900 to-teal-900 text-white p-5 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-amber-300/20 text-amber-200 text-[11px] font-bold mb-1.5">
                    🏨 فاتورة {{ $provider->property_kind ?: 'أجنحة فندقية مخدومة' }}
                </div>
                <h2 class="text-lg sm:text-xl font-black">{{ $provider->business_name }}</h2>
                <p class="text-xs text-emerald-100 mt-0.5">{{ $provider->name }} · {{ $provider->phone }}</p>
            </div>
            <div class="text-right sm:text-left">
                <div class="text-[11px] text-emerald-200 font-bold">رقم الفاتورة</div>
                <div class="font-mono text-base font-black">{{ $invoice->invoice_number }}</div>
                <div class="text-[11px] text-emerald-200 mt-0.5">{{ $invoice->created_at->format('Y-m-d') }}</div>
            </div>
        </div>
    </div>

    <div class="p-5 sm:p-6 space-y-5">
        <!-- Guest & Status -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 bg-emerald-50/70 border border-emerald-100 rounded-xl p-3.5 text-xs">
            <div>
                <div class="text-slate-500 font-bold">بيانات النزيل / العميل</div>
                <div class="text-sm font-black text-slate-900 mt-0.5">{{ $invoice->customer_name }}</div>
                <div class="text-slate-600 mt-0.5" dir="ltr">{{ $invoice->customer_phone }}</div>
            </div>
            <div class="sm:text-left">
                <div class="text-slate-500 font-bold">حالة الحجز والفاتورة</div>
                <div class="mt-1">
                    @if($invoice->isPaid())
                        <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 font-extrabold text-xs">✅ مدفوعة ومؤكدة</span>
                    @else
                        <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-800 font-extrabold text-xs">⏳ {{ $invoice->statusLabel() }}</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Stay Details -->
        <div>
            <h3 class="text-sm font-black text-emerald-950 mb-2">{{ $invoice->title }}</h3>
            @if($invoice->description)
                <p class="text-xs text-slate-600 leading-relaxed mb-3">{{ $invoice->description }}</p>
            @endif

            @if(!empty($details))
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 text-xs">
                    @if(!empty($details['property_name']))
                        <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-100">
                            <span class="text-slate-400 block text-[11px] font-bold">اسم العقار / البرج</span>
                            <span class="font-extrabold text-slate-800">{{ $details['property_name'] }}</span>
                        </div>
                    @endif
                    @if(!empty($details['suite_number']))
                        <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-100">
                            <span class="text-slate-400 block text-[11px] font-bold">رقم الجناح / الوحدة</span>
                            <span class="font-extrabold text-slate-800">{{ $details['suite_number'] }}</span>
                        </div>
                    @endif
                    @if(!empty($details['check_in_date']))
                        <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-100">
                            <span class="text-slate-400 block text-[11px] font-bold">تاريخ الدخول</span>
                            <span class="font-extrabold text-slate-800">{{ $details['check_in_date'] }}</span>
                        </div>
                    @endif
                    @if(!empty($details['check_out_date']))
                        <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-100">
                            <span class="text-slate-400 block text-[11px] font-bold">تاريخ الخروج</span>
                            <span class="font-extrabold text-slate-800">{{ $details['check_out_date'] }}</span>
                        </div>
                    @endif
                    @if(!empty($details['guests_count']))
                        <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-100">
                            <span class="text-slate-400 block text-[11px] font-bold">عدد النزلاء</span>
                            <span class="font-extrabold text-slate-800">{{ $details['guests_count'] }}</span>
                        </div>
                    @endif
                    @if(!empty($details['extras']))
                        <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-100">
                            <span class="text-slate-400 block text-[11px] font-bold">خدمات مشمولة</span>
                            <span class="font-extrabold text-slate-800">{{ $details['extras'] }}</span>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <!-- Amount Summary -->
        <div class="rounded-xl bg-emerald-950 text-white p-4 flex items-center justify-between">
            <div>
                <div class="text-xs text-emerald-200 font-bold">قيمة الإقامة والخدمة</div>
                @if($invoice->notes)
                    <div class="text-[11px] text-emerald-100/80 mt-0.5">{{ $invoice->notes }}</div>
                @endif
            </div>
            <div class="text-2xl font-black">{{ number_format((float) $invoice->amount, 2) }} <span class="text-xs font-bold">ر.س</span></div>
        </div>
    </div>
</div>
