<x-app-layout>
    <x-slot name="header">
        إعدادات رسوم بوابات الدفع
    </x-slot>

    <div class="max-w-5xl mx-auto space-y-6">

        <!-- Top Header Info -->
        <div class="card bg-gradient-to-l from-emerald-900/10 via-emerald-800/5 to-transparent border-emerald-500/20">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-black text-slate-800 flex items-center gap-2">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        إدارة نسب الرسوم والضرائب الإضافية لطرق الدفع
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-600 mt-1">
                        يمكنك هنا تحديد وتعديل نسب الرسوم المضافة لكل وسيلة دفع مباشرة. تنعكس هذه النسب فوراً على كروت الحجز وفواتير العملاء وجلسات الدفع.
                    </p>
                </div>
                <div class="shrink-0 flex items-center gap-2 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold px-3 py-1.5 rounded-full">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    تحديث فوري ومباشر
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('settings.payment.update') }}">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">

                <!-- 1. Visa / Mastercard / Mada -->
                <div class="card hover:border-emerald-400 transition">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-200 flex items-center justify-center text-blue-700 font-black text-xs">
                                💳
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 text-sm">البطاقات البنكية (Visa / Mastercard / مدى)</h3>
                                <p class="text-[11px] text-slate-500">الدفع المباشر عبر بوابة Telr</p>
                            </div>
                        </div>
                        <span class="bg-blue-100 text-blue-800 text-[11px] font-black px-2 py-0.5 rounded-md">بطاقات</span>
                    </div>

                    <div class="mt-4">
                        <label for="fee_visa_percent" class="form-label">نسبة الرسوم الإضافية (%)</label>
                        <div class="relative">
                            <input type="number" step="0.01" min="0" max="100" id="fee_visa_percent" name="fee_visa_percent"
                                   value="{{ old('fee_visa_percent', $visaFee) }}" required
                                   class="form-input text-base font-bold text-slate-800 pl-10"
                                   oninput="updatePreviewCalculator()">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 font-black text-slate-400 text-sm">%</span>
                        </div>
                        <x-input-error :messages="$errors->get('fee_visa_percent')" class="mt-1" />
                        <p class="text-[11px] text-slate-500 mt-1.5">النسبة الافتراضية الموصى بها: 5.00%</p>
                    </div>
                </div>

                <!-- 2. Apple Pay -->
                <div class="card hover:border-emerald-400 transition">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-10 h-10 rounded-xl bg-slate-900 border border-slate-700 flex items-center justify-center text-white font-black text-xs">
                                
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 text-sm">Apple Pay (محفظة آبل)</h3>
                                <p class="text-[11px] text-slate-500">السداد الفوري بالبصمة عبر أجهزة Apple</p>
                            </div>
                        </div>
                        <span class="bg-slate-100 text-slate-800 text-[11px] font-black px-2 py-0.5 rounded-md">Apple Wallet</span>
                    </div>

                    <div class="mt-4">
                        <label for="fee_apple_pay_percent" class="form-label">نسبة الرسوم الإضافية (%)</label>
                        <div class="relative">
                            <input type="number" step="0.01" min="0" max="100" id="fee_apple_pay_percent" name="fee_apple_pay_percent"
                                   value="{{ old('fee_apple_pay_percent', $applePayFee) }}" required
                                   class="form-input text-base font-bold text-slate-800 pl-10"
                                   oninput="updatePreviewCalculator()">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 font-black text-slate-400 text-sm">%</span>
                        </div>
                        <x-input-error :messages="$errors->get('fee_apple_pay_percent')" class="mt-1" />
                        <p class="text-[11px] text-slate-500 mt-1.5">النسبة الافتراضية الموصى بها: 5.00%</p>
                    </div>
                </div>

                <!-- 3. Tabby -->
                <div class="card hover:border-emerald-400 transition">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-300 flex items-center justify-center text-emerald-700 font-black text-xs">
                                tabby
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 text-sm">تابي (Tabby - تقسيط 4 دفعات)</h3>
                                <p class="text-[11px] text-slate-500">تقسيط مرن عبر بوابة Telr (Store 36090)</p>
                            </div>
                        </div>
                        <span class="bg-emerald-100 text-emerald-800 text-[11px] font-black px-2 py-0.5 rounded-md">4 دفعات</span>
                    </div>

                    <div class="mt-4">
                        <label for="fee_tabby_percent" class="form-label">نسبة الرسوم الإضافية (%)</label>
                        <div class="relative">
                            <input type="number" step="0.01" min="0" max="100" id="fee_tabby_percent" name="fee_tabby_percent"
                                   value="{{ old('fee_tabby_percent', $tabbyFee) }}" required
                                   class="form-input text-base font-bold text-slate-800 pl-10"
                                   oninput="updatePreviewCalculator()">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 font-black text-slate-400 text-sm">%</span>
                        </div>
                        <x-input-error :messages="$errors->get('fee_tabby_percent')" class="mt-1" />
                        <p class="text-[11px] text-slate-500 mt-1.5">النسبة الافتراضية الحالية: 15.00%</p>
                    </div>
                </div>

                <!-- 4. Tamara -->
                <div class="card hover:border-emerald-400 transition">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-10 h-10 rounded-xl bg-amber-50 border border-amber-300 flex items-center justify-center text-amber-700 font-black text-xs">
                                tamara
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 text-sm">تمارا (Tamara - تقسيط 4 دفعات)</h3>
                                <p class="text-[11px] text-amber-600 font-bold">⏳ قيد الاعتماد والتفعيل لدى تيلر</p>
                            </div>
                        </div>
                        <span class="bg-amber-100 text-amber-800 text-[11px] font-black px-2 py-0.5 rounded-md">4 دفعات</span>
                    </div>

                    <div class="mt-4">
                        <label for="fee_tamara_percent" class="form-label">نسبة الرسوم الإضافية (%)</label>
                        <div class="relative">
                            <input type="number" step="0.01" min="0" max="100" id="fee_tamara_percent" name="fee_tamara_percent"
                                   value="{{ old('fee_tamara_percent', $tamaraFee) }}" required
                                   class="form-input text-base font-bold text-slate-800 pl-10"
                                   oninput="updatePreviewCalculator()">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 font-black text-slate-400 text-sm">%</span>
                        </div>
                        <x-input-error :messages="$errors->get('fee_tamara_percent')" class="mt-1" />
                        <p class="text-[11px] text-slate-500 mt-1.5">النسبة الافتراضية الحالية: 15.00%</p>
                    </div>
                </div>

            </div>

            <!-- Submit Action Bar -->
            <div class="flex items-center justify-end gap-3 mb-8">
                <a href="{{ route('dashboard') }}" class="btn btn-ghost">إلغاء</a>
                <button type="submit" class="btn btn-indigo">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    حفظ وتطبيق التغييرات فوراً
                </button>
            </div>
        </form>

        <!-- Live Preview Calculator Card -->
        <div class="card border-slate-300 shadow-sm">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 pb-3 border-b border-slate-100 mb-4">
                <div>
                    <h3 class="text-sm font-black text-slate-800 flex items-center gap-1.5">
                        <span>🧮</span>
                        حاسبة المعاينة التفاعلية المباشرة
                    </h3>
                    <p class="text-xs text-slate-500">جرب إدخال أي مبلغ افتراضي لمشاهدة كيف ستظهر المبالغ للعميل على الكارت الذكي</p>
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-xs font-bold text-slate-600 whitespace-nowrap">مبلغ تجريبي (ر.س):</label>
                    <input type="number" id="preview_test_amount" value="1000" min="1" step="10"
                           class="form-input w-28 text-center font-bold text-emerald-800"
                           oninput="updatePreviewCalculator()">
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 text-slate-500">
                            <th class="py-2.5 px-3">طريقة الدفع</th>
                            <th class="py-2.5 px-3">المبلغ الأساسي</th>
                            <th class="py-2.5 px-3">نسبة الرسوم</th>
                            <th class="py-2.5 px-3">قيمة الرسوم</th>
                            <th class="py-2.5 px-3 font-bold text-slate-700">الإجمالي المطلوب سداده</th>
                            <th class="py-2.5 px-3">تفاصيل التقسيط</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <!-- Visa / Cards -->
                        <tr class="hover:bg-slate-50">
                            <td class="py-3 px-3 font-bold text-slate-800 flex items-center gap-1.5">
                                <span>💳</span> بطاقات مدى / فيزا
                            </td>
                            <td class="py-3 px-3 text-slate-600" id="prev_base_visa">1,000.00 ر.س</td>
                            <td class="py-3 px-3 font-bold text-blue-600" id="prev_pct_visa">5%</td>
                            <td class="py-3 px-3 text-slate-600" id="prev_fee_visa">50.00 ر.س</td>
                            <td class="py-3 px-3 font-black text-emerald-700 text-sm" id="prev_total_visa">1,050.00 ر.س</td>
                            <td class="py-3 px-3 text-slate-400">سداد فوري (دفعة واحدة)</td>
                        </tr>

                        <!-- Apple Pay -->
                        <tr class="hover:bg-slate-50">
                            <td class="py-3 px-3 font-bold text-slate-800 flex items-center gap-1.5">
                                <span></span> Apple Pay
                            </td>
                            <td class="py-3 px-3 text-slate-600" id="prev_base_apple">1,000.00 ر.س</td>
                            <td class="py-3 px-3 font-bold text-slate-800" id="prev_pct_apple">5%</td>
                            <td class="py-3 px-3 text-slate-600" id="prev_fee_apple">50.00 ر.س</td>
                            <td class="py-3 px-3 font-black text-emerald-700 text-sm" id="prev_total_apple">1,050.00 ر.س</td>
                            <td class="py-3 px-3 text-slate-400">سداد فوري عبر المحفظة</td>
                        </tr>

                        <!-- Tabby -->
                        <tr class="hover:bg-slate-50">
                            <td class="py-3 px-3 font-bold text-slate-800 flex items-center gap-1.5">
                                <span class="text-emerald-600 font-black">tabby</span> تابي
                            </td>
                            <td class="py-3 px-3 text-slate-600" id="prev_base_tabby">1,000.00 ر.س</td>
                            <td class="py-3 px-3 font-bold text-emerald-600" id="prev_pct_tabby">15%</td>
                            <td class="py-3 px-3 text-slate-600" id="prev_fee_tabby">150.00 ر.س</td>
                            <td class="py-3 px-3 font-black text-emerald-700 text-sm" id="prev_total_tabby">1,150.00 ر.س</td>
                            <td class="py-3 px-3 font-bold text-emerald-700" id="prev_inst_tabby">4 دفعات &bull; 287.50 ر.س / شهر</td>
                        </tr>

                        <!-- Tamara -->
                        <tr class="hover:bg-slate-50">
                            <td class="py-3 px-3 font-bold text-slate-800 flex items-center gap-1.5">
                                <span class="text-amber-600 font-black">tamara</span> تمارا
                            </td>
                            <td class="py-3 px-3 text-slate-600" id="prev_base_tamara">1,000.00 ر.س</td>
                            <td class="py-3 px-3 font-bold text-amber-600" id="prev_pct_tamara">15%</td>
                            <td class="py-3 px-3 text-slate-600" id="prev_fee_tamara">150.00 ر.س</td>
                            <td class="py-3 px-3 font-black text-emerald-700 text-sm" id="prev_total_tamara">1,150.00 ر.س</td>
                            <td class="py-3 px-3 font-bold text-amber-700" id="prev_inst_tamara">4 دفعات &bull; 287.50 ر.س / شهر</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        function updatePreviewCalculator() {
            const base = parseFloat(document.getElementById('preview_test_amount').value) || 0;
            const visaPct = parseFloat(document.getElementById('fee_visa_percent').value) || 0;
            const applePct = parseFloat(document.getElementById('fee_apple_pay_percent').value) || 0;
            const tabbyPct = parseFloat(document.getElementById('fee_tabby_percent').value) || 0;
            const tamaraPct = parseFloat(document.getElementById('fee_tamara_percent').value) || 0;

            const format = num => Number(num).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ر.س';

            // Visa
            const visaFee = (base * visaPct) / 100;
            const visaTotal = base + visaFee;
            document.getElementById('prev_base_visa').textContent = format(base);
            document.getElementById('prev_pct_visa').textContent = visaPct + '%';
            document.getElementById('prev_fee_visa').textContent = format(visaFee);
            document.getElementById('prev_total_visa').textContent = format(visaTotal);

            // Apple
            const appleFee = (base * applePct) / 100;
            const appleTotal = base + appleFee;
            document.getElementById('prev_base_apple').textContent = format(base);
            document.getElementById('prev_pct_apple').textContent = applePct + '%';
            document.getElementById('prev_fee_apple').textContent = format(appleFee);
            document.getElementById('prev_total_apple').textContent = format(appleTotal);

            // Tabby
            const tabbyFee = (base * tabbyPct) / 100;
            const tabbyTotal = base + tabbyFee;
            const tabbyInst = tabbyTotal / 4;
            document.getElementById('prev_base_tabby').textContent = format(base);
            document.getElementById('prev_pct_tabby').textContent = tabbyPct + '%';
            document.getElementById('prev_fee_tabby').textContent = format(tabbyFee);
            document.getElementById('prev_total_tabby').textContent = format(tabbyTotal);
            document.getElementById('prev_inst_tabby').textContent = `4 دفعات • ${format(tabbyInst)} / شهر`;

            // Tamara
            const tamaraFee = (base * tamaraPct) / 100;
            const tamaraTotal = base + tamaraFee;
            const tamaraInst = tamaraTotal / 4;
            document.getElementById('prev_base_tamara').textContent = format(base);
            document.getElementById('prev_pct_tamara').textContent = tamaraPct + '%';
            document.getElementById('prev_fee_tamara').textContent = format(tamaraFee);
            document.getElementById('prev_total_tamara').textContent = format(tamaraTotal);
            document.getElementById('prev_inst_tamara').textContent = `4 دفعات • ${format(tamaraInst)} / شهر`;
        }

        document.addEventListener('DOMContentLoaded', updatePreviewCalculator);
    </script>
    @endpush
</x-app-layout>
