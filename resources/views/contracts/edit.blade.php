<x-app-layout>
    <x-slot name="header">تعديل العقد #{{ $contract->contract_number }}</x-slot>

    <div class="card max-w-4xl mx-auto">
        <form method="POST" action="{{ route('contracts.update', $contract) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Lessor Selection -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">المالك</label>
                    <select name="lessor_id" class="form-input w-full" required>
                        <option value="">اختر المالك...</option>
                        @foreach($lessors as $lessor)
                            <option value="{{ $lessor->id }}" {{ old('lessor_id', $contract->lessor_id) == $lessor->id ? 'selected' : '' }}>
                                {{ $lessor->name }} ({{ $lessor->national_id }})
                            </option>
                        @endforeach
                    </select>
                    @error('lessor_id') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Lessee Selection -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">المستأجر</label>
                    <select name="customer_id" class="form-input w-full" required>
                        <option value="">اختر المستأجر...</option>
                        @foreach($lessees as $lessee)
                            <option value="{{ $lessee->id }}" {{ old('customer_id', $contract->customer_id) == $lessee->id ? 'selected' : '' }}>
                                {{ $lessee->name }} ({{ $lessee->national_id }})
                            </option>
                        @endforeach
                    </select>
                    @error('customer_id') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Template Selection Removed -->

                <!-- Start Date -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">تاريخ البداية</label>
                    <input type="date" name="start_date" value="{{ old('start_date', $contract->start_date->format('Y-m-d')) }}" class="form-input w-full" required>
                    @error('start_date') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- End Date -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">تاريخ النهاية</label>
                    <input type="date" name="end_date" value="{{ old('end_date', $contract->end_date->format('Y-m-d')) }}" class="form-input w-full" required>
                    @error('end_date') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Rent Amount -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">قيمة الإيجار (ريال سعودي)</label>
                    <input type="number" name="rent_amount" value="{{ old('rent_amount', $contract->rent_amount) }}" min="1" step="0.01" class="form-input w-full" required>
                    @error('rent_amount') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Tourism License Number -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">رقم الرخصة السياحية (اختياري)</label>
                    <input type="text" name="tourism_license_number" value="{{ old('tourism_license_number', $contract->tourism_license_number) }}" class="form-input w-full">
                    @error('tourism_license_number') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Property Details -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">وصف العقار</label>
                <textarea name="property_details" rows="3" class="form-input w-full" required>{{ old('property_details', $contract->property_details) }}</textarea>
                @error('property_details') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Additional Terms -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">شروط إضافية (اختياري)</label>
                <textarea name="additional_terms" rows="4" class="form-input w-full">{{ old('additional_terms', $contract->additional_terms) }}</textarea>
                @error('additional_terms') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">
                <a href="{{ route('contracts.show', $contract) }}" class="btn btn-ghost">إلغاء</a>
                <button type="submit" class="btn btn-indigo">
                    حفظ التعديلات
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
