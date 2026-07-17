<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>التحقق من صحة العقد</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Cairo', sans-serif; } </style>
</head>
<body class="bg-slate-50 min-h-screen flex flex-col">

    <div class="flex-grow flex items-center justify-center p-4">
        <div class="max-w-md w-full">
            
            @if(request('signed') == 'true')
                <div class="bg-emerald-100 border border-emerald-400 text-emerald-700 px-4 py-3 rounded mb-6 text-center shadow-sm">
                    ✅ تم حفظ توقيعك بنجاح!
                </div>
            @endif

            <div class="bg-white rounded-2xl shadow-lg p-6 sm:p-8">
                <div class="text-center mb-8">
                    <h1 class="text-2xl font-bold text-slate-800">التحقق من عقد إيجار</h1>
                    <p class="text-slate-500 mt-2 text-sm">أدخل رقم العقد أدناه للتحقق من حالته في النظام الموثق</p>
                </div>

                <form action="{{ url('/verify') }}" method="GET">
                    <div class="mb-4">
                        <label class="block text-slate-700 font-bold mb-2">رقم العقد</label>
                        <input type="text" name="contract_number" value="{{ request('contract_number') }}" required placeholder="مثال: RENT-2026-000125" dir="ltr" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-left">
                    </div>
                    <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-bold py-3 px-4 rounded-lg transition duration-200 shadow-md">
                        تحقق الآن
                    </button>
                </form>

                <!-- نتيجة البحث -->
                @if(isset($contract))
                    <div class="mt-8 pt-6 border-t border-slate-200">
                        @if($contract->status == 'signed')
                            <div class="bg-emerald-50 text-emerald-800 p-4 rounded-lg mb-4 text-center border border-emerald-200">
                                <span class="text-2xl block mb-1">✅</span>
                                <span class="font-bold">عقد موثّق وموقّع</span>
                            </div>
                        @elseif($contract->status == 'cancelled')
                            <div class="bg-red-50 text-red-800 p-4 rounded-lg mb-4 text-center border border-red-200">
                                <span class="text-2xl block mb-1">❌</span>
                                <span class="font-bold">هذا العقد مُلغى</span>
                            </div>
                        @else
                            <div class="bg-amber-50 text-amber-800 p-4 rounded-lg mb-4 text-center border border-amber-200">
                                <span class="text-2xl block mb-1">⏳</span>
                                <span class="font-bold">بانتظار توقيع العميل</span>
                            </div>
                        @endif

                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between border-b pb-2">
                                <span class="text-slate-500">رقم العقد:</span>
                                <span class="font-bold text-slate-800" dir="ltr">{{ $contract->contract_number }}</span>
                            </div>
                            <div class="flex justify-between border-b pb-2">
                                <span class="text-slate-500">اسم العميل:</span>
                                <span class="font-bold text-slate-800">{{ $contract->customer->name }}</span>
                            </div>
                            @if($contract->status == 'signed')
                            <div class="flex justify-between border-b pb-2">
                                <span class="text-slate-500">تاريخ التوقيع:</span>
                                <span class="font-bold text-slate-800" dir="ltr">{{ $contract->signed_at->format('Y-m-d H:i') }}</span>
                            </div>
                            <div class="mt-4 text-center">
                                <a href="{{ asset($contract->signed_pdf_path) }}" target="_blank" class="inline-block bg-blue-100 text-blue-700 font-bold py-2 px-4 rounded hover:bg-blue-200 transition text-sm">
                                    تحميل النسخة الموقعة (PDF)
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                @elseif(request()->has('contract_number'))
                    <div class="mt-8 pt-6 border-t border-slate-200 text-center text-red-500 font-bold">
                        لم يتم العثور على عقد بهذا الرقم. الرجاء التأكد من الرقم والمحاولة مجدداً.
                    </div>
                @endif

            </div>
        </div>
    </div>
</body>
</html>
