@extends('layouts.provider', ['title' => 'تعديل العقد ' . $contract->contract_number, 'header' => 'تعديل العقد #' . $contract->contract_number])

@section('content')
    <div class="max-w-3xl">
        <div class="p-card">
            <form method="POST" action="{{ route('provider.contracts.update', $contract) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <h3 class="text-sm font-black text-slate-900 mb-3 pb-2 border-b border-slate-100">بيانات المالك (الطرف الأول)</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">اسم المالك *</label>
                            <input type="text" name="lessor_name" value="{{ old('lessor_name', $contract->lessor_name) }}" required class="p-input">
                            @error('lessor_name') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">هوية المالك *</label>
                            <input type="text" name="lessor_national_id" value="{{ old('lessor_national_id', $contract->lessor_national_id) }}" required class="p-input font-mono" dir="ltr">
                            @error('lessor_national_id') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">جوال المالك *</label>
                            <input type="text" name="lessor_phone" value="{{ old('lessor_phone', $contract->lessor_phone) }}" required class="p-input font-mono" dir="ltr">
                            @error('lessor_phone') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-black text-slate-900 mb-3 pb-2 border-b border-slate-100">بيانات المستأجر (الطرف الثاني)</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">اسم المستأجر *</label>
                            <input type="text" name="lessee_name" value="{{ old('lessee_name', $contract->lessee_name) }}" required class="p-input">
                            @error('lessee_name') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">هوية المستأجر *</label>
                            <input type="text" name="lessee_national_id" value="{{ old('lessee_national_id', $contract->lessee_national_id) }}" required class="p-input font-mono" dir="ltr">
                            @error('lessee_national_id') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">جوال المستأجر *</label>
                            <input type="text" name="lessee_phone" value="{{ old('lessee_phone', $contract->lessee_phone) }}" required class="p-input font-mono" dir="ltr">
                            @error('lessee_phone') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">تاريخ البداية *</label>
                        <input type="datetime-local" name="start_date" value="{{ old('start_date', $contract->start_date?->format('Y-m-d\TH:i')) }}" required class="p-input">
                        @error('start_date') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">تاريخ النهاية *</label>
                        <input type="datetime-local" name="end_date" value="{{ old('end_date', $contract->end_date?->format('Y-m-d\TH:i')) }}" required class="p-input">
                        @error('end_date') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">قيمة الإيجار (ر.س) *</label>
                        <input type="number" name="rent_amount" value="{{ old('rent_amount', $contract->rent_amount) }}" min="1" step="0.01" required class="p-input font-bold" dir="ltr">
                        @error('rent_amount') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">مبلغ التأمين (ر.س) *</label>
                        <input type="number" name="deposit_amount" value="{{ old('deposit_amount', $contract->deposit_amount) }}" min="0" step="0.01" required class="p-input font-bold" dir="ltr">
                        @error('deposit_amount') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">رقم الرخصة السياحية (اختياري)</label>
                        <input type="text" name="tourism_license_number" value="{{ old('tourism_license_number', $contract->tourism_license_number) }}" class="p-input">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">وصف العقار *</label>
                    <textarea name="property_details" rows="3" required class="p-input">{{ old('property_details', $contract->property_details) }}</textarea>
                    @error('property_details') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">شروط إضافية (اختياري)</label>
                    <textarea name="additional_terms" rows="3" class="p-input">{{ old('additional_terms', $contract->additional_terms) }}</textarea>
                </div>

                <div class="flex items-center gap-2.5 pt-3 border-t border-slate-100">
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#064e3b] hover:bg-emerald-800 text-white text-xs sm:text-sm font-black transition">
                        حفظ التعديلات وإعادة توليد المسودة
                    </button>
                    <a href="{{ route('provider.contracts.show', $contract) }}" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs sm:text-sm font-bold">
                        إلغاء
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
