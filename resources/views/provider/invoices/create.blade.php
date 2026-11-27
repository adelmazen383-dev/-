@extends('layouts.provider', ['title' => 'إنشاء فاتورة جديدة', 'header' => 'إنشاء فاتورة إلكترونية جديدة'])

@section('content')
    @php
        $type     = $provider->service_type;
        $typeEnum = $provider->serviceTypeEnum();
    @endphp

    <div class="max-w-3xl mx-auto">
        <div class="mb-4 rounded-2xl bg-[#141311] text-white px-4 py-3.5 flex items-center justify-between gap-3 border border-amber-500/30 shadow">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/thiqa-gold-logo.png') }}" alt="منصة ثقة" class="w-10 h-10 object-contain shrink-0">
                <div>
                    <div class="text-xs font-black text-amber-300">
                        @if($type === 'coordination')
                            نموذج طلب منسق — فاتورة إلكترونية
                        @elseif($type === 'photography')
                            نموذج قسم المصورين — فاتورة إلكترونية
                        @elseif($type === 'serviced_suites')
                            نموذج قسم الشقق والأجنحة الفندقية — فاتورة إلكترونية
                        @else
                            نموذج فاتورة خدمة — {{ $provider->service_type_label }}
                        @endif
                    </div>
                    <div class="text-[11px] text-slate-300">تصدر الفاتورة بتصميم منصة ثقة الرسمي وتُضاف للمحفظة فور تأكيد السداد</div>
                </div>
            </div>
        </div>

        <div class="p-card">
            <form method="POST" action="{{ route('provider.invoices.store') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf

                @if($type === 'photography')
                    {{-- ================= ثالثاً: قسم المصورين ================= --}}
                    <div>
                        <h3 class="text-sm font-black text-slate-900 mb-3">1. بيانات العميل وجلسة التصوير</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">اسم العميل <span class="text-rose-500">*</span></label>
                                <input type="text" name="customer_name" value="{{ old('customer_name') }}" required class="p-input" placeholder="الاسم الكامل للعميل">
                                @error('customer_name') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">رقم الجوال <span class="text-rose-500">*</span></label>
                                <input type="tel" name="customer_phone" value="{{ old('customer_phone') }}" required class="p-input" placeholder="05xxxxxxxx" dir="ltr">
                                @error('customer_phone') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">نوع التصوير <span class="text-rose-500">*</span></label>
                                <input type="text" name="service_details[session_type]" value="{{ old('service_details.session_type') }}" required class="p-input" placeholder="فوتوغرافي، فيديو، مناسبة، منتجات...">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">موقع التصوير</label>
                                <input type="text" name="service_details[location]" value="{{ old('service_details.location') }}" class="p-input" placeholder="اسم الاستوديو أو القاعة أو العنوان">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">تاريخ التصوير</label>
                                <input type="date" name="service_details[session_date]" value="{{ old('service_details.session_date') }}" class="p-input">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">وقت الحضور</label>
                                <input type="time" name="service_details[arrival_time]" value="{{ old('service_details.arrival_time') }}" class="p-input" dir="ltr">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">مدة التصوير</label>
                                <input type="text" name="service_details[duration_hours]" value="{{ old('service_details.duration_hours') }}" class="p-input" placeholder="مثال: 3 ساعات">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">الباقة المطلوبة <span class="text-rose-500">*</span></label>
                                <input type="text" name="title" value="{{ old('title') }}" required class="p-input" placeholder="مثال: الباقة الذهبية (صور + فيديو)">
                                @error('title') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 mb-1">اسم المصور المسؤول</label>
                                <input type="text" name="service_details[responsible_name]" value="{{ old('service_details.responsible_name', $provider->name) }}" class="p-input" placeholder="اسم المصور المسؤول عن الجلسة">
                            </div>
                        </div>
                    </div>

                @elseif($type === 'coordination')
                    {{-- ================= نموذج طلب منسق ================= --}}
                    <div>
                        <h3 class="text-sm font-black text-slate-900 mb-3">1. بيانات العميل والمناسبة</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">اسم العميل <span class="text-rose-500">*</span></label>
                                <input type="text" name="customer_name" value="{{ old('customer_name') }}" required class="p-input" placeholder="الاسم الكامل للعميل">
                                @error('customer_name') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">رقم الجوال <span class="text-rose-500">*</span></label>
                                <input type="tel" name="customer_phone" value="{{ old('customer_phone') }}" required class="p-input" placeholder="05xxxxxxxx" dir="ltr">
                                @error('customer_phone') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">رقم الهوية</label>
                                <input type="text" name="customer_national_id" value="{{ old('customer_national_id') }}" class="p-input" placeholder="10xxxxxxxx" dir="ltr">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">نوع الخدمة / المناسبة <span class="text-rose-500">*</span></label>
                                <input type="text" name="title" value="{{ old('title', 'تنسيق مناسبة') }}" required class="p-input" placeholder="مثال: تنسيق مناسبة، تجهيز غرفة، استقبال...">
                                @error('title') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">المنطقة</label>
                                <input type="text" name="service_details[region]" value="{{ old('service_details.region', $provider->city) }}" class="p-input" placeholder="مثال: الرياض، جدة، الشرقية...">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">مكان الموقع</label>
                                <input type="text" name="service_details[venue]" value="{{ old('service_details.venue') }}" class="p-input" placeholder="اسم القاعة، الفندق، الشاليه أو العنوان">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">تاريخ المناسبة</label>
                                <input type="date" name="service_details[event_date]" value="{{ old('service_details.event_date') }}" class="p-input">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">وقت المناسبة</label>
                                <input type="time" name="service_details[event_time]" value="{{ old('service_details.event_time') }}" class="p-input" dir="ltr">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 mb-1">اسم المنسق المسؤول</label>
                                <input type="text" name="service_details[responsible_name]" value="{{ old('service_details.responsible_name', $provider->name) }}" class="p-input">
                            </div>
                        </div>
                    </div>

                @elseif($type === 'serviced_suites')
                    {{-- ================= ثانياً: قسم الشقق والأجنحة الفندقية ================= --}}
                    <div>
                        <h3 class="text-sm font-black text-slate-900 mb-3">1. بيانات المستأجر والوحدة السكنية</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">اسم المستأجر <span class="text-rose-500">*</span></label>
                                <input type="text" name="customer_name" value="{{ old('customer_name') }}" required class="p-input" placeholder="الاسم الكامل للمستأجر">
                                @error('customer_name') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">رقم الجوال <span class="text-rose-500">*</span></label>
                                <input type="tel" name="customer_phone" value="{{ old('customer_phone') }}" required class="p-input" placeholder="05xxxxxxxx" dir="ltr">
                                @error('customer_phone') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">رقم الهوية</label>
                                <input type="text" name="customer_national_id" value="{{ old('customer_national_id') }}" class="p-input" placeholder="10xxxxxxxx" dir="ltr">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">وصف / نوع الحجز <span class="text-rose-500">*</span></label>
                                <input type="text" name="title" value="{{ old('title', 'حجز ' . ($provider->property_kind ?: 'جناح فندقي مخدوم')) }}" required class="p-input">
                                @error('title') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">رقم الدور</label>
                                <input type="text" name="service_details[floor_number]" value="{{ old('service_details.floor_number') }}" class="p-input" placeholder="مثال: الدور الثالث">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">رقم الشقة / الجناح</label>
                                <input type="text" name="service_details[suite_number]" value="{{ old('service_details.suite_number') }}" class="p-input" placeholder="مثال: شقة 304">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">تاريخ الدخول</label>
                                <input type="date" id="check_in_date" name="service_details[check_in_date]" value="{{ old('service_details.check_in_date') }}" onchange="calcNights()" class="p-input">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">تاريخ الخروج</label>
                                <input type="date" id="check_out_date" name="service_details[check_out_date]" value="{{ old('service_details.check_out_date') }}" onchange="calcNights()" class="p-input">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">عدد الليالي</label>
                                <input type="number" min="1" id="nights_input" name="service_details[nights]" value="{{ old('service_details.nights') }}" class="p-input" placeholder="يُحسب تلقائياً" dir="ltr">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">عدد الأشخاص</label>
                                <input type="number" min="1" name="service_details[guests_count]" value="{{ old('service_details.guests_count') }}" class="p-input" placeholder="مثال: 2" dir="ltr">
                            </div>
                        </div>
                    </div>

                @else
                    {{-- ================= قسم أخرى ================= --}}
                    <div>
                        <h3 class="text-sm font-black text-slate-900 mb-3">1. بيانات العميل والخدمة</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">اسم العميل <span class="text-rose-500">*</span></label>
                                <input type="text" name="customer_name" value="{{ old('customer_name') }}" required class="p-input">
                                @error('customer_name') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">رقم الجوال <span class="text-rose-500">*</span></label>
                                <input type="tel" name="customer_phone" value="{{ old('customer_phone') }}" required class="p-input" placeholder="05xxxxxxxx" dir="ltr">
                                @error('customer_phone') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">نوع / عنوان الخدمة <span class="text-rose-500">*</span></label>
                                <input type="text" name="title" value="{{ old('title', $provider->custom_service_name) }}" required class="p-input">
                                @error('title') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">المسؤول</label>
                                <input type="text" name="service_details[responsible_name]" value="{{ old('service_details.responsible_name', $provider->name) }}" class="p-input">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">تاريخ الخدمة</label>
                                <input type="date" name="service_details[execution_date]" value="{{ old('service_details.execution_date') }}" class="p-input">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">الموقع / المنطقة</label>
                                <input type="text" name="service_details[location]" value="{{ old('service_details.location', $provider->city) }}" class="p-input">
                            </div>
                        </div>
                    </div>
                @endif

                {{-- ================= التفاصيل المالية (موحدة واحترافية لجميع الأقسام) ================= --}}
                <div class="pt-4 border-t border-slate-100">
                    <h3 class="text-sm font-black text-slate-900 mb-3">2. التفاصيل المالية</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                {{ $type === 'serviced_suites' ? 'قيمة الحجز (ر.س)' : ($type === 'photography' ? 'السعر المتفق عليه (ر.س)' : 'المبلغ المتفق عليه (ر.س)') }}
                                <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" step="0.01" min="1" max="500000" id="agreed_amount" name="agreed_amount"
                                   value="{{ old('agreed_amount') }}" required oninput="recalcFinancials(true)"
                                   class="p-input font-bold" placeholder="مثال: 2000" dir="ltr">
                            @error('agreed_amount') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                        </div>

                        @if($type === 'serviced_suites')
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">مبلغ التأمين (ر.س)</label>
                                <input type="number" step="0.01" min="0" max="500000" id="deposit_amount" name="deposit_amount"
                                       value="{{ old('deposit_amount', '0') }}" oninput="recalcFinancials(false)"
                                       class="p-input font-bold" placeholder="0.00" dir="ltr">
                            </div>
                        @endif

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">المبلغ المدفوع سابقاً (ر.س)</label>
                            <input type="number" step="0.01" min="0" max="500000" id="paid_previously" name="paid_previously"
                                   value="{{ old('paid_previously', '0') }}" oninput="recalcFinancials(false)"
                                   class="p-input font-bold" placeholder="0.00" dir="ltr">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">المبلغ المتبقي (يُحسب تلقائياً)</label>
                            <input type="text" id="remaining_preview" readonly value="0.00 ر.س"
                                   class="p-input bg-slate-50 font-black text-slate-700" dir="ltr">
                        </div>

                        <div class="sm:col-span-2 bg-[#141311] text-white rounded-2xl p-4 border border-amber-500/40">
                            <label for="amount" class="block text-xs font-extrabold text-amber-300 mb-1.5">
                                {{ $type === 'serviced_suites' ? 'المبلغ المطلوب دفعه الآن في هذه الفاتورة (ر.س)' : 'المبلغ المطلوب كدفعة الآن (ر.س)' }}
                                <span class="text-rose-400">*</span>
                            </label>
                            <input type="number" step="0.01" min="1" max="500000" id="amount" name="amount"
                                   value="{{ old('amount') }}" required
                                   class="w-full rounded-xl bg-white/10 border border-amber-400/50 text-white text-xl font-black px-4 py-2.5 focus:outline-none focus:border-amber-300"
                                   placeholder="مثال: 1000" dir="ltr">
                            <p class="text-[11px] text-slate-300 mt-1.5">هذا هو المبلغ الذي سيظهر بارزاً للعميل للدفع الإلكتروني ويُضاف لرصيد محفظتك عند تأكيد السداد.</p>
                            @error('amount') <p class="text-rose-400 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                        </div>

                        @if($type === 'serviced_suites')
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">هل الحجز مدفوع بالكامل أو جزئي؟</label>
                                <select name="payment_type" class="p-input">
                                    <option value="full" {{ old('payment_type') === 'full' ? 'selected' : '' }}>مدفوع بالكامل</option>
                                    <option value="partial" {{ old('payment_type', 'partial') === 'partial' ? 'selected' : '' }}>دفع جزئي (دفعة / عربون)</option>
                                </select>
                            </div>
                        @endif

                        <div class="{{ $type === 'serviced_suites' ? '' : 'sm:col-span-2' }}">
                            <label class="block text-xs font-bold text-slate-700 mb-1">طريقة الدفع</label>
                            <select name="payment_method" class="p-input">
                                <option value="all" {{ old('payment_method', 'all') === 'all' ? 'selected' : '' }}>جميع الوسائل (Apple Pay / مدى / تابي / تمارا)</option>
                                <option value="apple_pay" {{ old('payment_method') === 'apple_pay' ? 'selected' : '' }}>Apple Pay</option>
                                <option value="standard" {{ old('payment_method') === 'standard' ? 'selected' : '' }}>بطاقة مدى / فيزا / ماستركارد</option>
                                <option value="tabby" {{ old('payment_method') === 'tabby' ? 'selected' : '' }}>تابي (Tabby)</option>
                                <option value="tamara" {{ old('payment_method') === 'tamara' ? 'selected' : '' }}>تمارا (Tamara)</option>
                                <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>كاش (نقداً)</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- ================= مرفقات وملاحظات ================= --}}
                <div class="pt-4 border-t border-slate-100 space-y-3.5">
                    <h3 class="text-sm font-black text-slate-900">3. مرفقات وملاحظات</h3>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            📎 صورة الفاتورة أو عرض السعر (اختياري — JPG / PNG / PDF)
                        </label>
                        <input type="file" name="quote_attachment" accept=".jpg,.jpeg,.png,.webp,.pdf"
                               class="block w-full text-xs text-slate-600 p-2 rounded-xl border border-slate-200 bg-slate-50 file:ml-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-900 file:text-white cursor-pointer">
                        @error('quote_attachment') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            {{ $type === 'photography' ? 'ملاحظات وطلبات خاصة' : 'ملاحظات إضافية' }}
                        </label>
                        <textarea name="notes" rows="2" class="p-input" placeholder="أي تفاصيل أو شروط أو طلبات خاصة تظهر في الفاتورة...">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <a href="{{ route('provider.invoices.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold">إلغاء</a>
                    <button type="submit" class="px-6 py-3 rounded-xl bg-[#141311] hover:bg-black text-amber-300 text-sm font-black shadow-lg transition">
                        ✨ إصدار الفاتورة الإلكترونية وتوليد رابط الدفع
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    let amountTouchedManually = {{ old('amount') ? 'true' : 'false' }};
    const amountEl = document.getElementById('amount');
    if (amountEl) {
        amountEl.addEventListener('input', () => { amountTouchedManually = true; });
    }

    function recalcFinancials(autoFillAmount) {
        const agreed  = parseFloat(document.getElementById('agreed_amount')?.value || '0') || 0;
        const deposit = parseFloat(document.getElementById('deposit_amount')?.value || '0') || 0;
        const paid    = parseFloat(document.getElementById('paid_previously')?.value || '0') || 0;
        const remaining = Math.max(0, (agreed + deposit) - paid);

        const remEl = document.getElementById('remaining_preview');
        if (remEl) {
            remEl.value = remaining.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ر.س';
        }
        if (amountEl && (!amountTouchedManually || autoFillAmount) && remaining > 0) {
            amountEl.value = remaining.toFixed(2);
        }
    }

    function calcNights() {
        const ci = document.getElementById('check_in_date')?.value;
        const co = document.getElementById('check_out_date')?.value;
        const nEl = document.getElementById('nights_input');
        if (ci && co && nEl) {
            const diff = Math.round((new Date(co) - new Date(ci)) / (1000 * 60 * 60 * 24));
            if (diff > 0) nEl.value = diff;
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        recalcFinancials(false);

        const qInput = document.querySelector('input[name="quote_attachment"]');
        if (qInput) {
            const box = document.createElement('div');
            box.className = 'mt-2 hidden';
            qInput.insertAdjacentElement('afterend', box);

            qInput.addEventListener('change', function () {
                box.innerHTML = '';
                const file = qInput.files && qInput.files[0];
                if (!file) { box.classList.add('hidden'); return; }
                const sizeMb = (file.size / (1024 * 1024)).toFixed(2);
                if (file.size > 5 * 1024 * 1024) {
                    box.classList.remove('hidden');
                    box.innerHTML = '<div class="text-xs font-bold text-rose-600 bg-rose-50 border border-rose-200 rounded-lg px-3 py-2">⚠️ حجم الملف (' + sizeMb + ' MB) يتجاوز 5 MB.</div>';
                    qInput.value = '';
                    return;
                }
                const blobUrl = URL.createObjectURL(file);
                box.classList.remove('hidden');
                if (file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf')) {
                    box.innerHTML = '<div class="flex items-center justify-between gap-2 bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs"><span class="font-bold text-slate-700 truncate">📄 ' + file.name + ' (' + sizeMb + ' MB)</span><a href="' + blobUrl + '" target="_blank" class="font-extrabold text-indigo-700 underline shrink-0">معاينة PDF ↗</a></div>';
                } else if (file.type.startsWith('image/')) {
                    box.innerHTML = '<div class="flex items-center gap-3 bg-white border border-slate-200 rounded-xl p-2"><img src="' + blobUrl + '" alt="preview" class="w-16 h-16 rounded-lg object-cover border shrink-0"><div class="text-xs min-w-0"><div class="font-bold text-emerald-700">✅ صورة عرض السعر جاهزة</div><div class="text-slate-500 truncate">' + file.name + ' (' + sizeMb + ' MB)</div></div></div>';
                }
            });
        }
    });
</script>
@endpush
