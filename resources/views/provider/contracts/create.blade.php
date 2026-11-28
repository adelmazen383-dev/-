@extends('layouts.provider', ['title' => 'إنشاء عقد إلكتروني جديد', 'header' => 'إنشاء عقد إيجار إلكتروني'])

@section('content')
    <div class="max-w-3xl space-y-4">
        @if($invoice)
            <div class="rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-xs text-emerald-950">
                <div class="flex items-center justify-between gap-2 flex-wrap">
                    <strong class="text-sm font-black">تم استيراد بيانات المستأجر والحجز تلقائياً من الفاتورة ({{ $invoice->invoice_number }})</strong>
                    <span class="px-2 py-0.5 rounded-full bg-emerald-200/70 text-emerald-900 font-bold text-[11px]">استيراد تلقائي</span>
                </div>
                <p class="mt-1 text-emerald-800 leading-relaxed">
                    يرجى مراجعة رقم الهوية والتواريخ وتفاصيل الوحدة السكنية ثم الضغط على «إنشاء العقد وتوليد المسودة».
                </p>
            </div>
        @endif

        <div class="p-card">
            <form method="POST" action="{{ route('provider.contracts.store') }}" class="space-y-6">
                @csrf
                @if(!empty($prefill['provider_invoice_id']))
                    <input type="hidden" name="provider_invoice_id" value="{{ $prefill['provider_invoice_id'] }}">
                @endif

                <!-- 1. First Party: Lessor -->
                <div>
                    <h3 class="text-sm font-black text-slate-900 mb-3 pb-2 border-b border-slate-100">
                        1. بيانات الطرف الأول (المالك / المؤجر)
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                        <div>
                            <label for="lessor_name" class="block text-xs font-bold text-slate-700 mb-1">اسم المالك / المؤجر *</label>
                            <input type="text" id="lessor_name" name="lessor_name" value="{{ old('lessor_name', $prefill['lessor_name'] ?? '') }}" required class="p-input">
                            @error('lessor_name') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="lessor_national_id" class="block text-xs font-bold text-slate-700 mb-1">رقم الهوية الوطنية للمالك *</label>
                            <input type="text" id="lessor_national_id" name="lessor_national_id" value="{{ old('lessor_national_id', $prefill['lessor_national_id'] ?? '') }}" required class="p-input font-mono" dir="ltr" placeholder="10xxxxxxxx">
                            @error('lessor_national_id') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="lessor_phone" class="block text-xs font-bold text-slate-700 mb-1">رقم جوال المالك (للواتساب) *</label>
                            <input type="text" id="lessor_phone" name="lessor_phone" value="{{ old('lessor_phone', $prefill['lessor_phone'] ?? '') }}" required class="p-input font-mono" dir="ltr" placeholder="05xxxxxxxx">
                            @error('lessor_phone') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- 2. Second Party: Lessee -->
                <div>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3 pb-2 border-b border-slate-100">
                        <h3 class="text-sm font-black text-slate-900">2. بيانات الطرف الثاني (المستأجر)</h3>
                        @if($recentTenants->isNotEmpty())
                            <select id="recent_tenant_picker" class="p-input sm:w-64 py-1.5 text-xs"
                                    onchange="const opt=this.options[this.selectedIndex]; if(!opt.value) return; document.getElementById('lessee_name').value=opt.dataset.name; document.getElementById('lessee_national_id').value=opt.dataset.nid; document.getElementById('lessee_phone').value=opt.dataset.phone;">
                                <option value="">اختيار سريع من مستأجريك السابقين...</option>
                                @foreach($recentTenants as $t)
                                    <option value="1" data-name="{{ $t->lessee_name }}" data-nid="{{ $t->lessee_national_id }}" data-phone="{{ $t->lessee_phone }}">
                                        {{ $t->lessee_name }} ({{ $t->lessee_national_id }})
                                    </option>
                                @endforeach
                            </select>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                        <div>
                            <label for="lessee_name" class="block text-xs font-bold text-slate-700 mb-1">اسم المستأجر *</label>
                            <input type="text" id="lessee_name" name="lessee_name" value="{{ old('lessee_name', $prefill['lessee_name'] ?? '') }}" required class="p-input" placeholder="الاسم الكامل للمستأجر">
                            @error('lessee_name') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="lessee_national_id" class="block text-xs font-bold text-slate-700 mb-1">رقم الهوية / الإقامة *</label>
                            <input type="text" id="lessee_national_id" name="lessee_national_id" value="{{ old('lessee_national_id', $prefill['lessee_national_id'] ?? '') }}" required class="p-input font-mono" dir="ltr" placeholder="10xxxxxxxx">
                            @error('lessee_national_id') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="lessee_phone" class="block text-xs font-bold text-slate-700 mb-1">رقم جوال المستأجر *</label>
                            <input type="text" id="lessee_phone" name="lessee_phone" value="{{ old('lessee_phone', $prefill['lessee_phone'] ?? '') }}" required class="p-input font-mono" dir="ltr" placeholder="05xxxxxxxx">
                            @error('lessee_phone') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- 3. Property Details -->
                <div>
                    <h3 class="text-sm font-black text-slate-900 mb-3 pb-2 border-b border-slate-100">
                        3. بيانات العقار / الوحدة السكنية
                    </h3>
                    <div>
                        <label for="property_details" class="block text-xs font-bold text-slate-700 mb-1">وصف العقار ورقم الشقة / الجناح *</label>
                        <textarea id="property_details" name="property_details" required rows="3" class="p-input"
                                  placeholder="مثال: شقة 402 — الدور الرابع — حي النرجس، الرياض">{{ old('property_details', $prefill['property_details'] ?? '') }}</textarea>
                        @error('property_details') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- 4. Duration, Rent & Deposit -->
                <div>
                    <h3 class="text-sm font-black text-slate-900 mb-3 pb-2 border-b border-slate-100">
                        4. المدة والمبلغ والترخيص السياحي
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <label for="start_date" class="block text-xs font-bold text-slate-700 mb-1">تاريخ ووقت بداية العقد *</label>
                            <input type="datetime-local" id="start_date" name="start_date" value="{{ old('start_date', $prefill['start_date'] ?? '') }}" required class="p-input">
                            @error('start_date') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="end_date" class="block text-xs font-bold text-slate-700 mb-1">تاريخ ووقت نهاية العقد *</label>
                            <input type="datetime-local" id="end_date" name="end_date" value="{{ old('end_date', $prefill['end_date'] ?? '') }}" required class="p-input">
                            @error('end_date') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="rent_amount" class="block text-xs font-bold text-slate-700 mb-1">قيمة الإيجار (ر.س) *</label>
                            <input type="number" id="rent_amount" name="rent_amount" value="{{ old('rent_amount', $prefill['rent_amount'] ?? '') }}" required min="1" step="0.01" class="p-input font-bold" dir="ltr" placeholder="1500">
                            @error('rent_amount') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="deposit_amount" class="block text-xs font-bold text-slate-700 mb-1">مبلغ التأمين المسترد (ر.س) *</label>
                            <input type="number" id="deposit_amount" name="deposit_amount" value="{{ old('deposit_amount', $prefill['deposit_amount'] ?? '0') }}" required min="0" step="0.01" class="p-input font-bold" dir="ltr" placeholder="500 أو 0">
                            @error('deposit_amount') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label for="tourism_license_number" class="block text-xs font-bold text-slate-700 mb-1">رقم الرخصة السياحية (اختياري)</label>
                            <input type="text" id="tourism_license_number" name="tourism_license_number" value="{{ old('tourism_license_number', $prefill['tourism_license_number'] ?? '') }}" class="p-input" placeholder="مثال: 50012345">
                            @error('tourism_license_number') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- 5. Additional Terms -->
                <div>
                    <h3 class="text-sm font-black text-slate-900 mb-2">5. شروط إضافية (اختياري)</h3>
                    <textarea id="additional_terms" name="additional_terms" rows="3" class="p-input"
                              placeholder="أي بنود أو شروط إضافية تود إضافتها إلى العقد...">{{ old('additional_terms', $prefill['additional_terms'] ?? '') }}</textarea>
                </div>

                <div class="flex flex-wrap items-center gap-2.5 pt-4 border-t border-slate-100">
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#064e3b] hover:bg-emerald-800 text-white text-xs sm:text-sm font-black shadow transition">
                        إنشاء العقد وتوليد المسودة PDF
                    </button>
                    <a href="{{ $invoice ? route('provider.invoices.show', $invoice) : route('provider.contracts.index') }}"
                       class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs sm:text-sm font-bold transition">
                        إلغاء
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
