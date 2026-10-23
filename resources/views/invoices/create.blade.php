<x-app-layout>
    <x-slot name="header">إنشاء فاتورة وكارت حجز الوحدة السكنية</x-slot>

    <form method="POST" action="{{ route('invoices.store') }}" class="card max-w-3xl mx-auto">
        @csrf

        <div class="border-b border-slate-200 pb-3 mb-5 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm">
                    🪪
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-800">بيانات الفاتورة والكارت الذكي للوحدة</h3>
                    <p class="text-[11px] text-slate-500">حفظ الفاتورة تلقائياً وتوليد الكارت الذكي ورابط الدفع</p>
                </div>
            </div>
            <span class="text-[11px] px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-bold border border-emerald-200">توثيق العقود</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <!-- Customer Name -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-2">اسم العميل *</label>
                <input type="text" name="customer_name" value="{{ old('customer_name') }}" required
                       class="form-input @error('customer_name') border-rose-500 @enderror"
                       placeholder="الاسم الكامل للعميل">
                @error('customer_name') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Phone -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-2">رقم جوال العميل (واتساب) *</label>
                <input type="text" name="phone" value="{{ old('phone') }}" required dir="ltr"
                       class="form-input @error('phone') border-rose-500 @enderror"
                       placeholder="05xxxxxxxx">
                @error('phone') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- National ID -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-2">رقم هوية العميل / الإقامة *</label>
                <input type="text" name="national_id" value="{{ old('national_id') }}" required dir="ltr"
                       class="form-input @error('national_id') border-rose-500 @enderror"
                       placeholder="رقم الهوية الوطنية أو الإقامة">
                @error('national_id') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Suite / Apartment Number -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-2">رقم الجناح / الشقة *</label>
                <input type="text" name="apartment_number" id="apartment_number" value="{{ old('apartment_number') }}" required
                       class="form-input @error('apartment_number') border-rose-500 @enderror"
                       placeholder="مثال: جناح 201">
                <input type="hidden" name="suite_number" id="suite_number" value="{{ old('suite_number') }}">
                @error('apartment_number') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Check-in Date -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-2">تاريخ الدخول</label>
                <input type="date" name="check_in_date" id="check_in_date" value="{{ old('check_in_date', date('Y-m-d')) }}"
                       class="form-input @error('check_in_date') border-rose-500 @enderror">
                <span class="text-[11px] text-slate-500">وقت الدخول المعتاد: 4:00 مساءً</span>
                @error('check_in_date') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Check-out Date -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-2">تاريخ الخروج</label>
                <input type="date" name="check_out_date" id="check_out_date" value="{{ old('check_out_date', date('Y-m-d', strtotime('+1 day'))) }}"
                       class="form-input @error('check_out_date') border-rose-500 @enderror">
                <span class="text-[11px] text-slate-500">وقت الخروج المعتاد: 1:00 ظهراً</span>
                @error('check_out_date') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Agreed Amount -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-2">المبلغ المتفق عليه إجمالاً (ر.س) *</label>
                <input type="number" step="0.01" name="agreed_amount" id="agreed_amount" value="{{ old('agreed_amount') }}" required
                       class="form-input @error('agreed_amount') border-rose-500 @enderror"
                       placeholder="مثال: 1200" oninput="calculateRemaining()">
                @error('agreed_amount') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Amount to Pay Now -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-2">المبلغ المطلوب دفعه الآن (ر.س) *</label>
                <input type="number" step="0.01" name="amount" id="amount" value="{{ old('amount') }}" required
                       class="form-input @error('amount') border-rose-500 @enderror"
                       placeholder="المبلغ المطلوب على رابط الدفع" oninput="calculateRemaining()">
                <span class="text-[11px] text-slate-500">هذا هو المبلغ الذي سينشأ به رابط بوابة الدفع</span>
                @error('amount') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Remaining Amount (Calculated) -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-2">المبلغ المتبقي (ر.س)</label>
                <input type="text" id="remaining_display" readonly
                       class="form-input bg-slate-100 text-slate-700 font-bold font-mono"
                       value="0.00 ر.س">
                <span class="text-[11px] text-slate-500">يُحسب تلقائياً: (المتفق عليه - المطلوب دفعه)</span>
            </div>

            <!-- Deposit Amount -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-2">مبلغ التأمين المسترد (ر.س)</label>
                <input type="number" step="0.01" name="deposit_amount" value="{{ old('deposit_amount', 500) }}"
                       class="form-input @error('deposit_amount') border-rose-500 @enderror"
                       placeholder="500">
                <span class="text-[11px] text-slate-500">المبلغ الافتراضي: 500 ريال مسترد عند المغادرة</span>
                @error('deposit_amount') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Notes -->
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-slate-700 mb-2">ملاحظات إضافية (اختياري)</label>
                <input type="text" name="notes" value="{{ old('notes') }}"
                       class="form-input @error('notes') border-rose-500 @enderror"
                       placeholder="أي شروط خاصة أو ملاحظات تظهر في الكارت">
                @error('notes') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Property / Building Contact Details (Customizable) -->
            <div class="md:col-span-3 border-t border-slate-200 pt-4 mt-2">
                <h4 class="text-xs font-bold text-slate-800 mb-3 flex items-center gap-1.5">
                    <span>🏢 بيانات المنشأة في الفاتورة (قابلة للتعديل حسب رغبتك)</span>
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">اسم العقار / المنشأة</label>
                        <input type="text" name="property_name" value="{{ old('property_name', auth()->user()?->isSindi() ? 'برج داماك الجوهرة | Sindi Residence' : 'توثيق العقود') }}"
                               class="form-input @error('property_name') border-rose-500 @enderror"
                               placeholder="مثال: برج داماك الجوهرة | Sindi Residence">
                        @error('property_name') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">المدينة والموقع</label>
                        <input type="text" name="property_location" value="{{ old('property_location', 'جدة - المملكة العربية السعودية') }}"
                               class="form-input @error('property_location') border-rose-500 @enderror"
                               placeholder="مثال: جدة - المملكة العربية السعودية">
                        @error('property_location') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">رقم هاتف المنشأة للتواصل</label>
                        <input type="text" name="property_phone" dir="ltr" value="{{ old('property_phone', '+966 59 235 8118') }}"
                               class="form-input @error('property_phone') border-rose-500 @enderror"
                               placeholder="+966 59 235 8118">
                        @error('property_phone') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Policy Reminder Box -->
        <div class="mt-5 p-3 rounded-lg bg-amber-50 border border-amber-200 text-xs text-amber-900 space-y-1">
            <p class="font-bold text-amber-950">✨ يظهر تلقائياً في كارت العميل:</p>
            <p class="text-[11.5px]">• مواعيد الدخول: 4:00 مساءً | مواعيد الخروج: 1:00 ظهراً</p>
            <p class="text-[11.5px]">• التأمين: 500 ريال مسترد عند تسليم الوحدة</p>
        </div>

        <div class="mt-8 pt-6 border-t border-slate-200 flex items-center justify-between">
            <a href="{{ route('invoices.index') }}" class="btn btn-ghost">إلغاء</a>
            <button type="submit" class="btn btn-indigo">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                حفظ وإنشاء الكارت الذكي ورابط الدفع
            </button>
        </div>
    </form>

    <script>
        function calculateRemaining() {
            const agreed = parseFloat(document.getElementById('agreed_amount').value) || 0;
            const amount = parseFloat(document.getElementById('amount').value) || 0;
            const remaining = Math.max(0, agreed - amount);
            document.getElementById('remaining_display').value = remaining.toFixed(2) + ' ر.س';

            // Sync suite number with apartment number
            document.getElementById('suite_number').value = document.getElementById('apartment_number').value;
        }

        document.getElementById('apartment_number').addEventListener('input', function() {
            document.getElementById('suite_number').value = this.value;
        });

        // Initialize on load
        calculateRemaining();
    </script>
</x-app-layout>
