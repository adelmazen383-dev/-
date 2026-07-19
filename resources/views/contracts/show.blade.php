<x-app-layout>
    <x-slot name="header">تفاصيل العقد #{{ $contract->contract_number }}</x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Contract Header Card -->
            <div class="card">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
                    <div>
                        <p class="text-slate-600 text-sm">رقم العقد</p>
                        <p class="text-2xl font-black text-indigo-400 font-mono">{{ $contract->contract_number }}</p>
                    </div>
                    <span class="badge {{ $contract->status->badgeClass() }}" style="font-size:14px;padding:6px 16px;">
                        {{ $contract->status->label() }}
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-5 gap-x-8">
                    <div>
                        <p class="text-slate-600 text-xs font-bold mb-1">المالك</p>
                        <p class="text-slate-800 font-semibold">{{ $contract->lessor->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-slate-600 text-xs font-bold mb-1">المستأجر</p>
                        <p class="text-slate-800 font-semibold">{{ $contract->customer->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-slate-600 text-xs font-bold mb-1">هوية المالك</p>
                        <p class="text-slate-700 font-mono">{{ $contract->lessor->national_id ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-slate-600 text-xs font-bold mb-1">هوية المستأجر</p>
                        <p class="text-slate-700 font-mono">{{ $contract->customer->national_id ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-slate-600 text-xs font-bold mb-1">تاريخ البداية</p>
                        <p class="text-slate-700">{{ $contract->start_date?->format('Y/m/d') ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-slate-600 text-xs font-bold mb-1">تاريخ النهاية</p>
                        <p class="text-slate-700">{{ $contract->end_date?->format('Y/m/d') ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-slate-600 text-xs font-bold mb-1">قيمة الإيجار</p>
                        <p class="text-emerald-400 font-bold text-lg">{{ number_format($contract->rent_amount) }} ر.س</p>
                    </div>
                    @if($contract->status === \App\Enums\ContractStatus::SIGNED)
                    <div>
                        <p class="text-slate-600 text-xs font-bold mb-1">ربح الموقع</p>
                        <p class="text-amber-400 font-bold text-lg">{{ number_format($contract->site_profit) }} ر.س</p>
                    </div>
                    @endif
                    @if($contract->tourism_license_number)
                    <div>
                        <p class="text-slate-600 text-xs font-bold mb-1">رقم الرخصة السياحية</p>
                        <p class="text-slate-700">{{ $contract->tourism_license_number }}</p>
                    </div>
                    @endif
                </div>

                @if($contract->property_details)
                <div class="mt-5 pt-5 border-t border-slate-200">
                    <p class="text-slate-600 text-xs font-bold mb-2">وصف العقار</p>
                    <p class="text-slate-700 text-sm leading-relaxed">{{ $contract->property_details }}</p>
                </div>
                @endif

                @if($contract->additional_terms)
                <div class="mt-4 pt-4 border-t border-slate-200">
                    <p class="text-slate-600 text-xs font-bold mb-2">شروط إضافية</p>
                    <p class="text-slate-700 text-sm leading-relaxed">{{ $contract->additional_terms }}</p>
                </div>
                @endif
            </div>

            <!-- ═══ Workflow Progress ═══ -->
            <div class="card">
                <h3 class="text-slate-800 font-bold mb-5 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    مراحل العقد
                </h3>

                @php
                    $status = $contract->status;
                    $steps = [
                        ['key' => 'draft',   'label' => 'إنشاء المسودة',         'icon' => '📝'],
                        ['key' => 'sent',    'label' => 'إرسال للمستأجر',         'icon' => '📤'],
                        ['key' => 'lessee',  'label' => 'توقيع المستأجر',         'icon' => '✍️'],
                        ['key' => 'lessor',  'label' => 'توقيع المالك',           'icon' => '✍️'],
                        ['key' => 'done',    'label' => 'العقد مكتمل',            'icon' => '✅'],
                    ];

                    $statusOrder = [
                        'draft' => 0, 'sent' => 1, 'viewed' => 1,
                        'signed_by_lessee' => 2, 'signed' => 4,
                        'rejected' => -1, 'cancelled' => -1,
                    ];
                    $currentStep = $statusOrder[$status->value] ?? 0;
                @endphp

                @if(in_array($status, [\App\Enums\ContractStatus::REJECTED, \App\Enums\ContractStatus::CANCELLED]))
                    <div class="flex items-center gap-3 p-4 rounded-xl bg-rose-500/10 border border-rose-500/20">
                        <span class="text-2xl">{{ $status === \App\Enums\ContractStatus::REJECTED ? '❌' : '🚫' }}</span>
                        <div>
                            <p class="text-rose-400 font-bold">{{ $status->label() }}</p>
                            <p class="text-slate-600 text-sm">هذا العقد {{ $status === \App\Enums\ContractStatus::REJECTED ? 'مرفوض من العميل' : 'ملغى من الإدارة' }}</p>
                        </div>
                    </div>
                @else
                    <div class="flex items-center gap-1">
                        @foreach($steps as $i => $step)
                            @php
                                $isCompleted = $currentStep > $i;
                                $isCurrent = $currentStep === $i;
                            @endphp
                            <div class="flex-1 text-center">
                                <div class="mx-auto w-10 h-10 rounded-full flex items-center justify-center text-lg
                                    {{ $isCompleted ? 'bg-emerald-500/20 ring-2 ring-emerald-500/50' : ($isCurrent ? 'bg-indigo-500/20 ring-2 ring-indigo-500/50 animate-pulse' : 'bg-slate-50 ring-1 ring-white/10') }}">
                                    {{ $isCompleted ? '✅' : $step['icon'] }}
                                </div>
                                <p class="text-xs mt-2 font-semibold {{ $isCompleted ? 'text-emerald-400' : ($isCurrent ? 'text-indigo-400' : 'text-slate-600') }}">
                                    {{ $step['label'] }}
                                </p>
                            </div>
                            @if(!$loop->last)
                                <div class="w-6 h-0.5 mt-[-16px] {{ $isCompleted ? 'bg-emerald-500' : 'bg-slate-100' }}"></div>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- ═══ Actions Card ═══ -->
            <div class="card">
                <h3 class="text-slate-800 font-bold mb-4">إجراءات</h3>
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

                    {{-- ═══ Send final contract to both parties ═══ --}}
                    @if($contract->status === \App\Enums\ContractStatus::SIGNED && $contract->signed_pdf_path)
                        @php
                            $signedPdfUrl = asset($contract->signed_pdf_path);

                            // Lessee WhatsApp
                            $lesseePhone = preg_replace('/[^0-9]/', '', $contract->customer->phone ?? '');
                            if (str_starts_with($lesseePhone, '05')) $lesseePhone = '966' . substr($lesseePhone, 1);
                            elseif (str_starts_with($lesseePhone, '01')) $lesseePhone = '20' . substr($lesseePhone, 1);
                            elseif (str_starts_with($lesseePhone, '5')) $lesseePhone = '966' . $lesseePhone;

                            $lesseeMsg = "مرحباً " . ($contract->customer->name ?? '') . "،\n\n✅ تم اعتماد عقد الإيجار رقم " . $contract->contract_number . " بنجاح بعد توقيع الطرفين.\n\n📄 يمكنك تحميل نسختك من العقد الموقّع عبر الرابط التالي:\n" . $signedPdfUrl . "\n\nشكراً لثقتك.";
                            $lesseeWhatsapp = "https://wa.me/" . $lesseePhone . "?text=" . urlencode($lesseeMsg);

                            // Lessor WhatsApp
                            $lessorPhone = preg_replace('/[^0-9]/', '', $contract->lessor->phone ?? '');
                            if (str_starts_with($lessorPhone, '05')) $lessorPhone = '966' . substr($lessorPhone, 1);
                            elseif (str_starts_with($lessorPhone, '01')) $lessorPhone = '20' . substr($lessorPhone, 1);
                            elseif (str_starts_with($lessorPhone, '5')) $lessorPhone = '966' . $lessorPhone;

                            $lessorMsg = "مرحباً " . ($contract->lessor->name ?? '') . "،\n\n✅ تم اعتماد عقد الإيجار رقم " . $contract->contract_number . " بنجاح بعد توقيع الطرفين.\n\n📄 يمكنك تحميل نسختك من العقد الموقّع عبر الرابط التالي:\n" . $signedPdfUrl . "\n\nشكراً لثقتك.";
                            $lessorWhatsapp = "https://wa.me/" . $lessorPhone . "?text=" . urlencode($lessorMsg);
                        @endphp

                        <div class="w-full"></div>{{-- force new row --}}

                        <a href="{{ $lesseeWhatsapp }}" target="_blank" class="btn btn-sm" style="background: linear-gradient(135deg, #25d366 0%, #128c7e 100%); color: white; border: none;">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.625.846 5.059 2.284 7.034L.789 23.492a.5.5 0 00.613.613l4.458-1.495A11.952 11.952 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-2.352 0-4.55-.758-6.336-2.065l-.442-.332-3.262 1.093 1.093-3.262-.332-.442A9.96 9.96 0 012 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/></svg>
                            إرسال العقد للمستأجر ({{ $contract->customer->name ?? '' }})
                        </a>

                        <a href="{{ $lessorWhatsapp }}" target="_blank" class="btn btn-sm" style="background: linear-gradient(135deg, #25d366 0%, #128c7e 100%); color: white; border: none;">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.625.846 5.059 2.284 7.034L.789 23.492a.5.5 0 00.613.613l4.458-1.495A11.952 11.952 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-2.352 0-4.55-.758-6.336-2.065l-.442-.332-3.262 1.093 1.093-3.262-.332-.442A9.96 9.96 0 012 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/></svg>
                            إرسال العقد للمالك ({{ $contract->lessor->name ?? '' }})
                        </a>

                        <button type="button" onclick="navigator.clipboard.writeText('{{ $signedPdfUrl }}').then(() => alert('تم نسخ رابط العقد الموقّع!'))" class="btn btn-ghost btn-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                            نسخ رابط العقد الموقّع
                        </button>
                    @endif

                    @if(!$contract->isTerminal())
                        @php
                            $isLessorTurn = $contract->status === \App\Enums\ContractStatus::SIGNED_BY_LESSEE;
                            $targetPerson = $isLessorTurn ? $contract->lessor : $contract->customer;
                            $targetRole = $isLessorTurn ? 'المالك' : 'المستأجر';
                        @endphp

                        {{-- Copy signing link --}}
                        <form method="POST" action="{{ route('contracts.send', $contract) }}" class="inline">
                            @csrf
                            <button type="submit"
                                onclick="navigator.clipboard.writeText('{{ route('sign.show', $contract->verification_token) }}').then(() => {})"
                                class="btn btn-sm" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; border: none;">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                نسخ رابط توقيع {{ $targetRole }}
                            </button>
                        </form>

                        {{-- WhatsApp --}}
                        @php
                            $phone = preg_replace('/[^0-9]/', '', $targetPerson->phone ?? '');
                            if (str_starts_with($phone, '05')) $phone = '966' . substr($phone, 1);
                            elseif (str_starts_with($phone, '01')) $phone = '20' . substr($phone, 1);
                            elseif (str_starts_with($phone, '5')) $phone = '966' . $phone;
                            
                            $whatsappMessage = "مرحباً " . ($targetPerson->name ?? '') . "،\n\nنرجو مراجعة عقد الإيجار وتوقيعه عبر الرابط التالي:\n" . route('sign.show', $contract->verification_token);
                            $whatsappLink = "https://wa.me/" . $phone . "?text=" . urlencode($whatsappMessage);
                        @endphp
                        <a href="{{ $whatsappLink }}" target="_blank"
                           onclick="fetch('{{ route('contracts.send', $contract) }}', {method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}})"
                           class="btn btn-indigo btn-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            مراسلة {{ $targetRole }} واتساب
                        </a>

                        {{-- Edit (only before any signing) --}}
                        @if(!in_array($contract->status, [\App\Enums\ContractStatus::SIGNED_BY_LESSEE]))
                            <a href="{{ route('contracts.edit', $contract) }}" class="btn btn-ghost btn-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                تعديل العقد
                            </a>
                        @endif

                        {{-- Cancel --}}
                        <form method="POST" action="{{ route('contracts.cancel', $contract) }}" onsubmit="return confirm('هل أنت متأكد من إلغاء هذا العقد؟')">
                            @csrf
                            <button type="submit" class="btn btn-rose btn-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                إلغاء العقد
                            </button>
                        </form>
                    @endif
                </div>

                {{-- Context message about what to do next --}}
                @if(!$contract->isTerminal())
                <div class="mt-4 p-3 rounded-xl {{ $isLessorTurn ? 'bg-amber-500/10 border border-amber-500/20' : 'bg-indigo-500/10 border border-indigo-500/20' }}">
                    <p class="text-sm font-semibold {{ $isLessorTurn ? 'text-amber-400' : 'text-indigo-400' }}">
                        @if($contract->status === \App\Enums\ContractStatus::DRAFT)
                            📤 الخطوة التالية: أرسل رابط التوقيع للمستأجر ({{ $contract->customer->name ?? '' }}) عبر الواتساب أو انسخ الرابط.
                        @elseif($contract->status === \App\Enums\ContractStatus::SENT)
                            ⏳ تم إرسال الرابط للمستأجر — بانتظار فتحه والتوقيع عليه.
                        @elseif($contract->status === \App\Enums\ContractStatus::VIEWED)
                            👁️ المستأجر فتح الرابط — بانتظار التوقيع.
                        @elseif($contract->status === \App\Enums\ContractStatus::SIGNED_BY_LESSEE)
                            ✍️ المستأجر وقّع! الخطوة التالية: أرسل رابط التوقيع للمالك ({{ $contract->lessor->name ?? '' }}).
                        @endif
                    </p>
                </div>
                @elseif($contract->status === \App\Enums\ContractStatus::SIGNED)
                <div class="mt-4 p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20">
                    <p class="text-sm font-semibold text-emerald-400">
                        🎉 العقد مكتمل! أرسل نسخة العقد الموقّع للمستأجر والمالك عبر أزرار الواتساب بالأعلى.
                    </p>
                </div>
                @endif
            </div>

            <!-- ═══ Signatures Display ═══ -->
            @if($contract->signatures->count() > 0)
            <div class="card">
                <h3 class="text-slate-800 font-bold mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    التوقيعات
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($contract->signatures as $sig)
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                        <p class="text-slate-600 text-xs font-bold mb-2">
                            {{ $sig->role === 'lessee' ? 'توقيع المستأجر' : 'توقيع المالك' }}
                        </p>
                        <div class="bg-white rounded-lg p-2 mb-2">
                            <img src="{{ asset($sig->signature_path) }}" alt="التوقيع" class="max-h-24 mx-auto">
                        </div>
                        <p class="text-slate-600 text-xs">{{ $sig->signed_at?->format('Y/m/d H:i') }} · IP: {{ $sig->ip_address }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar: Audit Trail -->
        <div class="lg:col-span-1">
            <div class="card">
                <h3 class="text-slate-800 font-bold mb-5 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    سجل الأحداث
                </h3>

                <div class="space-y-0">
                    @forelse($contract->logs as $log)
                        <div class="relative pr-6 pb-6 {{ !$loop->last ? 'border-r-2 border-slate-200' : '' }}">
                            <div class="absolute right-0 top-0 w-3 h-3 rounded-full bg-indigo-500 -translate-x-[5px] ring-4 ring-slate-800"></div>
                            <p class="text-slate-700 text-sm font-semibold">{{ $log->event }}</p>
                            <p class="text-slate-600 text-xs mt-1">{{ $log->created_at->diffForHumans() }}</p>
                        </div>
                    @empty
                        <p class="text-slate-600 text-sm text-center py-4">لا توجد أحداث مسجلة بعد</p>
                    @endforelse
                </div>
            </div>

            <!-- QR Code Card -->
            <div class="card mt-6 text-center">
                <h3 class="text-slate-800 font-bold mb-4 flex items-center justify-center gap-2">
                    <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    رمز التحقق (QR)
                </h3>
                <div class="bg-white p-3 rounded-xl inline-block mx-auto mb-2 shadow-lg">
                    {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(140)->margin(1)->generate(route('verify.index') . '?contract_number=' . $contract->contract_number) !!}
                </div>
                <p class="text-slate-600 text-xs mt-2 leading-relaxed">امسح الرمز بكاميرا الجوال<br>للتحقق من حالة وصلاحية العقد</p>
            </div>
        </div>
    </div>
</x-app-layout>
