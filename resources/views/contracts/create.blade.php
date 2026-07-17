<x-app-layout>
    <x-slot name="header">إنشاء عقد جديد</x-slot>

    <div class="max-w-3xl">
        <div class="card">
            <form method="POST" action="{{ route('contracts.store') }}">
                @csrf

                <!-- Section: Client & Template -->
                <div class="mb-8">
                    <h3 class="text-white font-bold text-base mb-4 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-indigo-500/20 flex items-center justify-center text-indigo-400 text-xs font-black">1</span>
                        بيانات العميل والقالب
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="customer_id" class="form-label">اختر العميل</label>
                            <select id="customer_id" name="customer_id" required class="form-input">
                                <option value="">-- اختر عميلاً --</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ old('customer_id')==$customer->id?'selected':'' }}>{{ $customer->name }} ({{ $customer->national_id }})</option>
                                @endforeach
                            </select>
                            @error('customer_id') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="template_id" class="form-label">قالب العقد</label>
                            <select id="template_id" name="template_id" required class="form-input">
                                <option value="">-- اختر قالباً --</option>
                                @foreach($templates as $template)
                                    <option value="{{ $template->id }}" {{ old('template_id')==$template->id?'selected':'' }}>{{ $template->name }}</option>
                                @endforeach
                            </select>
                            @error('template_id') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Section: Property Details -->
                <div class="mb-8">
                    <h3 class="text-white font-bold text-base mb-4 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-emerald-500/20 flex items-center justify-center text-emerald-400 text-xs font-black">2</span>
                        بيانات العقار
                    </h3>
                    <div>
                        <label for="property_details" class="form-label">وصف العقار</label>
                        <textarea id="property_details" name="property_details" required class="form-input" rows="3"
                                  placeholder="مثال: شقة في الرياض حي النرجس، مكونة من 3 غرف وصالة...">{{ old('property_details') }}</textarea>
                        @error('property_details') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Section: Duration & Payment -->
                <div class="mb-8">
                    <h3 class="text-white font-bold text-base mb-4 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-amber-500/20 flex items-center justify-center text-amber-400 text-xs font-black">3</span>
                        المدة والمبلغ
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="start_date" class="form-label">تاريخ بداية العقد</label>
                            <input type="date" id="start_date" name="start_date" value="{{ old('start_date') }}" required class="form-input">
                            @error('start_date') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="end_date" class="form-label">تاريخ نهاية العقد</label>
                            <input type="date" id="end_date" name="end_date" value="{{ old('end_date') }}" required class="form-input">
                            @error('end_date') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="rent_amount" class="form-label">قيمة الإيجار (ر.س)</label>
                            <input type="number" id="rent_amount" name="rent_amount" value="{{ old('rent_amount') }}" required min="0" step="0.01" class="form-input" placeholder="مثال: 25000">
                            @error('rent_amount') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="payment_method" class="form-label">طريقة الدفع</label>
                            <select id="payment_method" name="payment_method" required class="form-input">
                                <option value="">-- اختر --</option>
                                <option value="monthly" {{ old('payment_method')=='monthly'?'selected':'' }}>شهري</option>
                                <option value="quarterly" {{ old('payment_method')=='quarterly'?'selected':'' }}>ربع سنوي</option>
                                <option value="semi_annual" {{ old('payment_method')=='semi_annual'?'selected':'' }}>نصف سنوي</option>
                                <option value="annual" {{ old('payment_method')=='annual'?'selected':'' }}>سنوي</option>
                            </select>
                            @error('payment_method') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Section: Additional Terms -->
                <div class="mb-8">
                    <h3 class="text-white font-bold text-base mb-4 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-rose-500/20 flex items-center justify-center text-rose-400 text-xs font-black">4</span>
                        شروط إضافية (اختياري)
                    </h3>
                    <textarea id="additional_terms" name="additional_terms" class="form-input" rows="3"
                              placeholder="أي بنود أو شروط إضافية تريد إضافتها للعقد...">{{ old('additional_terms') }}</textarea>
                </div>

                <div class="flex items-center gap-3 pt-4 border-t border-white/5">
                    <button type="submit" class="btn btn-emerald">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        إنشاء العقد وإرساله
                    </button>
                    <a href="{{ route('contracts.index') }}" class="btn btn-ghost">إلغاء</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
