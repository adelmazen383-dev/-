<x-app-layout>
    <x-slot name="header">تفاصيل العقد #{{ $contract->contract_number }}</x-slot>



    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Contract Header Card -->
            <div class="card">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
                    <div>
                        <p class="text-slate-400 text-sm">رقم العقد</p>
                        <p class="text-2xl font-black text-indigo-400 font-mono">{{ $contract->contract_number }}</p>
                    </div>
                    <span class="badge {{ $contract->status->badgeClass() }}" style="font-size:14px;padding:6px 16px;">
                        {{ $contract->status->label() }}
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-5 gap-x-8">
                    <div>
                        <p class="text-slate-500 text-xs font-bold mb-1">اسم العميل</p>
                        <p class="text-white font-semibold">{{ $contract->customer->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500 text-xs font-bold mb-1">رقم الهوية</p>
                        <p class="text-slate-300 font-mono">{{ $contract->customer->national_id ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500 text-xs font-bold mb-1">تاريخ البداية</p>
                        <p class="text-slate-300">{{ $contract->start_date?->format('Y/m/d') ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500 text-xs font-bold mb-1">تاريخ النهاية</p>
                        <p class="text-slate-300">{{ $contract->end_date?->format('Y/m/d') ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500 text-xs font-bold mb-1">قيمة الإيجار</p>
                        <p class="text-emerald-400 font-bold text-lg">{{ number_format($contract->rent_amount) }} ر.س</p>
                    </div>
                    <div>
                        <p class="text-slate-500 text-xs font-bold mb-1">طريقة الدفع</p>
                        <p class="text-slate-300">{{ $contract->payment_method instanceof \App\Enums\PaymentMethod ? $contract->payment_method->label() : $contract->payment_method }}</p>
                    </div>
                </div>

                @if($contract->property_details)
                <div class="mt-5 pt-5 border-t border-white/5">
                    <p class="text-slate-500 text-xs font-bold mb-2">وصف العقار</p>
                    <p class="text-slate-300 text-sm leading-relaxed">{{ $contract->property_details }}</p>
                </div>
                @endif

                @if($contract->additional_terms)
                <div class="mt-4 pt-4 border-t border-white/5">
                    <p class="text-slate-500 text-xs font-bold mb-2">شروط إضافية</p>
                    <p class="text-slate-300 text-sm leading-relaxed">{{ $contract->additional_terms }}</p>
                </div>
                @endif
            </div>

            <!-- Actions Card -->
            <div class="card">
                <h3 class="text-white font-bold mb-4">إجراءات</h3>
                <div class="flex flex-wrap gap-3">
                    @if($contract->pdf_path)
                        <a href="{{ asset($contract->pdf_path) }}" target="_blank" class="btn btn-ghost btn-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            تحميل المسودة PDF
                        </a>
                    @endif

                    @if($contract->signed_pdf_path)
                        <a href="{{ asset($contract->signed_pdf_path) }}" target="_blank" class="btn btn-emerald btn-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            تحميل العقد الموقّع
                        </a>
                    @endif

                    @if(!$contract->isTerminal())
                        <button type="button" onclick="navigator.clipboard.writeText('{{ route('sign.show', $contract->verification_token) }}').then(() => alert('تم نسخ الرابط بنجاح!'))" class="btn btn-sm" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; border: none;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                            نسخ رابط التوقيع
                        </button>
                        @php
                            $phone = preg_replace('/[^0-9]/', '', $contract->customer->phone ?? '');
                            if (str_starts_with($phone, '05')) $phone = '966' . substr($phone, 1);
                            elseif (str_starts_with($phone, '01')) $phone = '20' . substr($phone, 1);
                            elseif (str_starts_with($phone, '5')) $phone = '966' . $phone;
                            
                            $whatsappMessage = "مرحباً " . ($contract->customer->name ?? '') . "،\n\nنرجو مراجعة عقد الإيجار وتوقيعه عبر الرابط التالي:\n" . route('sign.show', $contract->verification_token);
                            $whatsappLink = "https://wa.me/" . $phone . "?text=" . urlencode($whatsappMessage);
                        @endphp
                        <a href="{{ $whatsappLink }}" target="_blank" class="btn btn-indigo btn-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            مراسلة واتساب مباشر
                        </a>

                        <form method="POST" action="{{ route('contracts.cancel', $contract) }}" onsubmit="return confirm('هل أنت متأكد من إلغاء هذا العقد؟')">
                            @csrf
                            <button type="submit" class="btn btn-rose btn-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                إلغاء العقد
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar: Audit Trail -->
        <div class="lg:col-span-1">
            <div class="card">
                <h3 class="text-white font-bold mb-5 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    سجل الأحداث
                </h3>

                <div class="space-y-0">
                    @forelse($contract->logs as $log)
                        <div class="relative pr-6 pb-6 {{ !$loop->last ? 'border-r-2 border-white/5' : '' }}">
                            <div class="absolute right-0 top-0 w-3 h-3 rounded-full bg-indigo-500 -translate-x-[5px] ring-4 ring-slate-800"></div>
                            <p class="text-slate-300 text-sm font-semibold">{{ $log->event }}</p>
                            <p class="text-slate-600 text-xs mt-1">{{ $log->created_at->diffForHumans() }}</p>
                        </div>
                    @empty
                        <p class="text-slate-500 text-sm text-center py-4">لا توجد أحداث مسجلة بعد</p>
                    @endforelse
                </div>
            </div>

            <!-- QR Code Card -->
            <div class="card mt-6 text-center">
                <h3 class="text-white font-bold mb-4 flex items-center justify-center gap-2">
                    <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    رمز التحقق (QR)
                </h3>
                <div class="bg-white p-3 rounded-xl inline-block mx-auto mb-2 shadow-lg">
                    {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(140)->margin(1)->generate(route('verify.index') . '?contract_number=' . $contract->contract_number) !!}
                </div>
                <p class="text-slate-400 text-xs mt-2 leading-relaxed">امسح الرمز بكاميرا الجوال<br>للتحقق من حالة وصلاحية العقد</p>
            </div>
        </div>
    </div>
</x-app-layout>
