<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تحقق من صحة العقد</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Tajawal', 'sans-serif'] }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Tajawal', sans-serif; background-color: #f8fafc; }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col items-center justify-center p-4">

    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl overflow-hidden">
        
        <div class="bg-slate-900 p-8 text-center relative overflow-hidden">
            <!-- Decorative circle -->
            <div class="absolute -top-10 -right-10 w-32 h-32 bg-indigo-500 rounded-full opacity-20 blur-xl"></div>
            <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-teal-500 rounded-full opacity-20 blur-xl"></div>
            
            <svg class="w-16 h-16 text-indigo-400 mx-auto mb-4 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
            <h1 class="text-2xl font-bold text-white relative z-10">التحقق من صحة العقد</h1>
            <p class="text-slate-400 text-sm mt-2 relative z-10">أدخل رقم العقد للتحقق من حالته وصلاحيته في النظام</p>
        </div>

        <div class="p-8">
            <form action="{{ route('verify.submit') ?? '#' }}" method="GET">
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">رقم العقد</label>
                    <input type="text" name="contract_number" placeholder="مثال: CON-2026-001" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 outline-none transition-all text-left" dir="ltr" required value="{{ request('contract_number') }}">
                </div>
                
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-xl transition-colors shadow-md flex justify-center items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <span>تحقق الآن</span>
                </button>
            </form>

            <!-- Results Section (Conditionally rendered in Laravel) -->
            @if(isset($result))
                <div class="mt-8 pt-6 border-t border-gray-100">
                    
                    @if($result->status === 'active')
                        <!-- ✅ Valid & Active -->
                        <div class="text-center">
                            <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-3">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-800">عقد صحيح وساري</h3>
                            <p class="text-sm text-gray-500 mt-1">هذا العقد مسجل رسمياً في النظام وموثق.</p>
                            
                            <div class="mt-4 bg-gray-50 p-4 rounded-lg text-sm text-right space-y-2">
                                <p><span class="text-gray-500">اسم المستأجر:</span> <span class="font-bold text-gray-800">{{ $result->client_name }}</span></p>
                                <p><span class="text-gray-500">تاريخ الإصدار:</span> <span class="font-bold text-gray-800">{{ $result->issue_date }}</span></p>
                            </div>
                        </div>
                    @elseif($result->status === 'cancelled')
                        <!-- ⚠️ Cancelled -->
                        <div class="text-center">
                            <div class="w-16 h-16 bg-yellow-100 text-yellow-600 rounded-full flex items-center justify-center mx-auto mb-3">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-800">عقد ملغى</h3>
                            <p class="text-sm text-gray-500 mt-1">هذا العقد مسجل ولكنه تم إلغاؤه ولا يعتبر سارياً.</p>
                        </div>
                    @else
                        <!-- ❌ Not Found -->
                        <div class="text-center">
                            <div class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-3">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-800">عقد غير موجود</h3>
                            <p class="text-sm text-gray-500 mt-1">لم نتمكن من العثور على أي عقد بهذا الرقم في سجلاتنا.</p>
                        </div>
                    @endif
                </div>
            @endif

        </div>
    </div>
    
</body>
</html>
