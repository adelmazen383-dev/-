<x-guest-layout>
    <div class="max-w-md mx-auto mt-10">
        <div class="card text-center p-8">
            @if($status === 'success')
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-emerald-100 mb-6">
                    <svg class="h-8 w-8 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-slate-800 mb-2">تم استلام طلب الدفع</h2>
                <p class="text-slate-600 mb-6">جاري التحقق من نجاح العملية مع البنك. سيتم التواصل معك قريباً.</p>
            @elseif($status === 'declined')
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-rose-100 mb-6">
                    <svg class="h-8 w-8 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-slate-800 mb-2">تم رفض العملية</h2>
                <p class="text-slate-600 mb-6">عذراً، تم رفض الدفع من قبل البنك أو البطاقة.</p>
            @else
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-amber-100 mb-6">
                    <svg class="h-8 w-8 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-slate-800 mb-2">تم إلغاء العملية</h2>
                <p class="text-slate-600 mb-6">لقد قمت بإلغاء عملية الدفع.</p>
            @endif
        </div>
    </div>
</x-guest-layout>
