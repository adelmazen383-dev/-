@extends('layouts.provider', ['title' => 'العقد ' . $contract->contract_number, 'header' => 'تفاصيل العقد #' . $contract->contract_number])

@section('content')
    @php
        $status = $contract->status;
        $isLessorTurn = $status === \App\Enums\ContractStatus::SIGNED_BY_LESSEE;
        $targetName  = $isLessorTurn ? $contract->lessor_name : $contract->lessee_name;
        $targetPhone = $isLessorTurn ? $contract->lessor_phone : $contract->lessee_phone;
        $targetRole  = $isLessorTurn ? 'المالك' : 'المستأجر';
        $signUrl     = route('provider-sign.show', $contract->verification_token);

        $formatWa = function (?string $raw): string {
            $digits = preg_replace('/[^0-9]/', '', (string) $raw);
            if (str_starts_with($digits, '05')) return '966' . substr($digits, 1);
            if (str_starts_with($digits, '01')) return '20' . substr($digits, 1);
            if (str_starts_with($digits, '5') && strlen($digits) === 9) return '966' . $digits;
            return $digits;
        };
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <!-- Main Column -->
        <div class="lg:col-span-2 space-y-5">
            <!-- Active / Expired Banner -->
            @if($contract->isExpired())
                <div class="rounded-xl bg-rose-50 border border-rose-300 px-4 py-3 text-center text-rose-700 font-black text-sm">
                    العقد منتهي الصلاحية
                </div>
            @else
                <div class="rounded-xl bg-emerald-50 border border-emerald-300 px-4 py-3 text-center text-emerald-800 font-black text-sm">
                    العقد فعال
                </div>
            @endif

            <!-- Contract Summary Card -->
            <div class="p-card">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 mb-4 border-b border-slate-100">
                    <div>
                        <span class="text-xs text-slate-500 font-bold">رقم العقد</span>
                        <div class="text-xl font-black text-emerald-800 font-mono">{{ $contract->contract_number }}</div>
                    </div>
                    <span class="px-3.5 py-1 rounded-full text-xs font-extrabold self-start sm:self-center
                        {{ $status === \App\Enums\ContractStatus::SIGNED ? 'bg-emerald-100 text-emerald-800' : (in_array($status, [\App\Enums\ContractStatus::REJECTED, \App\Enums\ContractStatus::CANCELLED]) ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-900') }}">
                        {{ $status->label() }}
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs sm:text-sm">
                    <div class="p-3 rounded-xl bg-slate-50">
                        <span class="text-[11px] font-bold text-slate-400 block">الطرف الأول (المالك)</span>
                        <strong class="text-slate-900">{{ $contract->lessor_name }}</strong>
                        <div class="text-[11px] text-slate-500 font-mono mt-0.5" dir="ltr">هوية: {{ $contract->lessor_national_id }} · {{ $contract->lessor_phone }}</div>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50">
                        <span class="text-[11px] font-bold text-slate-400 block">الطرف الثاني (المستأجر)</span>
                        <strong class="text-slate-900">{{ $contract->lessee_name }}</strong>
                        <div class="text-[11px] text-slate-500 font-mono mt-0.5" dir="ltr">هوية: {{ $contract->lessee_national_id }} · {{ $contract->lessee_phone }}</div>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50">
                        <span class="text-[11px] font-bold text-slate-400 block">تاريخ البداية</span>
                        <strong class="text-slate-800">{{ $contract->start_date?->format('Y/m/d H:i') ?? '—' }}</strong>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50">
                        <span class="text-[11px] font-bold text-slate-400 block">تاريخ النهاية</span>
                        <strong class="text-slate-800">{{ $contract->end_date?->format('Y/m/d H:i') ?? '—' }}</strong>
                    </div>
                    <div class="p-3 rounded-xl bg-emerald-50/70 border border-emerald-100">
                        <span class="text-[11px] font-bold text-emerald-700 block">قيمة الإيجار</span>
                        <strong class="text-emerald-800 text-base font-black tabular-nums">{{ number_format((float) $contract->rent_amount, 2) }} ر.س</strong>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50">
                        <span class="text-[11px] font-bold text-slate-400 block">مبلغ التأمين</span>
                        <strong class="text-slate-900 text-base font-black tabular-nums">{{ number_format((float) $contract->deposit_amount, 2) }} ر.س</strong>
                    </div>
                    @if($contract->tourism_license_number)
                        <div class="sm:col-span-2 p-3 rounded-xl bg-slate-50">
                            <span class="text-[11px] font-bold text-slate-400 block">رقم الرخصة السياحية</span>
                            <strong class="text-slate-900 font-mono">{{ $contract->tourism_license_number }}</strong>
                        </div>
                    @endif
                </div>

                <div class="mt-4 pt-4 border-t border-slate-100">
                    <span class="text-xs font-bold text-slate-500 block mb-1">وصف العقار</span>
                    <p class="text-xs sm:text-sm text-slate-800 leading-relaxed">{{ $contract->property_details }}</p>
                </div>

                @if($contract->additional_terms)
                    <div class="mt-4 pt-4 border-t border-slate-100">
                        <span class="text-xs font-bold text-slate-500 block mb-1">شروط إضافية</span>
                        <p class="text-xs sm:text-sm text-slate-800 leading-relaxed whitespace-pre-line">{{ $contract->additional_terms }}</p>
                    </div>
                @endif
            </div>

            <!-- 5-Step Workflow Progress (Clean Vector Steps, Zero Emojis) -->
            <div class="p-card">
                <h3 class="text-sm font-black text-slate-900 mb-4">مراحل توثيق العقد</h3>

                @php
                    $steps = [
                        ['num' => 1, 'label' => 'إنشاء المسودة'],
                        ['num' => 2, 'label' => 'إرسال للمستأجر'],
                        ['num' => 3, 'label' => 'توقيع المستأجر'],
                        ['num' => 4, 'label' => 'توقيع المالك'],
                        ['num' => 5, 'label' => 'العقد مكتمل'],
                    ];
                    $statusOrder = [
                        'draft' => 0, 'sent' => 1, 'viewed' => 1,
                        'signed_by_lessee' => 2, 'signed' => 5,
                        'rejected' => -1, 'cancelled' => -1,
                    ];
                    $currentStep = $statusOrder[$status->value] ?? 0;
                @endphp

                @if(in_array($status, [\App\Enums\ContractStatus::REJECTED, \App\Enums\ContractStatus::CANCELLED]))
                    <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold">
                        {{ $status->label() }} — هذا العقد {{ $status === \App\Enums\ContractStatus::REJECTED ? 'مرفوض من الطرف المعني' : 'ملغى' }}.
                    </div>
                @else
                    <div class="grid grid-cols-5 gap-2 text-center">
                        @foreach($steps as $i => $step)
                            @php
                                $done = $currentStep > $i;
                                $active = $currentStep === $i;
                            @endphp
                            <div>
                                <div class="mx-auto w-8 h-8 rounded-full flex items-center justify-center text-xs font-black
                                    {{ $done ? 'bg-emerald-600 text-white' : ($active ? 'bg-emerald-100 text-emerald-900 ring-2 ring-emerald-500' : 'bg-slate-100 text-slate-400') }}">
                                    @if($done)
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    @else
                                        {{ $step['num'] }}
                                    @endif
                                </div>
                                <p class="text-[10.5px] mt-1.5 font-bold {{ $done ? 'text-emerald-700' : ($active ? 'text-slate-900' : 'text-slate-400') }}">
                                    {{ $step['label'] }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Actions Card -->
            <div class="p-card">
                <h3 class="text-sm font-black text-slate-900 mb-3">إجراءات العقد والتوقيع</h3>

                <div class="flex flex-wrap gap-2">
                    @if($contract->pdf_path)
                        <a href="{{ asset($contract->pdf_path) }}" target="_blank"
                           class="px-3.5 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold transition">
                            تحميل المسودة PDF
                        </a>
                    @endif

                    @if($contract->signed_pdf_path)
                        <a href="{{ asset($contract->signed_pdf_path) }}" target="_blank"
                           class="px-3.5 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-extrabold transition">
                            تحميل العقد الموقّع PDF
                        </a>
                    @endif

                    @if($provider->hasActiveSubscription())
                        <form method="POST" action="{{ route('provider.contracts.regenerate-pdf', $contract) }}" class="inline">
                            @csrf
                            <button type="submit" class="px-3.5 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold transition">
                                تحديث ملف PDF
                            </button>
                        </form>
                    @endif

                    @if($status === \App\Enums\ContractStatus::SIGNED && $contract->signed_pdf_path)
                        @php
                            $signedPdfUrl = asset($contract->signed_pdf_path);
                            $lesseeMsg = "مرحباً {$contract->lessee_name}،\n\nتم اعتماد عقد الإيجار رقم {$contract->contract_number} بنجاح بعد توقيع الطرفين.\n\nيمكنك تحميل نسختك من العقد الموقّع عبر الرابط التالي:\n{$signedPdfUrl}\n\nشكراً لثقتك — منصة ثقة.";
                            $lessorMsg = "مرحباً {$contract->lessor_name}،\n\nتم اعتماد عقد الإيجار رقم {$contract->contract_number} بنجاح بعد توقيع الطرفين.\n\nيمكنك تحميل نسختك من العقد الموقّع عبر الرابط التالي:\n{$signedPdfUrl}\n\nشكراً لثقتك — منصة ثقة.";
                        @endphp

                        <a href="https://wa.me/{{ $formatWa($contract->lessee_phone) }}?text={{ urlencode($lesseeMsg) }}" target="_blank"
                           class="px-3.5 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-extrabold transition">
                            إرسال العقد للمستأجر واتساب
                        </a>
                        <a href="https://wa.me/{{ $formatWa($contract->lessor_phone) }}?text={{ urlencode($lessorMsg) }}" target="_blank"
                           class="px-3.5 py-2 rounded-lg bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-extrabold transition">
                            إرسال العقد للمالك واتساب
                        </a>
                        <button type="button"
                                onclick="navigator.clipboard.writeText('{{ $signedPdfUrl }}').then(() => alert('تم نسخ رابط العقد الموقّع'))"
                                class="px-3.5 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold transition">
                            نسخ رابط العقد الموقّع
                        </button>
                    @endif

                    @if(!$contract->isTerminal() && $provider->hasActiveSubscription())
                        <form method="POST" action="{{ route('provider.contracts.send', $contract) }}" class="inline">
                            @csrf
                            <button type="submit"
                                    onclick="navigator.clipboard.writeText('{{ $signUrl }}').then(() => {})"
                                    class="px-3.5 py-2 rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-xs font-extrabold transition">
                                نسخ رابط توقيع {{ $targetRole }}
                            </button>
                        </form>

                        @php
                            $waSignMsg = "مرحباً أستاذ/ة {$targetName}،\n\nنرجو التكرم بمراجعة عقد الإيجار رقم {$contract->contract_number} وقراءته بعناية، ثم توقيعه إلكترونياً عبر الرابط التالي:\n{$signUrl}\n\nونود إحاطتكم علماً بأن العقد يُعد سارياً وملزماً للطرفين اعتباراً من وقت توقيعه.\n\nشاكرين لكم تعاونكم — منصة ثقة.";
                        @endphp
                        <a href="https://wa.me/{{ $formatWa($targetPhone) }}?text={{ urlencode($waSignMsg) }}" target="_blank"
                           onclick="fetch('{{ route('provider.contracts.send', $contract) }}', {method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}})"
                           class="px-3.5 py-2 rounded-lg bg-[#064e3b] hover:bg-emerald-800 text-white text-xs font-extrabold transition">
                            مراسلة {{ $targetRole }} واتساب
                        </a>

                        @if($isLessorTurn)
                            <a href="{{ $signUrl }}" target="_blank"
                               class="px-3.5 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-extrabold transition">
                                فتح صفحة التوقيع للمالك الآن
                            </a>
                        @endif

                        @if($status !== \App\Enums\ContractStatus::SIGNED_BY_LESSEE)
                            <a href="{{ route('provider.contracts.edit', $contract) }}"
                               class="px-3.5 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold transition">
                                تعديل العقد
                            </a>
                        @endif

                        <form method="POST" action="{{ route('provider.contracts.cancel', $contract) }}"
                              onsubmit="return confirm('هل أنت متأكد من إلغاء هذا العقد؟')">
                            @csrf
                            <button type="submit" class="px-3.5 py-2 rounded-lg bg-rose-50 hover:bg-rose-600 text-rose-700 hover:text-white text-xs font-bold transition">
                                إلغاء العقد
                            </button>
                        </form>
                    @endif

                    @if($provider->hasActiveSubscription())
                        <form method="POST" action="{{ route('provider.contracts.destroy', $contract) }}"
                              onsubmit="return confirm('هل أنت متأكد من حذف هذا العقد نهائياً؟')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-3.5 py-2 rounded-lg bg-rose-50 hover:bg-rose-600 text-rose-700 hover:text-white text-xs font-bold transition">
                                حذف العقد
                            </button>
                        </form>
                    @endif
                </div>

                <!-- Next Step Context Guidance -->
                @if(!$contract->isTerminal())
                    <div class="mt-4 p-3 rounded-xl {{ $isLessorTurn ? 'bg-amber-50 border border-amber-200 text-amber-900' : 'bg-emerald-50 border border-emerald-200 text-emerald-900' }} text-xs font-bold">
                        @if($status === \App\Enums\ContractStatus::DRAFT)
                            الخطوة التالية: أرسل رابط التوقيع للمستأجر ({{ $contract->lessee_name }}) عبر الواتساب أو انسخ الرابط.
                        @elseif($status === \App\Enums\ContractStatus::SENT)
                            تم إرسال الرابط للمستأجر — بانتظار فتحه والتوقيع عليه.
                        @elseif($status === \App\Enums\ContractStatus::VIEWED)
                            قام المستأجر بفتح الرابط — بانتظار إتمام التوقيع.
                        @elseif($status === \App\Enums\ContractStatus::SIGNED_BY_LESSEE)
                            وقّع المستأجر بنجاح. الخطوة التالية: توقيع المالك ({{ $contract->lessor_name }}) لإصدار النسخة النهائية الموقّعة.
                        @endif
                    </div>
                @elseif($status === \App\Enums\ContractStatus::SIGNED)
                    <div class="mt-4 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-bold">
                        العقد مكتمل وموثّق من الطرفين. يمكنك تحميل النسخة النهائية أو إرسالها للطرفين عبر الواتساب.
                    </div>
                @endif
            </div>

            <!-- Signatures Display -->
            @if($contract->signatures->isNotEmpty())
                <div class="p-card">
                    <h3 class="text-sm font-black text-slate-900 mb-3">التوقيعات الإلكترونية</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        @foreach($contract->signatures as $sig)
                            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                                <p class="text-xs font-extrabold text-slate-700 mb-2">
                                    {{ $sig->role === 'lessee' ? 'توقيع المستأجر' : 'توقيع المالك' }}
                                </p>
                                <div class="bg-white rounded-lg p-2 border border-slate-100 mb-2">
                                    <img src="{{ asset($sig->signature_path) }}" alt="التوقيع" class="max-h-20 mx-auto">
                                </div>
                                <p class="text-[11px] text-slate-500 font-mono" dir="ltr">
                                    {{ $sig->signed_at?->format('Y/m/d H:i') }} · IP: {{ $sig->ip_address }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar: Audit Trail + Verification QR -->
        <div class="space-y-5">
            <div class="p-card">
                <h3 class="text-sm font-black text-slate-900 mb-4">سجل أحداث العقد</h3>
                <div class="space-y-3">
                    @forelse($contract->logs as $log)
                        <div class="pr-3 border-r-2 border-emerald-500">
                            <p class="text-xs font-bold text-slate-800">{{ $log->event }}</p>
                            <p class="text-[10.5px] text-slate-400 mt-0.5">{{ $log->created_at->diffForHumans() }}</p>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 text-center py-4">لا توجد أحداث مسجلة بعد</p>
                    @endforelse
                </div>
            </div>

            <div class="p-card text-center">
                <h3 class="text-sm font-black text-slate-900 mb-3">رمز التحقق من العقد (QR)</h3>
                <div class="bg-white p-3 rounded-xl inline-block border border-slate-200">
                    {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(140)->margin(1)->generate(route('verify.index') . '?contract_number=' . $contract->contract_number) !!}
                </div>
                <p class="text-[11.5px] text-slate-500 mt-2.5 leading-relaxed">
                    امسح الرمز بكاميرا الجوال للتحقق الفوري من حالة وصلاحية العقد عبر منصة ثقة
                </p>
            </div>
        </div>
    </div>
@endsection
