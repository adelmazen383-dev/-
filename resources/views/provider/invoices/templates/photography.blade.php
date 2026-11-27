@php
    $details = $invoice->service_details ?? [];
@endphp
<div class="bg-white rounded-2xl border border-amber-300/70 shadow-sm overflow-hidden">
    <!-- Header (Photography Studio Template) -->
    <div class="bg-gradient-to-l from-zinc-900 via-neutral-900 to-amber-950 text-white p-5 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-amber-400/20 text-amber-200 text-[11px] font-bold mb-1.5">
                    📸 فاتورة خدمات التصوير
                </div>
                <h2 class="text-lg sm:text-xl font-black text-amber-50">{{ $provider->business_name }}</h2>
                <p class="text-xs text-zinc-300 mt-0.5">{{ $provider->name }} · {{ $provider->phone }}</p>
            </div>
            <div class="text-right sm:text-left">
                <div class="text-[11px] text-amber-300 font-bold">رقم الفاتورة</div>
                <div class="font-mono text-base font-black text-white">{{ $invoice->invoice_number }}</div>
                <div class="text-[11px] text-zinc-400 mt-0.5">{{ $invoice->created_at->format('Y-m-d') }}</div>
            </div>
        </div>
    </div>

    <div class="p-5 sm:p-6 space-y-5">
        <!-- Customer & Status -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 bg-amber-50/70 border border-amber-200/70 rounded-xl p-3.5 text-xs">
            <div>
                <div class="text-slate-500 font-bold">العميل</div>
                <div class="text-sm font-black text-slate-900 mt-0.5">{{ $invoice->customer_name }}</div>
                <div class="text-slate-600 mt-0.5" dir="ltr">{{ $invoice->customer_phone }}</div>
            </div>
            <div class="sm:text-left">
                <div class="text-slate-500 font-bold">الحالة</div>
                <div class="mt-1">
                    @if($invoice->isPaid())
                        <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 font-extrabold text-xs">✅ مدفوعة</span>
                    @else
                        <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-900 font-extrabold text-xs">⏳ {{ $invoice->statusLabel() }}</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Photography Details -->
        <div>
            <h3 class="text-sm font-black text-zinc-900 mb-2">{{ $invoice->title }}</h3>
            @if($invoice->description)
                <p class="text-xs text-slate-600 leading-relaxed mb-3">{{ $invoice->description }}</p>
            @endif

            @if(!empty($details))
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 text-xs">
                    @if(!empty($details['session_type']))
                        <div class="p-2.5 rounded-lg bg-zinc-50 border border-zinc-200/70">
                            <span class="text-zinc-500 block text-[11px] font-bold">نوع التصوير</span>
                            <span class="font-extrabold text-zinc-900">{{ $details['session_type'] }}</span>
                        </div>
                    @endif
                    @if(!empty($details['session_date']))
                        <div class="p-2.5 rounded-lg bg-zinc-50 border border-zinc-200/70">
                            <span class="text-zinc-500 block text-[11px] font-bold">تاريخ الجلسة</span>
                            <span class="font-extrabold text-zinc-900">{{ $details['session_date'] }}</span>
                        </div>
                    @endif
                    @if(!empty($details['duration_hours']))
                        <div class="p-2.5 rounded-lg bg-zinc-50 border border-zinc-200/70">
                            <span class="text-zinc-500 block text-[11px] font-bold">مدة التغطية / التصوير</span>
                            <span class="font-extrabold text-zinc-900">{{ $details['duration_hours'] }}</span>
                        </div>
                    @endif
                    @if(!empty($details['location']))
                        <div class="p-2.5 rounded-lg bg-zinc-50 border border-zinc-200/70">
                            <span class="text-zinc-500 block text-[11px] font-bold">موقع التصوير</span>
                            <span class="font-extrabold text-zinc-900">{{ $details['location'] }}</span>
                        </div>
                    @endif
                    @if(!empty($details['deliverables']))
                        <div class="sm:col-span-2 p-2.5 rounded-lg bg-zinc-50 border border-zinc-200/70">
                            <span class="text-zinc-500 block text-[11px] font-bold">المخرجات وموعد التسليم</span>
                            <span class="font-extrabold text-zinc-900">{{ $details['deliverables'] }}</span>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <!-- Amount Summary -->
        <div class="rounded-xl bg-zinc-900 text-white p-4 flex items-center justify-between border-r-4 border-amber-400">
            <div>
                <div class="text-xs text-amber-300 font-bold">قيمة باقة التصوير</div>
                @if($invoice->notes)
                    <div class="text-[11px] text-zinc-300 mt-0.5">{{ $invoice->notes }}</div>
                @endif
            </div>
            <div class="text-2xl font-black text-amber-300">{{ number_format((float) $invoice->amount, 2) }} <span class="text-xs font-bold text-white">ر.س</span></div>
        </div>
    </div>
</div>
