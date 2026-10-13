<x-app-layout>
    <x-slot name="header">تفاصيل الفاتورة #{{ $invoice->invoice_number }}</x-slot>

    <!-- Top Action Bar for Smart Card -->
    <div class="mb-5 p-3.5 rounded-xl bg-amber-50 border border-amber-200 flex flex-col md:flex-row items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-lg bg-amber-100 text-amber-800 flex items-center justify-center text-lg font-bold shrink-0">
                🪪
            </div>
            <div>
                <h2 class="text-sm font-bold text-slate-800 leading-tight">كارت حجز الوحدة السكنية الذكي جاهز</h2>
                <p class="text-xs text-slate-500">تم حفظ الفاتورة تلقائياً ويمكنك استعراض الكارت، طباعته، أو إرساله للعميل</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('invoices.booking-card', $invoice) }}" target="_blank"
               class="btn btn-sm" style="background: #c59b63; color: white; border: none; font-weight:700;">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                فتح الكارت (طباعة / PNG)
            </a>

            @php
                $cleanPhone = preg_replace('/[^0-9]/', '', $invoice->phone);
                if (str_starts_with($cleanPhone, '05')) $cleanPhone = '966' . substr($cleanPhone, 1);
                elseif (str_starts_with($cleanPhone, '01')) $cleanPhone = '20' . substr($cleanPhone, 1);
                elseif (str_starts_with($cleanPhone, '5')) $cleanPhone = '966' . $cleanPhone;

                $cardLink = route('booking-card.show', $invoice->booking_card_token);
                $payLink  = $invoice->telr_transaction_url;

                $whatsappMessage = "مرحباً أستاذ/ة " . $invoice->customer_name . "،\n"
                                 . "يسعدنا تأكيد حجزكم عبر *منصة توثيق العقود* 📑\n\n"
                                 . "🪪 *كارت تفاصيل الحجز الذكي:*\n" . $cardLink . "\n\n";

                if ($invoice->isPending() && $payLink) {
                    $whatsappMessage .= "💳 *رابط الدفع الإلكتروني المباشر:*\n" . $payLink . "\n\n";
                }
                $whatsappMessage .= "شكراً لاختياركم منصتنا.";
                $whatsappLink = "https://wa.me/" . $cleanPhone . "?text=" . urlencode($whatsappMessage);
            @endphp

            <a href="{{ $whatsappLink }}" target="_blank"
               class="btn btn-sm" style="background: #25d366; color: white; border: none; font-weight:700;">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.625.846 5.059 2.284 7.034L.789 23.492a.5.5 0 00.613.613l4.458-1.495A11.952 11.952 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-2.352 0-4.55-.758-6.336-2.065l-.442-.332-3.262 1.093 1.093-3.262-.332-.442A9.96 9.96 0 012 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/></svg>
                واتساب
            </a>

            <button type="button" onclick="navigator.clipboard.writeText('{{ $cardLink }}').then(() => alert('تم نسخ رابط الكارت الذكي!'))" class="btn btn-ghost btn-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                نسخ الرابط
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-5">

            <!-- Status Box -->
            @if($invoice->isPaid())
                <div class="bg-emerald-50 border border-emerald-200 p-3 rounded-xl text-center">
                    <p class="text-emerald-700 text-sm font-bold">✅ تم الدفع بنجاح</p>
                    @if($invoice->paid_at)
                        <p class="text-emerald-600 text-xs mt-0.5">{{ $invoice->paid_at->format('Y/m/d H:i') }}</p>
                    @endif
                </div>
            @elseif($invoice->isPending())
                <div class="bg-amber-50 border border-amber-200 p-3 rounded-xl text-center">
                    <p class="text-amber-800 text-sm font-bold">⏳ بانتظار الدفع</p>
                </div>
            @else
                <div class="bg-rose-50 border border-rose-200 p-3 rounded-xl text-center">
                    <p class="text-rose-700 text-sm font-bold">❌ {{ $invoice->statusLabel() }}</p>
                </div>
            @endif

            <!-- Invoice Details Card -->
            <div class="card">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
                    <div>
                        <p class="text-slate-600 text-sm">رقم الفاتورة</p>
                        <p class="text-2xl font-black text-indigo-500 font-mono">{{ $invoice->invoice_number }}</p>
                    </div>
                    <span class="badge {{ $invoice->statusBadgeClass() }}" style="font-size:14px;padding:6px 16px;">
                        {{ $invoice->statusLabel() }}
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-y-5 gap-x-6">
                    <div>
                        <p class="text-slate-500 text-xs font-bold mb-1">اسم العميل</p>
                        <p class="text-slate-800 font-semibold">{{ $invoice->customer_name }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500 text-xs font-bold mb-1">رقم الهاتف</p>
                        <p class="text-slate-700 font-mono" dir="ltr">{{ $invoice->phone }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500 text-xs font-bold mb-1">الرقم القومي / الهوية</p>
                        <p class="text-slate-700 font-mono">{{ $invoice->national_id }}</p>
                    </div>

                    <div>
                        <p class="text-slate-500 text-xs font-bold mb-1">رقم الجناح / الشقة</p>
                        <p class="text-slate-800 font-bold text-lg">{{ $invoice->suite_number ?: $invoice->apartment_number }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500 text-xs font-bold mb-1">تاريخ الدخول</p>
                        <p class="text-slate-700 font-semibold">{{ $invoice->check_in_date ? $invoice->check_in_date->format('Y/m/d') : '—' }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500 text-xs font-bold mb-1">تاريخ الخروج</p>
                        <p class="text-slate-700 font-semibold">{{ $invoice->check_out_date ? $invoice->check_out_date->format('Y/m/d') : '—' }}</p>
                    </div>

                    <div>
                        <p class="text-slate-500 text-xs font-bold mb-1">المبلغ المتفق عليه</p>
                        <p class="text-slate-800 font-bold text-lg">{{ number_format($invoice->agreed_amount ?? $invoice->amount, 2) }} ر.س</p>
                    </div>
                    <div>
                        <p class="text-slate-500 text-xs font-bold mb-1">المبلغ المطلوب دفعه (الفاتورة)</p>
                        <p class="text-emerald-500 font-bold text-lg">{{ number_format($invoice->amount, 2) }} ر.س</p>
                    </div>
                    <div>
                        <p class="text-slate-500 text-xs font-bold mb-1">المبلغ المتبقي</p>
                        <p class="text-rose-500 font-bold text-lg">{{ number_format($invoice->remaining_amount, 2) }} ر.س</p>
                    </div>

                    <div>
                        <p class="text-slate-500 text-xs font-bold mb-1">التأمين المسترد</p>
                        <p class="text-amber-600 font-bold">{{ number_format($invoice->deposit_amount ?? 500, 2) }} ر.س</p>
                    </div>
                    <div class="sm:col-span-2">
                        <p class="text-slate-500 text-xs font-bold mb-1">تاريخ إنشاء الفاتورة</p>
                        <p class="text-slate-700 text-sm">{{ $invoice->created_at->format('Y/m/d H:i') }}</p>
                    </div>
                </div>

                @if($invoice->notes)
                <div class="mt-5 pt-4 border-t border-slate-100">
                    <p class="text-slate-500 text-xs font-bold mb-1">ملاحظات إضافية</p>
                    <p class="text-slate-700 text-sm">{{ $invoice->notes }}</p>
                </div>
                @endif
            </div>

            <!-- ═══ Actions Card ═══ -->
            <div class="card">
                <h3 class="text-slate-800 font-bold mb-4">إجراءات الدفع والبوابة</h3>
                <div class="flex flex-wrap gap-3">

                    @if($invoice->isPending() && $invoice->telr_transaction_url)
                        <button type="button" onclick="navigator.clipboard.writeText('{{ $invoice->telr_transaction_url }}').then(() => alert('تم نسخ رابط الدفع!'))" class="btn btn-ghost btn-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                            نسخ رابط بوابة Telr
                        </button>

                        <form method="POST" action="{{ route('invoices.check-status', $invoice) }}" class="inline">
                            @csrf
                            <button type="submit" class="btn btn-indigo btn-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                التحقق من حالة الدفع من البوابة
                            </button>
                        </form>
                    @endif

                    @if($invoice->isPending() && !$invoice->telr_transaction_url)
                        <form method="POST" action="{{ route('invoices.retry-telr', $invoice) }}" class="inline">
                            @csrf
                            <button type="submit" class="btn btn-indigo btn-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                إعادة توليد رابط الدفع
                            </button>
                        </form>
                    @endif

                    @if($invoice->isPending())
                        <form method="POST" action="{{ route('invoices.mark-as-paid', $invoice) }}" class="inline" onsubmit="return confirm('هل تريد تسجيل هذه الفاتورة كمدفوعة يدوياً؟ سيتم تحديث المبلغ المتبقي.')">
                            @csrf
                            <button type="submit" class="btn btn-sm" style="background:#059669; color:white;">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                تسجيل كمدفوعة يدوياً (كاش / تحويل)
                            </button>
                        </form>
                    @endif

                    @if($invoice->status === 'failed')
                        <form method="POST" action="{{ route('invoices.retry-telr', $invoice) }}" class="inline">
                            @csrf
                            <button type="submit" class="btn btn-indigo btn-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                إعادة المحاولة — رابط دفع جديد
                            </button>
                        </form>
                    @endif

                    @if($invoice->isPaid())
                        <a href="{{ route('contracts.create') }}" class="btn btn-indigo btn-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            إنشاء عقد إيجار لهذا العميل
                        </a>
                    @endif
                </div>

                {{-- Context message --}}
                @if($invoice->isPending() && $invoice->telr_transaction_url)
                <div class="mt-4 p-3 rounded-xl bg-amber-500/10 border border-amber-500/20">
                    <p class="text-sm font-semibold text-amber-700">
                        📤 يمكنك إرسال الكارت الذكي للعميل عبر الواتساب متضمناً رابط السداد، واضغط "التحقق من حالة الدفع" لتحديث الحالة تلقائياً بعد السداد.
                    </p>
                </div>
                @elseif($invoice->isPending() && !$invoice->telr_transaction_url)
                <div class="mt-4 p-3 rounded-xl bg-rose-500/10 border border-rose-500/20">
                    <p class="text-sm font-semibold text-rose-500">
                        ⚠️ لم يتم توليد رابط الدفع بعد. تأكد من إعدادات بوابة Telr في ملف .env ثم اضغط "إعادة توليد رابط الدفع".
                    </p>
                </div>
                @elseif($invoice->isPaid())
                <div class="mt-4 p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20">
                    <p class="text-sm font-semibold text-emerald-600">
                        🎉 تم الدفع بنجاح! يمكنك الآن إنشاء العقد الموثق للعميل.
                    </p>
                </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1 space-y-6">
            <div class="card">
                <h3 class="text-slate-800 font-bold mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    بيانات بوابة الدفع (Telr)
                </h3>
                <div class="space-y-4 text-sm">
                    <div>
                        <p class="text-slate-500 text-xs font-bold mb-1">الرقم المرجعي (Telr Order Ref)</p>
                        <p class="text-slate-700 font-mono text-xs break-all">{{ $invoice->telr_order_ref ?? 'لم يتم التوليد بعد' }}</p>
                    </div>
                    @if($invoice->telr_transaction_url)
                    <div>
                        <p class="text-slate-500 text-xs font-bold mb-1">رابط الدفع المباشر</p>
                        <a href="{{ $invoice->telr_transaction_url }}" target="_blank" class="text-indigo-600 text-xs underline break-all">{{ Str::limit($invoice->telr_transaction_url, 45) }}</a>
                    </div>
                    @endif
                    <div>
                        <p class="text-slate-500 text-xs font-bold mb-1">رمز الكارت الذكي (Token)</p>
                        <p class="text-slate-500 font-mono text-[11px] break-all">{{ $invoice->booking_card_token }}</p>
                    </div>
                </div>
            </div>

            <div class="card bg-slate-50 border-dashed">
                <h4 class="text-xs font-bold text-slate-700 mb-2">تعليمات الكارت والفاتورة:</h4>
                <ul class="text-xs text-slate-500 space-y-1.5 list-disc list-inside">
                    <li>الكارت محفوظ في النظام تلقائياً.</li>
                    <li>يمكنك تحميله كصورة PNG بجودة عالية لأي عميل.</li>
                    <li>إرسال الكارت للعميل اختياري (عبر الواتساب أو نسخ الرابط).</li>
                    <li>بوابة الدفع تحدث حالة الفاتورة فور اكتمال السداد.</li>
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
