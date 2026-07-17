@extends('layouts.app')

@section('title', 'إنشاء عقد جديد')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">إنشاء عقد جديد</h2>
        <p class="text-slate-500 text-sm mt-1">تعبئة بيانات العقد لإرساله للعميل</p>
    </div>
    <a href="{{ route('contracts.index') }}" class="text-slate-500 hover:text-slate-700 font-semibold transition">&larr; العودة للقائمة</a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden max-w-4xl">
    <form action="{{ route('contracts.store') }}" method="POST" class="p-6 sm:p-8 space-y-8">
        @csrf

        <!-- 1. اختيار العميل والقالب -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-slate-700 font-bold mb-2">العميل <span class="text-red-500">*</span></label>
                <select name="customer_id" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="" disabled selected>-- اختر العميل --</option>
                    @foreach($customers ?? [] as $customer)
                        <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>{{ $customer->name }} - {{ $customer->national_id }}</option>
                    @endforeach
                </select>
                @error('customer_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-slate-700 font-bold mb-2">قالب العقد <span class="text-red-500">*</span></label>
                <select name="template_id" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="" disabled selected>-- اختر القالب --</option>
                    @foreach($templates ?? [] as $template)
                        <option value="{{ $template->id }}" {{ old('template_id') == $template->id ? 'selected' : '' }}>{{ $template->name }}</option>
                    @endforeach
                </select>
                @error('template_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <hr class="border-slate-100">

        <!-- 2. بيانات العقار -->
        <div>
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                بيانات العقار والمدة
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-slate-700 font-bold mb-2">وصف العقار / العنوان <span class="text-red-500">*</span></label>
                    <input type="text" name="property_details" value="{{ old('property_details') }}" required placeholder="مثال: شقة رقم 5 بالدور الثاني، حي الياسمين..." class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-slate-700 font-bold mb-2">تاريخ بداية العقد <span class="text-red-500">*</span></label>
                    <input type="date" name="start_date" value="{{ old('start_date') }}" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-right">
                </div>

                <div>
                    <label class="block text-slate-700 font-bold mb-2">تاريخ نهاية العقد <span class="text-red-500">*</span></label>
                    <input type="date" name="end_date" value="{{ old('end_date') }}" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-right">
                </div>
            </div>
        </div>

        <hr class="border-slate-100">

        <!-- 3. البيانات المالية -->
        <div>
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                البيانات المالية
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-slate-700 font-bold mb-2">قيمة الإيجار (سنوياً) <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="number" step="0.01" name="rent_amount" value="{{ old('rent_amount') }}" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-left" dir="ltr">
                        <span class="absolute right-3 top-2 text-slate-400">رس</span>
                    </div>
                </div>

                <div>
                    <label class="block text-slate-700 font-bold mb-2">طريقة الدفع</label>
                    <select name="payment_method" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="monthly" {{ old('payment_method') == 'monthly' ? 'selected' : '' }}>شهري</option>
                        <option value="quarterly" {{ old('payment_method') == 'quarterly' ? 'selected' : '' }}>ربع سنوي (كل 3 شهور)</option>
                        <option value="semi_annual" {{ old('payment_method') == 'semi_annual' ? 'selected' : '' }}>نصف سنوي (كل 6 شهور)</option>
                        <option value="annual" {{ old('payment_method') == 'annual' ? 'selected' : '' }}>سنوي</option>
                    </select>
                </div>
            </div>
        </div>

        <hr class="border-slate-100">

        <!-- 4. شروط إضافية -->
        <div>
            <label class="block text-slate-700 font-bold mb-2">شروط وبنود إضافية (تظهر في العقد)</label>
            <textarea name="additional_terms" rows="4" placeholder="أدخل أي شروط إضافية متفق عليها هنا..." class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('additional_terms') }}</textarea>
            <p class="text-slate-400 text-xs mt-1">سيتم إدراج هذه البنود تلقائياً في ملف الـ PDF قبل التوقيع.</p>
        </div>

        <div class="flex justify-end gap-3 pt-4">
            <a href="{{ route('contracts.index') }}" class="px-6 py-2 border border-slate-300 rounded-lg text-slate-700 font-semibold hover:bg-slate-50 transition">إلغاء</a>
            <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold shadow-md transition">إنشاء العقد وتجهيزه للإرسال</button>
        </div>
    </form>
</div>
@endsection
