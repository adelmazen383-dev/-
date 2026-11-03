<x-app-layout>
    <x-slot name="header">إنشاء عقد جديد</x-slot>

    <div class="max-w-3xl">
        @if(isset($invoice))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 flex items-start gap-3 shadow-sm">
            <div class="w-9 h-9 rounded-lg bg-emerald-500/20 text-emerald-600 flex items-center justify-center shrink-0 mt-0.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="flex-1">
                <div class="flex items-center justify-between">
                    <h4 class="font-bold text-emerald-950 text-sm">تم استيراد بيانات العميل والحجز تلقائياً من الفاتورة (#{{ $invoice->invoice_number }})</h4>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800">فاتورة مدفوعة</span>
                </div>
                <p class="text-xs text-emerald-800 mt-1">
                    تم اختيار المستأجر ({{ $invoice->customer_name }})، وتعبئة الإيجار ({{ number_format($prefill['rent_amount'] ?? 0, 2) }} ر.س)، والتأمين ({{ number_format($prefill['deposit_amount'] ?? 0, 2) }} ر.س)، والتواريخ، وتفاصيل الوحدة السكنية. يرجى اختيار المالك ومراجعة الشروط لإتمام توثيق العقد.
                </p>
            </div>
        </div>
        @endif

        <div class="card">
            <form method="POST" action="{{ route('contracts.store') }}">
                @csrf

                <!-- Section: Client & Template -->
                <div class="mb-8">
                    <h3 class="text-slate-800 font-bold text-base mb-4 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-indigo-500/20 flex items-center justify-center text-indigo-400 text-xs font-black">1</span>
                        بيانات العميل والقالب
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="lessor_id" class="form-label">اختر المالك *</label>
                            <select id="lessor_id" name="lessor_id" required class="form-input">
                                <option value="">-- اختر مالكاً --</option>
                                @foreach($lessors as $lessor)
                                    <option value="{{ $lessor->id }}" {{ old('lessor_id', $prefill['lessor_id'] ?? '') == $lessor->id ? 'selected' : '' }}>
                                        {{ $lessor->name }} ({{ $lessor->national_id }})
                                    </option>
                                @endforeach
                            </select>
                            @error('lessor_id') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="customer_id" class="form-label">اختر المستأجر *</label>
                            <select id="customer_id" name="customer_id" required class="form-input">
                                <option value="">-- اختر مستأجراً --</option>
                                @foreach($lessees as $lessee)
                                    <option value="{{ $lessee->id }}" {{ old('customer_id', $prefill['customer_id'] ?? '') == $lessee->id ? 'selected' : '' }}>
                                        {{ $lessee->name }} ({{ $lessee->national_id }})
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Section: Property Details -->
                <div class="mb-8">
                    <h3 class="text-slate-800 font-bold text-base mb-4 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-emerald-500/20 flex items-center justify-center text-emerald-400 text-xs font-black">2</span>
                        بيانات العقار
                    </h3>
                    <div>
                        <label for="property_details" class="form-label">وصف العقار *</label>
                        <textarea id="property_details" name="property_details" required class="form-input" rows="3"
                                  placeholder="مثال: شقة في الرياض حي النرجس، مكونة من 3 غرف وصالة...">{{ old('property_details', $prefill['property_details'] ?? '') }}</textarea>
                        @error('property_details') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Section: Duration & Payment -->
                <div class="mb-8">
                    <h3 class="text-slate-800 font-bold text-base mb-4 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-amber-500/20 flex items-center justify-center text-amber-400 text-xs font-black">3</span>
                        المدة والمبلغ
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="start_date" class="form-label">تاريخ بداية العقد *</label>
                            <input type="datetime-local" id="start_date" name="start_date" value="{{ old('start_date', $prefill['start_date'] ?? '') }}" required class="form-input">
                            @error('start_date') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="end_date" class="form-label">تاريخ نهاية العقد *</label>
                            <input type="datetime-local" id="end_date" name="end_date" value="{{ old('end_date', $prefill['end_date'] ?? '') }}" required class="form-input">
                            @error('end_date') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="rent_amount" class="form-label">قيمة الإيجار (ر.س) *</label>
                            <input type="number" id="rent_amount" name="rent_amount" value="{{ old('rent_amount', $prefill['rent_amount'] ?? '') }}" required min="0" step="0.01" class="form-input" placeholder="مثال: 25000">
                            @error('rent_amount') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="deposit_amount" class="form-label">مبلغ التأمين (ر.س) *</label>
                            <input type="number" id="deposit_amount" name="deposit_amount" value="{{ old('deposit_amount', isset($prefill['deposit_amount']) ? $prefill['deposit_amount'] : '') }}" required min="0" step="0.01" class="form-input" placeholder="مثال: 500 أو 0">
                            <span class="text-[11px] text-slate-500 mt-1 block">مبلغ التأمين المسترد عند تسليم الوحدة</span>
                            @error('deposit_amount') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label for="tourism_license_number" class="form-label">رقم الرخصة السياحية (اختياري)</label>
                            <input type="text" id="tourism_license_number" name="tourism_license_number" value="{{ old('tourism_license_number', $prefill['tourism_license_number'] ?? '') }}" class="form-input" placeholder="مثال: 123456789">
                            @error('tourism_license_number') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Section: Additional Terms -->
                <div class="mb-8">
                    <h3 class="text-slate-800 font-bold text-base mb-4 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-rose-500/20 flex items-center justify-center text-rose-400 text-xs font-black">4</span>
                        شروط إضافية (اختياري)
                    </h3>
                    <textarea id="additional_terms" name="additional_terms" class="form-input" rows="3"
                              placeholder="أي بنود أو شروط إضافية تريد إضافتها للعقد...">{{ old('additional_terms', $prefill['additional_terms'] ?? '') }}</textarea>
                </div>

                <div class="flex items-center gap-3 pt-4 border-t border-slate-200">
                    <button type="submit" class="btn btn-emerald">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        إنشاء العقد وإرساله
                    </button>
                    @if(isset($invoice))
                        <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-ghost">العودة للفاتورة</a>
                    @else
                        <a href="{{ route('contracts.index') }}" class="btn btn-ghost">إلغاء</a>
                    @endif
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
