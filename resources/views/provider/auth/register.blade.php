@extends('layouts.provider-guest', ['title' => 'تسجيل مقدم خدمة جديد', 'maxWidth' => 'max-w-2xl'])

@section('content')
    <div class="mb-5">
        <h2 class="text-lg sm:text-xl font-black text-slate-900">إنشاء حساب مقدم خدمة جديد</h2>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
            اختر نوع خدمتك وأرفق الوثائق المطلوبة ليتم مراجعتها وتفعيل اشتراكك الشهري لإصدار الفواتير
        </p>
    </div>

    <form method="POST" action="{{ route('provider.register.submit') }}" enctype="multipart/form-data" class="space-y-5">
        @csrf

        <!-- 1. Service Type (4 Types) -->
        <div>
            <label class="block text-xs font-extrabold text-slate-800 mb-2">
                1. ما هي الخدمة التي تقدمها؟ <span class="text-rose-500">*</span>
            </label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                @foreach($serviceTypes as $type)
                    <label class="relative flex items-start gap-3 p-3.5 rounded-xl border-2 cursor-pointer transition-all bg-slate-50/70 border-slate-200 hover:border-indigo-400 has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/60">
                        <input type="radio" name="service_type" value="{{ $type['value'] }}"
                               {{ old('service_type', 'serviced_suites') === $type['value'] ? 'checked' : '' }}
                               onchange="updateServiceRequirements()"
                               class="mt-1 text-indigo-600 focus:ring-indigo-500 shrink-0">
                        <div class="min-w-0">
                            <div class="font-extrabold text-sm text-slate-900 flex items-center gap-1.5">
                                <span>{{ $type['icon'] }}</span>
                                <span>{{ $type['label'] }}</span>
                            </div>
                            <p class="text-[11.5px] text-slate-500 mt-0.5 leading-relaxed">{{ $type['subtitle'] }}</p>
                        </div>
                    </label>
                @endforeach
            </div>
            @error('service_type')
                <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Property Kind (for serviced_suites) -->
        <div id="property-kind-wrap" class="hidden bg-emerald-50/80 border border-emerald-200 rounded-xl p-3.5">
            <label for="property_kind" class="block text-xs font-extrabold text-emerald-900 mb-1.5">
                نوع العقار (شقة / فيلا / شاليه / جناح فندقي)
            </label>
            <select id="property_kind" name="property_kind" class="p-input">
                <option value="جناح فندقي" {{ old('property_kind') === 'جناح فندقي' ? 'selected' : '' }}>جناح فندقي مخدوم</option>
                <option value="شقة" {{ old('property_kind') === 'شقة' ? 'selected' : '' }}>شقة</option>
                <option value="فيلا" {{ old('property_kind') === 'فيلا' ? 'selected' : '' }}>فيلا</option>
                <option value="شاليه" {{ old('property_kind') === 'شاليه' ? 'selected' : '' }}>شاليه</option>
            </select>
        </div>

        <!-- Custom Service Name (for other) -->
        <div id="custom-service-wrap" class="hidden bg-sky-50 border border-sky-200 rounded-xl p-3.5">
            <label for="custom_service_name" class="block text-xs font-extrabold text-sky-900 mb-1.5">
                اكتب نوع / اسم خدمتك <span class="text-rose-500">*</span>
            </label>
            <input type="text" id="custom_service_name" name="custom_service_name" value="{{ old('custom_service_name') }}"
                   class="p-input" placeholder="مثال: ضيافة قهوة عربية، تأجير سيارات فارهة، تنظيم رحلات...">
            @error('custom_service_name')
                <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Detailed Service Description -->
        <div>
            <label for="service_description" class="block text-xs font-extrabold text-slate-800 mb-1.5">
                وصف وتفاصيل الخدمة التي تقدمها <span class="text-rose-500">*</span>
            </label>
            <textarea id="service_description" name="service_description" rows="2" required class="p-input"
                      placeholder="اشرح باختصار طبيعة نشاطك أو العقار والخدمات التي تقدمها والمدن المتاحة...">{{ old('service_description') }}</textarea>
            @error('service_description')
                <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- 2. Basic Account Info -->
        <div class="pt-3 border-t border-slate-100">
            <h3 class="text-xs font-extrabold text-slate-800 mb-3">2. بيانات صاحب النشاط / المنشأة</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label for="name" class="block text-xs font-bold text-slate-700 mb-1">اسم صاحب النشاط / الشخص <span class="text-rose-500">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required class="p-input" placeholder="الاسم الثلاثي كما في الهوية">
                    @error('name') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="business_name" class="block text-xs font-bold text-slate-700 mb-1">اسم المنشأة أو اسم الشخص التجاري <span class="text-rose-500">*</span></label>
                    <input type="text" id="business_name" name="business_name" value="{{ old('business_name') }}" required class="p-input" placeholder="اسم المنشأة أو العلامة التجارية">
                    @error('business_name') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="email" class="block text-xs font-bold text-slate-700 mb-1">البريد الإلكتروني (للدخول ورموز OTP) <span class="text-rose-500">*</span></label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required class="p-input" placeholder="name@example.com" dir="ltr">
                    @error('email') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="phone" class="block text-xs font-bold text-slate-700 mb-1">رقم الجوال <span class="text-rose-500">*</span></label>
                    <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" required class="p-input" placeholder="05xxxxxxxx" dir="ltr">
                    @error('phone') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="national_id_number" class="block text-xs font-bold text-slate-700 mb-1">رقم الهوية الوطنية (اختياري)</label>
                    <input type="text" id="national_id_number" name="national_id_number" value="{{ old('national_id_number') }}" class="p-input" placeholder="10xxxxxxxx" dir="ltr">
                </div>

                <div>
                    <label for="city" class="block text-xs font-bold text-slate-700 mb-1">المدينة</label>
                    <input type="text" id="city" name="city" value="{{ old('city') }}" class="p-input" placeholder="الرياض، جدة، الدمام...">
                </div>

                <div class="sm:col-span-2" id="cr-number-wrap">
                    <label for="commercial_register" class="block text-xs font-bold text-slate-700 mb-1">رقم وثيقة العمل الحر أو السجل التجاري / رقم الترخيص (اختياري)</label>
                    <input type="text" id="commercial_register" name="commercial_register" value="{{ old('commercial_register') }}" class="p-input" placeholder="أدخل الرقم إن وُجد" dir="ltr">
                </div>
            </div>
        </div>

        <!-- 3. Required KYC Documents (Dynamic per Service Type) -->
        <div class="pt-3 border-t border-slate-100">
            <div class="flex items-center justify-between gap-2 mb-1">
                <h3 class="text-xs font-extrabold text-slate-800">3. الوثائق والمستندات المطلوبة للتوثيق</h3>
                <span class="text-[10.5px] font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-full">صور JPG/PNG أو PDF (حد أقصى 5MB)</span>
            </div>
            <p id="kyc-summary-hint" class="text-[11.5px] text-slate-600 mb-3 bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 font-semibold"></p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <!-- National ID Front (Required for ALL) -->
                <div class="p-3 rounded-xl border border-slate-200 bg-slate-50/70">
                    <label for="national_id_front" class="block text-xs font-extrabold text-slate-800 mb-1">
                        🪪 صورة الهوية الوطنية (الوجه الأمامي) <span class="text-rose-500">*</span>
                    </label>
                    <input type="file" id="national_id_front" name="national_id_front" accept=".jpg,.jpeg,.png,.webp,.pdf" required
                           class="block w-full text-xs text-slate-600 file:ml-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 cursor-pointer">
                    @error('national_id_front') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- National ID Back (Required for serviced_suites, optional for others) -->
                <div id="doc-id-back-wrap" class="p-3 rounded-xl border border-slate-200 bg-slate-50/70">
                    <label for="national_id_back" class="block text-xs font-extrabold text-slate-800 mb-1">
                        🪪 صورة الهوية الوطنية (الوجه الخلفي) <span id="id-back-req" class="text-rose-500">*</span>
                    </label>
                    <input type="file" id="national_id_back" name="national_id_back" accept=".jpg,.jpeg,.png,.webp,.pdf"
                           class="block w-full text-xs text-slate-600 file:ml-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 cursor-pointer">
                    @error('national_id_back') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Tourism License (Required for serviced_suites) -->
                <div id="doc-tourism-wrap" class="hidden sm:col-span-2 p-3 rounded-xl border-2 border-emerald-200 bg-emerald-50/60">
                    <label for="tourism_license" class="block text-xs font-extrabold text-emerald-900 mb-1">
                        🏨 رخصة السياحة (وزارة السياحة / مرفق الضيافة) <span class="text-rose-500">*</span>
                    </label>
                    <input type="file" id="tourism_license" name="tourism_license" accept=".jpg,.jpeg,.png,.webp,.pdf"
                           class="block w-full text-xs text-slate-700 file:ml-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-emerald-600 file:text-white hover:file:bg-emerald-700 cursor-pointer">
                    @error('tourism_license') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Freelance Document or Commercial Register (Required for coordination & photography) -->
                <div id="doc-commercial-wrap" class="hidden p-3 rounded-xl border-2 border-indigo-200 bg-indigo-50/50">
                    <label for="commercial_doc" class="block text-xs font-extrabold text-indigo-900 mb-1">
                        📜 وثيقة عمل حر أو سجل تجاري <span id="commercial-req" class="text-rose-500">*</span>
                    </label>
                    <input type="file" id="commercial_doc" name="commercial_doc" accept=".jpg,.jpeg,.png,.webp,.pdf"
                           class="block w-full text-xs text-slate-700 file:ml-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 cursor-pointer">
                    @error('commercial_doc') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Photography License (Required for photography) -->
                <div id="doc-photo-wrap" class="hidden p-3 rounded-xl border-2 border-amber-200 bg-amber-50/60">
                    <label for="photography_license" class="block text-xs font-extrabold text-amber-900 mb-1">
                        📸 ترخيص التصوير <span class="text-rose-500">*</span>
                    </label>
                    <input type="file" id="photography_license" name="photography_license" accept=".jpg,.jpeg,.png,.webp,.pdf"
                           class="block w-full text-xs text-slate-700 file:ml-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-amber-600 file:text-white hover:file:bg-amber-700 cursor-pointer">
                    @error('photography_license') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <!-- 4. Bank Account Info (Required for ALL) -->
        <div class="pt-3 border-t border-slate-100">
            <h3 class="text-xs font-extrabold text-slate-800 mb-1">4. بيانات الحساب البنكي لتحويل مستحقاتك <span class="text-rose-500">*</span></h3>
            <p id="bank-owner-hint" class="text-[11px] text-slate-500 mb-3">يجب إدخال حساب بنكي صحيح لتحويل أرصدة محفظتك إليه عند التسوية.</p>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label for="bank_name" class="block text-xs font-bold text-slate-700 mb-1">اسم البنك <span class="text-rose-500">*</span></label>
                    <input type="text" id="bank_name" name="bank_name" value="{{ old('bank_name') }}" required class="p-input" placeholder="الراجحي، الأهلي، الإنماء...">
                    @error('bank_name') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="bank_account_name" class="block text-xs font-bold text-slate-700 mb-1">اسم صاحب الحساب البنكي <span class="text-rose-500">*</span></label>
                    <input type="text" id="bank_account_name" name="bank_account_name" value="{{ old('bank_account_name') }}" required class="p-input" placeholder="بنفس اسم صاحب النشاط">
                    @error('bank_account_name') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="iban" class="block text-xs font-bold text-slate-700 mb-1">رقم الآيبان (IBAN) <span class="text-rose-500">*</span></label>
                    <input type="text" id="iban" name="iban" value="{{ old('iban') }}" required class="p-input" placeholder="SA0000000000000000000000" dir="ltr">
                    @error('iban') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div id="bank-confirm-wrap" class="hidden mt-3 p-3 rounded-xl bg-amber-50 border border-amber-200">
                <label class="flex items-start gap-2.5 text-xs font-extrabold text-amber-900 cursor-pointer">
                    <input type="checkbox" id="bank_owner_confirmed" name="bank_owner_confirmed" value="1" {{ old('bank_owner_confirmed') ? 'checked' : '' }}
                           class="mt-0.5 rounded border-amber-400 text-indigo-600 focus:ring-indigo-500">
                    <span>أُقر بأن الحساب البنكي المُدخل أعلاه هو بنفس اسم صاحب النشاط / العقار المسجل في الهوية الوطنية ورخصة السياحة.</span>
                </label>
                @error('bank_owner_confirmed') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <!-- 5. Password -->
        <div class="pt-3 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-2 gap-3.5">
            <div>
                <label for="password" class="block text-xs font-bold text-slate-700 mb-1">كلمة المرور (8 أحرف على الأقل) <span class="text-rose-500">*</span></label>
                <input type="password" id="password" name="password" required class="p-input" placeholder="••••••••">
                @error('password') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="password_confirmation" class="block text-xs font-bold text-slate-700 mb-1">تأكيد كلمة المرور <span class="text-rose-500">*</span></label>
                <input type="password" id="password_confirmation" name="password_confirmation" required class="p-input" placeholder="••••••••">
            </div>
        </div>

        <button type="submit" class="p-btn-primary">
            تسجيل الحساب ورفع الوثائق
        </button>
    </form>

    <div class="mt-5 pt-4 border-t border-slate-100 text-center">
        <p class="text-xs text-slate-600 font-semibold">
            لديك حساب بالفعل؟
            <a href="{{ route('provider.login') }}" class="text-indigo-600 hover:text-indigo-800 font-black underline mr-1">
                تسجيل الدخول
            </a>
        </p>
    </div>
@endsection

@push('scripts')
<script>
    function updateServiceRequirements() {
        const type = document.querySelector('input[name="service_type"]:checked')?.value || 'serviced_suites';

        const propWrap    = document.getElementById('property-kind-wrap');
        const customWrap  = document.getElementById('custom-service-wrap');
        const customInput = document.getElementById('custom_service_name');

        const idBackInput = document.getElementById('national_id_back');
        const idBackReq   = document.getElementById('id-back-req');

        const tourismWrap  = document.getElementById('doc-tourism-wrap');
        const tourismInput = document.getElementById('tourism_license');

        const commWrap  = document.getElementById('doc-commercial-wrap');
        const commInput = document.getElementById('commercial_doc');
        const commReq   = document.getElementById('commercial-req');

        const photoWrap  = document.getElementById('doc-photo-wrap');
        const photoInput = document.getElementById('photography_license');

        const bankConfirmWrap  = document.getElementById('bank-confirm-wrap');
        const bankConfirmCheck = document.getElementById('bank_owner_confirmed');
        const bankHint         = document.getElementById('bank-owner-hint');
        const kycHint          = document.getElementById('kyc-summary-hint');

        // Reset visibility & required flags
        propWrap.classList.add('hidden');
        customWrap.classList.add('hidden');
        customInput.required = false;

        tourismWrap.classList.add('hidden');
        tourismInput.required = false;

        commWrap.classList.add('hidden');
        commInput.required = false;

        photoWrap.classList.add('hidden');
        photoInput.required = false;

        bankConfirmWrap.classList.add('hidden');
        bankConfirmCheck.required = false;

        idBackInput.required = false;
        idBackReq.classList.add('hidden');

        if (type === 'serviced_suites') {
            propWrap.classList.remove('hidden');
            idBackInput.required = true;
            idBackReq.classList.remove('hidden');
            tourismWrap.classList.remove('hidden');
            tourismInput.required = true;
            bankConfirmWrap.classList.remove('hidden');
            bankConfirmCheck.required = true;
            bankHint.textContent = '⚠️ يشترط أن يكون الحساب البنكي بنفس اسم صاحب النشاط / العقار.';
            kycHint.textContent = '📌 المطلوب لأصحاب العقارات والأجنحة الفندقية: تصوير الهوية الوطنية (أمام وخلف) + رخصة السياحة + حساب بنكي بنفس اسم صاحب النشاط.';
        } else if (type === 'coordination') {
            commWrap.classList.remove('hidden');
            commWrap.classList.add('sm:col-span-2');
            commInput.required = true;
            commReq.classList.remove('hidden');
            bankHint.textContent = 'حساب بنكي باسمك أو باسم المنشأة لتحويل أرباح الفواتير.';
            kycHint.textContent = '📌 المطلوب للمنسقين: وثيقة عمل حر أو سجل تجاري + اسم المنشأة أو الشخص + الهوية الوطنية + حساب بنكي.';
        } else if (type === 'photography') {
            commWrap.classList.remove('hidden');
            commWrap.classList.remove('sm:col-span-2');
            commInput.required = true;
            commReq.classList.remove('hidden');
            photoWrap.classList.remove('hidden');
            photoInput.required = true;
            bankHint.textContent = 'حساب بنكي باسمك أو باسم المنشأة لتحويل أرباح الفواتير.';
            kycHint.textContent = '📌 المطلوب للمصورين: ترخيص التصوير + وثيقة عمل حر أو سجل تجاري + اسم المنشأة أو الشخص + الهوية الوطنية + حساب بنكي.';
        } else {
            customWrap.classList.remove('hidden');
            customInput.required = true;
            commWrap.classList.remove('hidden');
            commWrap.classList.add('sm:col-span-2');
            commInput.required = false;
            commReq.classList.add('hidden');
            bankHint.textContent = 'حساب بنكي لتحويل أرباح الفواتير من المحفظة.';
            kycHint.textContent = '📌 المطلوب للخدمات الأخرى: كتابة اسم وتفاصيل الخدمة + الهوية الوطنية + حساب بنكي (ووثيقة العمل الحر أو السجل التجاري اختياري).';
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        updateServiceRequirements();

        // Live instant preview for all uploaded images & PDFs
        document.querySelectorAll('input[type="file"]').forEach(function (input) {
            const previewBox = document.createElement('div');
            previewBox.className = 'mt-2 hidden';
            input.insertAdjacentElement('afterend', previewBox);

            input.addEventListener('change', function () {
                previewBox.innerHTML = '';
                const file = input.files && input.files[0];
                if (!file) {
                    previewBox.classList.add('hidden');
                    return;
                }
                const sizeMb = (file.size / (1024 * 1024)).toFixed(2);
                if (file.size > 5 * 1024 * 1024) {
                    previewBox.classList.remove('hidden');
                    previewBox.innerHTML = '<div class="text-xs font-bold text-rose-600 bg-rose-50 border border-rose-200 rounded-lg px-2.5 py-1.5">⚠️ حجم الملف (' + sizeMb + ' MB) يتجاوز الحد المسموح (5 MB). يرجى اختيار ملف أصغر.</div>';
                    input.value = '';
                    return;
                }

                const blobUrl = URL.createObjectURL(file);
                previewBox.classList.remove('hidden');

                if (file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf')) {
                    previewBox.innerHTML = '<div class="flex items-center justify-between gap-2 bg-white border border-slate-200 rounded-lg px-2.5 py-2 text-xs"><span class="font-bold text-slate-700 truncate">📄 ' + file.name + ' (' + sizeMb + ' MB)</span><a href="' + blobUrl + '" target="_blank" class="font-extrabold text-indigo-700 shrink-0 underline">معاينة PDF ↗</a></div>';
                } else if (file.type.startsWith('image/')) {
                    previewBox.innerHTML = '<div class="flex items-center gap-2.5 bg-white border border-slate-200 rounded-lg p-2"><img src="' + blobUrl + '" alt="preview" class="w-14 h-14 rounded-lg object-cover border border-slate-200 shrink-0"><div class="min-w-0 text-xs"><div class="font-bold text-emerald-700">✅ الصورة جاهزة للرفع</div><div class="text-slate-500 truncate">' + file.name + ' (' + sizeMb + ' MB)</div></div></div>';
                }
            });
        });
    });
</script>
@endpush
