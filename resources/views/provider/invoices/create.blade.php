@extends('layouts.provider', ['title' => 'إنشاء فاتورة جديدة', 'header' => 'إنشاء فاتورة جديدة للعميل'])

@section('content')
    @php
        $typeEnum = $provider->serviceTypeEnum();
    @endphp

    <div class="max-w-3xl mx-auto">
        <div class="mb-4 rounded-xl bg-indigo-50 border border-indigo-200 px-4 py-3 flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <span class="text-xl">{{ $typeEnum->icon() }}</span>
                <div>
                    <div class="text-xs font-black text-indigo-900">قالب الفاتورة النشط: {{ $provider->service_type_label }}</div>
                    <div class="text-[11px] text-indigo-700">سيتم إصدار الفاتورة بقالب مخصص لنوع خدمتك، وبمجرد سداد العميل وتأكيد الدفع يُضاف المبلغ مباشرة لرصيد محفظتك.</div>
                </div>
            </div>
        </div>

        <div class="p-card">
            <form method="POST" action="{{ route('provider.invoices.store') }}" class="space-y-5">
                @csrf

                <!-- Customer Info -->
                <div>
                    <h3 class="text-sm font-black text-slate-900 mb-3">1. بيانات العميل</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
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
                            <label class="block text-xs font-bold text-slate-700 mb-1">البريد الإلكتروني (اختياري)</label>
                            <input type="email" name="customer_email" value="{{ old('customer_email') }}" class="p-input" placeholder="client@example.com" dir="ltr">
                            @error('customer_email') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Main Invoice Info -->
                <div class="pt-4 border-t border-slate-100">
                    <h3 class="text-sm font-black text-slate-900 mb-3">2. تفاصيل الفاتورة والمبلغ</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 mb-1">عنوان الخدمة / الباقة <span class="text-rose-500">*</span></label>
                            <input type="text" name="title" value="{{ old('title') }}" required class="p-input"
                                   placeholder="@switch($provider->service_type) @case('coordination') مثال: تنسيق غرفة مناسبة خاصة باقة VIP @break @case('photography') مثال: جلسة تصوير احترافية مع ألبوم رقمي @break @case('serviced_suites') مثال: حجز جناح فندقي مخدوم لمدة ليلتين @break @default مثال: تقديم خدمة مخصصة حسب الاتفاق @endswitch">
                            @error('title') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">المبلغ المطلوب (ر.س) <span class="text-rose-500">*</span></label>
                            <input type="number" step="0.01" min="1" max="500000" name="amount" value="{{ old('amount') }}" required class="p-input font-black text-indigo-800" placeholder="0.00" dir="ltr">
                            @error('amount') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="block text-xs font-bold text-slate-700 mb-1">وصف وبنود الخدمة (اختياري)</label>
                        <textarea name="description" rows="2" class="p-input" placeholder="تفاصيل الباقة أو البنود المشمولة في هذه الفاتورة...">{{ old('description') }}</textarea>
                    </div>
                </div>

                <!-- Service-Type Specific Fields (4 Tailored Templates) -->
                <div class="pt-4 border-t border-slate-100">
                    <h3 class="text-sm font-black text-slate-900 mb-3">3. بيانات مخصصة لقالب ({{ $typeEnum->label() }})</h3>

                    @if($provider->service_type === 'coordination')
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">نوع المناسبة</label>
                                <input type="text" name="service_details[event_type]" value="{{ old('service_details.event_type') }}" class="p-input" placeholder="مثال: ذكرى زواج، تخرج، استقبال مولود...">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">تاريخ المناسبة / التنفيذ</label>
                                <input type="date" name="service_details[event_date]" value="{{ old('service_details.event_date') }}" class="p-input">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">الموقع / اسم القاعة أو الجناح</label>
                                <input type="text" name="service_details[venue]" value="{{ old('service_details.venue') }}" class="p-input" placeholder="اسم الفندق أو العنوان">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">الألوان أو عناصر التنسيق</label>
                                <input type="text" name="service_details[theme]" value="{{ old('service_details.theme') }}" class="p-input" placeholder="مثال: ورد طبيعي، بالونات، كيك، إضاءة...">
                            </div>
                        </div>
                    @elseif($provider->service_type === 'photography')
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">نوع التصوير</label>
                                <input type="text" name="service_details[session_type]" value="{{ old('service_details.session_type') }}" class="p-input" placeholder="مثال: فوتوغرافي، فيديو ريلز، تغطية مناسبة...">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">تاريخ الجلسة</label>
                                <input type="date" name="service_details[session_date]" value="{{ old('service_details.session_date') }}" class="p-input">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">مدة التصوير</label>
                                <input type="text" name="service_details[duration_hours]" value="{{ old('service_details.duration_hours') }}" class="p-input" placeholder="مثال: 3 ساعات">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">موقع التصوير</label>
                                <input type="text" name="service_details[location]" value="{{ old('service_details.location') }}" class="p-input" placeholder="الاستوديو أو موقع خارجي">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 mb-1">المخرجات ومدة التسليم</label>
                                <input type="text" name="service_details[deliverables]" value="{{ old('service_details.deliverables') }}" class="p-input" placeholder="مثال: 40 صورة معدّلة + فيديو دقيقة خلال 5 أيام">
                            </div>
                        </div>
                    @elseif($provider->service_type === 'serviced_suites')
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">اسم العقار / البرج الفندقي</label>
                                <input type="text" name="service_details[property_name]" value="{{ old('service_details.property_name', $provider->business_name) }}" class="p-input">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">رقم الجناح / الوحدة</label>
                                <input type="text" name="service_details[suite_number]" value="{{ old('service_details.suite_number') }}" class="p-input" placeholder="مثال: جناح 405">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">تاريخ الدخول</label>
                                <input type="date" name="service_details[check_in_date]" value="{{ old('service_details.check_in_date') }}" class="p-input">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">تاريخ الخروج</label>
                                <input type="date" name="service_details[check_out_date]" value="{{ old('service_details.check_out_date') }}" class="p-input">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">عدد النزلاء</label>
                                <input type="text" name="service_details[guests_count]" value="{{ old('service_details.guests_count') }}" class="p-input" placeholder="مثال: 2 بالغين">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">خدمات إضافية مشمولة</label>
                                <input type="text" name="service_details[extras]" value="{{ old('service_details.extras') }}" class="p-input" placeholder="مثال: ضيافة، تنظيف يومي، موقف خاص">
                            </div>
                        </div>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">نوع الخدمة</label>
                                <input type="text" name="service_details[custom_service_type]" value="{{ old('service_details.custom_service_type', $provider->custom_service_name) }}" class="p-input" placeholder="نوع الخدمة المقدمة">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">تاريخ تقديم الخدمة</label>
                                <input type="date" name="service_details[execution_date]" value="{{ old('service_details.execution_date') }}" class="p-input">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">الموقع / المدينة</label>
                                <input type="text" name="service_details[location]" value="{{ old('service_details.location', $provider->city) }}" class="p-input">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">تفاصيل التسليم</label>
                                <input type="text" name="service_details[deliverables]" value="{{ old('service_details.deliverables') }}" class="p-input" placeholder="تفاصيل إضافية">
                            </div>
                        </div>
                    @endif
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">ملاحظات أو شروط إضافية للعميل (اختياري)</label>
                    <textarea name="notes" rows="2" class="p-input" placeholder="أي شروط أو ملاحظات تظهر أسفل الفاتورة...">{{ old('notes') }}</textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <a href="{{ route('provider.invoices.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold">إلغاء</a>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-extrabold shadow">
                        ✅ إصدار الفاتورة وتوليد رابط الدفع
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
