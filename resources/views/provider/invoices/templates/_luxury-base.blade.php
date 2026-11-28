@php
    $agreed = $invoice->effectiveAgreedAmount();
    $deposit = round((float) $invoice->deposit_amount, 2);
    $paidPrev = round((float) $invoice->paid_previously, 2);
    $remaining = $invoice->effectiveRemainingAmount();
    $dueNow = round((float) $invoice->amount, 2);
    $showMethodsStrip = $showMethodsStrip ?? true;
    $attachmentUrl = $attachmentUrl ?? null;
@endphp

<div class="bg-white rounded-[24px] border border-slate-200/90 shadow-lg overflow-hidden max-w-xl mx-auto">
    <!-- Top Obsidian & Gold Header -->
    <div class="bg-[#141311] text-white px-6 py-6 text-center relative">
        <div class="flex flex-col items-center justify-center">
            <img src="{{ asset('images/thiqa-gold-logo.png') }}" alt="منصة ثقة"
                 class="w-16 h-16 sm:w-20 sm:h-20 object-contain mb-2.5 drop-shadow">
            <h2 class="text-xl sm:text-2xl font-black tracking-tight text-white">فاتورة إلكترونية</h2>
            <div class="text-[11px] font-bold tracking-[0.22em] text-[#c8a464] uppercase mt-1">
                ELECTRONIC INVOICE
            </div>
            <div class="mt-2 inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full bg-white/5 border border-[#c8a464]/30 text-[11px] text-[#e5cfa0] font-bold">
                <span>منصة ثقة</span>
                <span class="opacity-50">•</span>
                <span>{{ $sectionBadge ?? $provider->service_type_label }}</span>
            </div>
        </div>
    </div>

    <div class="p-5 sm:p-7 space-y-6">
        <!-- Invoice Number & Issue Date Row -->
        <div class="flex items-start justify-between gap-4 pb-4 border-b border-slate-100">
            <div class="text-right">
                <div class="text-xs font-semibold text-slate-400">رقم الفاتورة</div>
                <div class="font-mono text-sm sm:text-base font-black text-slate-900 mt-0.5" dir="ltr">{{ $invoice->invoice_number }}</div>
            </div>
            <div class="text-left">
                <div class="text-xs font-semibold text-slate-400">تاريخ الإصدار</div>
                <div class="font-mono text-sm sm:text-base font-black text-slate-900 mt-0.5" dir="ltr">{{ $invoice->created_at->format('d/m/Y') }}</div>
            </div>
        </div>

        <!-- Section 1: Customer & Service Details Table -->
        <div>
            <h3 class="text-base font-black text-slate-900 mb-3">{{ $sectionTitle ?? 'بيانات العميل' }}</h3>
            <div class="overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-xs text-slate-500">
                            <th class="py-2 text-right font-extrabold">البيان</th>
                            <th class="py-2 text-left font-extrabold">التفاصيل</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($rows as $label =>$value)
                            @if($value !== null && $value !== '')
                                <tr>
                                    <td class="py-2.5 text-slate-500 font-semibold text-xs sm:text-sm whitespace-nowrap pl-3">{{ $label }}</td>
                                    <td class="py-2.5 text-left font-bold text-slate-900 text-xs sm:text-sm break-words">{{ $value }}</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Section 2: Financial Details -->
        <div class="pt-2 border-t border-slate-100">
            <h3 class="text-base font-black text-slate-900 mb-3">تفاصيل المبالغ</h3>
            <div class="space-y-2.5 text-sm">
                <div class="flex items-center justify-between">
                    <span class="text-slate-500 font-semibold">{{ $agreedLabel ?? 'المبلغ المتفق عليه' }}</span>
                    <span class="font-black text-slate-900">{{ number_format($agreed, 2) }} ر.س</span>
                </div>

                @if($deposit >0)
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-semibold">مبلغ التأمين</span>
                        <span class="font-black text-slate-900">{{ number_format($deposit, 2) }} ر.س</span>
                    </div>
                @endif

                <div class="flex items-center justify-between">
                    <span class="text-slate-500 font-semibold">المدفوع سابقاً</span>
                    <span class="font-black text-slate-900">{{ number_format($paidPrev, 2) }} ر.س</span>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-slate-500 font-semibold">المتبقي</span>
                    <span class="font-black text-slate-900">{{ number_format($remaining, 2) }} ر.س</span>
                </div>

                @if($invoice->paymentTypeLabel())
                    <div class="flex items-center justify-between pt-1">
                        <span class="text-slate-500 font-semibold">حالة سداد الحجز</span>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-extrabold {{ $invoice->payment_type === 'full' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-amber-50 text-amber-800 border border-amber-200' }}">
                            {{ $invoice->paymentTypeLabel() }}
                        </span>
                    </div>
                @endif

                @if($invoice->payment_method && $invoice->payment_method !== 'all')
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-semibold">طريقة الدفع المحددة</span>
                        <span class="font-extrabold text-slate-800">{{ $invoice->paymentMethodCleanLabel() }}</span>
                    </div>
                @endif
            </div>

            <!-- Black & Gold Hero Box: Amount Due Now -->
            <div class="mt-5 rounded-2xl bg-[#141311] text-white p-5 text-center sm:text-right border border-[#c8a464]/30 shadow-md">
                <div class="text-xs sm:text-sm font-bold text-[#c8a464]">المبلغ المطلوب دفعه الآن</div>
                <div class="text-3xl sm:text-4xl font-black text-white mt-1.5 tracking-tight">
                    {{ number_format($dueNow, 2) }} <span class="text-lg font-extrabold text-[#e5cfa0]">ر.س</span>
                </div>
                <div class="mt-3">
                    @if($invoice->isPaid())
                        <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-emerald-500/20 border border-emerald-400/40 text-emerald-300 text-xs font-extrabold">
                            تم الدفع بنجاح
                        </span>
                    @elseif($invoice->status === 'failed')
                        <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-rose-500/20 border border-rose-400/40 text-rose-300 text-xs font-extrabold">
                            لم تكتمل المحاولة
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-amber-600/25 border border-amber-500/35 text-amber-300 text-xs font-extrabold">
                            بانتظار الدفع
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Optional Notes & Quote Attachment -->
        @if($invoice->notes || ($invoice->quote_attachment_path && $attachmentUrl))
            <div class="space-y-2.5 pt-1">
                @if($invoice->notes)
                    <div class="rounded-xl bg-slate-50 border border-slate-200/80 p-3.5 text-xs">
                        <div class="font-extrabold text-slate-700 mb-1">ملاحظات وطلبات خاصة</div>
                        <p class="text-slate-600 leading-relaxed whitespace-pre-line">{{ $invoice->notes }}</p>
                    </div>
                @endif

                @if($invoice->quote_attachment_path && $attachmentUrl)
                    @php
                        $attachIsImage = \App\Services\ProviderDocumentService::isImage($invoice->quote_attachment_path);
                    @endphp
                    <div class="rounded-xl border border-amber-200 bg-amber-50/50 overflow-hidden">
                        <div class="px-4 py-2.5 flex items-center justify-between gap-2 border-b border-amber-200/70">
                            <span class="text-xs font-extrabold text-amber-950">
                                {{ $attachIsImage ? 'صورة الفاتورة / عرض السعر المرفق' : 'ملف الفاتورة / عرض السعر المرفق (PDF)' }}
                            </span>
                            <div class="flex items-center gap-2 shrink-0">
                                <a href="{{ $attachmentUrl }}" target="_blank"
                                   class="px-2.5 py-1 rounded-lg bg-[#141311] text-[#e5cfa0] text-[11px] font-bold">
                                    فتح كامل ↗
                                </a>
                                <a href="{{ $attachmentUrl }}?download=1"
                                   class="px-2.5 py-1 rounded-lg bg-white border border-amber-300 text-amber-950 text-[11px] font-bold">
                                    تحميل 
                                </a>
                            </div>
                        </div>
                        @if($attachIsImage)
                            <a href="{{ $attachmentUrl }}" target="_blank" class="block p-3 bg-white text-center">
                                <img src="{{ $attachmentUrl }}" alt="عرض السعر المرفق" loading="lazy"
                                     class="max-h-72 mx-auto rounded-lg object-contain border border-slate-100">
                            </a>
                        @else
                            <div class="px-4 py-3 bg-white flex items-center justify-between gap-3 text-xs">
                                <span class="text-slate-600 font-semibold">اضغط «فتح كامل» لعرض ملف PDF مباشرة على جهازك أو «تحميل» لحفظه.</span>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        @endif

        <!-- Payment Methods 2x2 Clean Strip (Shown on print/preview; interactive version is on /p-pay/{token}) -->
        @if($showMethodsStrip)
            <div>
                <h3 class="text-sm font-black text-slate-900 mb-3">وسائل الدفع</h3>
                <div class="grid grid-cols-2 gap-2.5 text-center text-xs sm:text-sm font-extrabold text-slate-800">
                    <div class="py-3 px-3 rounded-xl border border-slate-200 bg-white">Apple Pay</div>
                    <div class="py-3 px-3 rounded-xl border border-slate-200 bg-white">Tabby</div>
                    <div class="py-3 px-3 rounded-xl border border-slate-200 bg-white">Tamara</div>
                    <div class="py-3 px-3 rounded-xl border border-slate-200 bg-white">مدى / فيزا</div>
                </div>
            </div>
        @endif

        <!-- Luxury Light-Gray Platform Footer Box -->
        <div class="rounded-2xl bg-[#f3f4f6] px-5 py-5 text-center border border-slate-200/60">
            <img src="{{ asset('images/thiqa-gold-logo.png') }}" alt="منصة ثقة"
                 class="w-10 h-10 object-contain mx-auto mb-1.5">
            <div class="text-base font-black text-slate-900 tracking-wide">منصة ثقة | THIQA</div>
            <div class="text-xs font-bold text-slate-600 mt-0.5">{{ $provider->business_name }}</div>
            <div class="text-xs text-slate-500 mt-2">شكراً لثقتكم بنا</div>
            <div class="text-xs text-slate-500">نتمنى لكم تجربة مميزة</div>
        </div>
    </div>
</div>
