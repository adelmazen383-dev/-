@extends('layouts.app')

@section('title', isset($client) ? 'تعديل بيانات العميل' : 'إضافة عميل جديد')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">{{ isset($client) ? 'تعديل بيانات العميل' : 'إضافة عميل جديد' }}</h2>
        <p class="text-slate-500 text-sm mt-1">إدخال البيانات الأساسية للعميل لربطها بالعقود</p>
    </div>
    <a href="{{ route('clients.index') }}" class="text-slate-500 hover:text-slate-700 font-semibold transition">&larr; العودة للقائمة</a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden max-w-3xl">
    <!-- هذا الفورم يعمل في حالة الإضافة وحالة التعديل معاً (عن طريق التحقق من وجود المتغير $client) -->
    <form action="{{ isset($client) ? route('clients.update', $client->id) : route('clients.store') }}" method="POST" class="p-6 sm:p-8 space-y-6">
        @csrf
        @if(isset($client))
            @method('PUT')
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- اسم العميل -->
            <div class="md:col-span-2">
                <label class="block text-slate-700 font-bold mb-2">اسم العميل (الرباعي) <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $client->name ?? '') }}" required placeholder="مثال: أحمد محمد علي..." class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- رقم الهوية -->
            <div>
                <label class="block text-slate-700 font-bold mb-2">رقم الهوية / الإقامة <span class="text-red-500">*</span></label>
                <input type="text" name="national_id" value="{{ old('national_id', $client->national_id ?? '') }}" required placeholder="أدخل رقم الهوية المكون من 10 أرقام" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" dir="ltr">
                @error('national_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- رقم الجوال -->
            <div>
                <label class="block text-slate-700 font-bold mb-2">رقم الجوال (لإرسال الواتساب) <span class="text-red-500">*</span></label>
                <input type="text" name="phone" value="{{ old('phone', $client->phone ?? '') }}" required placeholder="مثال: +966500000000" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-left" dir="ltr">
                <p class="text-slate-400 text-xs mt-1 text-right">يجب أن يشمل مفتاح الدولة</p>
                @error('phone') <p class="text-red-500 text-xs mt-1 text-right">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
            <a href="{{ route('clients.index') }}" class="px-6 py-2 border border-slate-300 rounded-lg text-slate-700 font-semibold hover:bg-slate-50 transition">إلغاء</a>
            <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold shadow-md transition">
                {{ isset($client) ? 'حفظ التعديلات' : 'إضافة العميل' }}
            </button>
        </div>
    </form>
</div>
@endsection
