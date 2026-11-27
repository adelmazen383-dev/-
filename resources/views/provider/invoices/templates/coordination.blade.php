@php
    $details = $invoice->service_details ?? [];
@endphp
<div class="bg-white rounded-2xl border border-fuchsia-200 shadow-sm overflow-hidden">
    <!-- Header (Coordination Template) -->
    <div class="bg-gradient-to-l from-fuchsia-800 via-purple-800 to-rose-700 text-white p-5 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-white/15 text-[11px] font-bold mb-1.5">
                    🎀 فاتورة خدمة تنسيق
                </div>
                <h2 class="text-lg sm:text-xl font-black">{{ $provider->business_name }}</h2>
                <p class="text-xs text-fuchsia-100 mt-0.5">{{ $provider->name }} · {{ $provider->phone }}</p>
            </div>
            <div class="text-right sm:text-left">
                <div class="text-[11px] text-fuchsia-200 font-bold">رقم الفاتورة</div>
                <div class="font-mono text-base font-black">{{ $invoice->invoice_number }}</div>
                <div class="text-[11px] text-fuchsia-100 mt-0.5">{{ $invoice->created_at->format('Y-m-d') }}</div>
            </div>
        </div>
    </div>

    <div class="p-5 sm:p-6 space-y-5">
        <!-- Customer & Status -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 bg-fuchsia-50/60 border border-fuchsia-100 rounded-xl p-3.5 text-xs">
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

        <!-- Coordination Details -->
        <div>
            <h3 class="text-sm font-black text-fuchsia-950 mb-2">{{ $invoice->title }}</h3>
            @if($invoice->description)
                <p class="text-xs text-slate-600 leading-relaxed mb-3">{{ $invoice->description }}</p>
            @endif

            @if(!empty($details))
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 text-xs">
                    @if(!empty($details['event_type']))
                        <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-100">
                            <span class="text-slate-400 block text-[11px] font-bold">نوع المناسبة</span>
                            <span class="font-extrabold text-slate-800">{{ $details['event_type'] }}</span>
                        </div>
                    @endif
                    @if(!empty($details['event_date']))
                        <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-100">
                            <span class="text-slate-400 block text-[11px] font-bold">تاريخ المناسبة</span>
                            <span class="font-extrabold text-slate-800">{{ $details['event_date'] }}</span>
                        </div>
                    @endif
                    @if(!empty($details['venue']))
                        <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-100">
                            <span class="text-slate-400 block text-[11px] font-bold">الموقع / المكان</span>
                            <span class="font-extrabold text-slate-800">{{ $details['venue'] }}</span>
                        </div>
                    @endif
                    @if(!empty($details['theme']))
                        <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-100">
                            <span class="text-slate-400 block text-[11px] font-bold">عناصر وألوان التنسيق</span>
                            <span class="font-extrabold text-slate-800">{{ $details['theme'] }}</span>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <!-- Amount Summary -->
        <div class="rounded-xl bg-fuchsia-950 text-white p-4 flex items-center justify-between">
            <div>
                <div class="text-xs text-fuchsia-200 font-bold">قيمة الخدمة</div>
                @if($invoice->notes)
                    <div class="text-[11px] text-fuchsia-100/80 mt-0.5">{{ $invoice->notes }}</div>
                @endif
            </div>
            <div class="text-2xl font-black">{{ number_format((float) $invoice->amount, 2) }} <span class="text-xs font-bold">ر.س</span></div>
        </div>
    </div>
</div>
