@extends('layouts.app')

@section('title', 'تفاصيل العقد')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
        <h2 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
            تفاصيل العقد
            <span dir="ltr" class="text-lg bg-slate-100 text-slate-600 px-3 py-1 rounded-md border border-slate-200">{{ $contract->contract_number }}</span>
        </h2>
        <p class="text-slate-500 text-sm mt-1">نظرة عامة على حالة العقد والإجراءات المتاحة</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('contracts.index') }}" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 font-semibold transition shadow-sm">العودة</a>
        @if($contract->status != 'signed' && $contract->status != 'cancelled')
            <form action="{{ route('contracts.cancel', $contract->id ?? 0) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من إلغاء هذا العقد؟ لن يتمكن العميل من توقيعه بعد الإلغاء.');">
                @csrf
                <button type="submit" class="px-4 py-2 bg-red-50 border border-red-200 text-red-600 rounded-lg hover:bg-red-100 font-semibold transition shadow-sm">إلغاء العقد</button>
            </form>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- القسم الأيمن: بيانات العقد -->
    <div class="lg:col-span-2 space-y-6">
        
        <!-- كارت الحالة والإجراءات السريعة -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <!-- أيقونة الحالة -->
                @if($contract->status == 'signed')
                    <div class="w-14 h-14 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">مكتمل وموقّع</h3>
                        <p class="text-slate-500 text-sm">تم التوقيع في: <span dir="ltr">{{ $contract->signed_at ? $contract->signed_at->format('Y-m-d H:i') : '' }}</span></p>
                    </div>
                @elseif($contract->status == 'cancelled')
                    <div class="w-14 h-14 rounded-full bg-red-100 flex items-center justify-center text-red-600 shrink-0">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">ملغى</h3>
                        <p class="text-slate-500 text-sm">تم إلغاء هذا العقد ولا يمكن للعميل توقيعه.</p>
                    </div>
                @else
                    <div class="w-14 h-14 rounded-full bg-amber-100 flex items-center justify-center text-amber-600 shrink-0">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">بانتظار التوقيع</h3>
                        <p class="text-slate-500 text-sm">الحالة الحالية: <span class="font-bold">{{ $contract->status == 'viewed' ? 'تمت المشاهدة من العميل' : 'مُرسل / مسودة' }}</span></p>
                    </div>
                @endif
            </div>

            <!-- أزرار الإجراءات (واتساب و PDF) -->
            <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                @if($contract->status == 'signed')
                    <a href="{{ asset($contract->signed_pdf_path) }}" target="_blank" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-center font-bold rounded-lg shadow-sm transition flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        تحميل العقد الموقّع
                    </a>
                @else
                    <a href="{{ asset($contract->pdf_path) }}" target="_blank" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-center font-bold rounded-lg shadow-sm transition flex items-center justify-center gap-2 border border-slate-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        معاينة المسودة
                    </a>
                    
                    @if($contract->status != 'cancelled')
                    <form action="{{ route('contracts.resend_whatsapp', $contract->id ?? 0) }}" method="POST" class="w-full sm:w-auto">
                        @csrf
                        <button type="submit" class="w-full px-4 py-2 bg-[#25D366] hover:bg-[#128C7E] text-white text-center font-bold rounded-lg shadow-sm transition flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 0C5.385 0 0 5.385 0 12.031c0 2.656.844 5.125 2.375 7.219L.719 23.531l4.437-1.156A11.97 11.97 0 0012.031 24c6.646 0 12.031-5.385 12.031-12.031S18.677 0 12.031 0zm0 22.031a9.92 9.92 0 01-5.125-1.406l-.375-.219-3.094.813.844-3.031-.25-.375a9.947 9.947 0 01-1.531-5.344c0-5.5 4.5-10 10-10 5.5 0 10 4.5 10 10 0 5.5-4.5 10-10 10zm5.344-7.375c-.281-.156-1.719-.844-1.969-.938-.25-.094-.438-.156-.625.125-.188.281-.75.938-.906 1.125-.156.188-.344.219-.625.063-.281-.156-1.219-.438-2.313-1.406-.844-.75-1.438-1.688-1.594-1.969-.156-.281-.031-.438.125-.563.125-.125.281-.344.438-.5.156-.156.219-.281.344-.469.125-.188.063-.344 0-.5-.063-.125-.625-1.5-.844-2.063-.219-.531-.438-.469-.625-.469-.156 0-.344-.031-.531-.031-.188 0-.5.063-.781.344-.281.281-1.063 1.031-1.063 2.5 0 1.469 1.094 2.875 1.25 3.094.156.219 2.125 3.188 5.125 4.469 1.938.813 2.813.938 3.844.875 1.156-.063 3.531-1.438 4.031-2.844.5-1.406.5-2.625.344-2.844-.125-.25-.469-.375-.75-.531z"/></svg>
                            إرسال الرابط (واتساب)
                        </button>
                    </form>
                    @endif
                @endif
            </div>
        </div>

        <!-- بيانات العميل والعقار -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 font-bold text-slate-800">تفاصيل العقد والعميل</div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 text-sm sm:text-base">
                    <div>
                        <span class="block text-slate-500 text-xs mb-1">اسم العميل</span>
                        <span class="font-semibold text-slate-800">{{ $contract->customer->name ?? '' }}</span>
                    </div>
                    <div>
                        <span class="block text-slate-500 text-xs mb-1">رقم الهوية</span>
                        <span class="font-semibold text-slate-800">{{ $contract->customer->national_id ?? '' }}</span>
                    </div>
                    <div>
                        <span class="block text-slate-500 text-xs mb-1">رقم الجوال</span>
                        <span class="font-semibold text-slate-800" dir="ltr">{{ $contract->customer->phone ?? '' }}</span>
                    </div>
                    <div>
                        <span class="block text-slate-500 text-xs mb-1">قيمة الإيجار</span>
                        <span class="font-semibold text-emerald-600">{{ number_format($contract->rent_amount ?? 0, 2) }} ر.س</span>
                    </div>
                    <div class="md:col-span-2">
                        <span class="block text-slate-500 text-xs mb-1">تفاصيل العقار</span>
                        <span class="font-semibold text-slate-800">{{ $contract->property_details ?? 'غير محدد' }}</span>
                    </div>
                    <div>
                        <span class="block text-slate-500 text-xs mb-1">تاريخ البداية</span>
                        <span class="font-semibold text-slate-800" dir="ltr">{{ $contract->start_date ? \Carbon\Carbon::parse($contract->start_date)->format('Y-m-d') : '' }}</span>
                    </div>
                    <div>
                        <span class="block text-slate-500 text-xs mb-1">تاريخ النهاية</span>
                        <span class="font-semibold text-slate-800" dir="ltr">{{ $contract->end_date ? \Carbon\Carbon::parse($contract->end_date)->format('Y-m-d') : '' }}</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- القسم الأيسر: سجل الأحداث Audit Trail -->
    <div class="space-y-6">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 font-bold text-slate-800 flex justify-between items-center">
                سجل النظام (Audit Trail)
                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div class="p-6">
                @if(isset($contract->logs) && count($contract->logs) > 0)
                <ol class="relative border-r-2 border-slate-200 mr-2 space-y-6">
                    @foreach($contract->logs as $log)
                    <li class="pr-6">
                        <span class="absolute flex items-center justify-center w-4 h-4 bg-blue-100 rounded-full -right-[9px] ring-4 ring-white">
                            <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                        </span>
                        <h4 class="flex items-center mb-1 text-sm font-bold text-slate-800">{{ $log->event }}</h4>
                        <time class="block mb-2 text-xs font-normal text-slate-400" dir="ltr">{{ $log->created_at->format('Y-m-d H:i:s') }}</time>
                        @if($log->meta)
                            <p class="text-xs text-slate-500 bg-slate-50 p-2 rounded border border-slate-100" dir="ltr">{{ is_string($log->meta) ? $log->meta : json_encode($log->meta, JSON_UNESCAPED_UNICODE) }}</p>
                        @endif
                    </li>
                    @endforeach
                </ol>
                @else
                <p class="text-slate-500 text-sm text-center">لا توجد سجلات متاحة حتى الآن.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
