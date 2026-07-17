<x-app-layout>
    <x-slot name="header">تعديل العقد #{{ $contract->contract_number }}</x-slot>

    <div class="card max-w-4xl mx-auto">
        <form method="POST" action="{{ route('contracts.update', $contract) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Customer Selection -->
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">العميل</label>
                    <select name="customer_id" class="form-input w-full" required>
                        <option value="">اختر العميل...</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ old('customer_id', $contract->customer_id) == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }} ({{ $customer->national_id }})
                            </option>
                        @endforeach
                    </select>
                    @error('customer_id') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Template Selection -->
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">نموذج العقد</label>
                    <select name="template_id" class="form-input w-full" required>
                        <option value="">اختر النموذج...</option>
                        @foreach($templates as $template)
                            <option value="{{ $template->id }}" {{ old('template_id', $contract->template_id) == $template->id ? 'selected' : '' }}>
                                {{ $template->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('template_id') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Start Date -->
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">تاريخ البداية</label>
                    <input type="date" name="start_date" value="{{ old('start_date', $contract->start_date->format('Y-m-d')) }}" class="form-input w-full" required>
                    @error('start_date') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- End Date -->
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">تاريخ النهاية</label>
                    <input type="date" name="end_date" value="{{ old('end_date', $contract->end_date->format('Y-m-d')) }}" class="form-input w-full" required>
                    @error('end_date') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Rent Amount -->
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">قيمة الإيجار (ريال سعودي)</label>
                    <input type="number" name="rent_amount" value="{{ old('rent_amount', $contract->rent_amount) }}" min="1" step="0.01" class="form-input w-full" required>
                    @error('rent_amount') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Payment Method -->
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">طريقة الدفع</label>
                    <select name="payment_method" class="form-input w-full" required>
                        <option value="monthly" {{ old('payment_method', $contract->payment_method->value ?? $contract->payment_method) == 'monthly' ? 'selected' : '' }}>شهري</option>
                        <option value="quarterly" {{ old('payment_method', $contract->payment_method->value ?? $contract->payment_method) == 'quarterly' ? 'selected' : '' }}>ربع سنوي</option>
                        <option value="semi_annual" {{ old('payment_method', $contract->payment_method->value ?? $contract->payment_method) == 'semi_annual' ? 'selected' : '' }}>نصف سنوي</option>
                        <option value="annual" {{ old('payment_method', $contract->payment_method->value ?? $contract->payment_method) == 'annual' ? 'selected' : '' }}>سنوي</option>
                    </select>
                    @error('payment_method') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Property Details -->
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-2">وصف العقار المؤجر</label>
                <textarea name="property_details" rows="3" class="form-input w-full" required>{{ old('property_details', $contract->property_details) }}</textarea>
                @error('property_details') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Additional Terms -->
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-2">شروط إضافية (اختياري)</label>
                <textarea name="additional_terms" rows="4" class="form-input w-full">{{ old('additional_terms', $contract->additional_terms) }}</textarea>
                @error('additional_terms') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-white/5">
                <a href="{{ route('contracts.show', $contract) }}" class="btn btn-ghost">إلغاء</a>
                <button type="submit" class="btn btn-indigo">
                    حفظ التعديلات
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
