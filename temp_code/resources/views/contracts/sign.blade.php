<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>توقيع العقد | {{ $contract->contract_number ?? '' }}</title>
    <!-- Tailwind CSS & Fonts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Cairo', sans-serif; touch-action: none; /* لمنع السحب أثناء التوقيع */ } </style>
    <!-- Axios & Signature Pad -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-lg w-full bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="bg-blue-600 text-white px-6 py-5 text-center">
            <h1 class="text-xl font-bold">توقيع العقد الإلكتروني</h1>
            <p class="text-blue-100 mt-1 text-sm">رقم العقد: <span dir="ltr">{{ $contract->contract_number ?? 'N/A' }}</span></p>
        </div>

        <div class="p-6">
            <!-- تفاصيل سريعة -->
            <div class="bg-blue-50 border-r-4 border-blue-500 p-4 rounded text-sm text-slate-700 mb-6 space-y-2">
                <p><span class="font-bold">الطرف الثاني:</span> {{ $contract->customer->name ?? 'N/A' }}</p>
                <p><span class="font-bold">الهوية:</span> {{ $contract->customer->national_id ?? 'N/A' }}</p>
                <div class="pt-2 mt-2 border-t border-blue-200">
                    <a href="{{ asset($contract->pdf_path ?? '#') }}" target="_blank" class="text-blue-600 hover:text-blue-800 font-bold flex items-center gap-1">
                        📄 اضغط هنا لمراجعة مسودة العقد قبل التوقيع
                    </a>
                </div>
            </div>

            <!-- منطقة التوقيع -->
            <div class="mb-6">
                <label class="block text-slate-700 font-bold mb-2">الرجاء التوقيع بإصبعك في المربع أدناه:</label>
                <div class="border-2 border-dashed border-slate-300 rounded-lg bg-slate-50 relative">
                    <!-- Canvas -->
                    <canvas id="signature-pad" class="w-full h-56 rounded-lg cursor-crosshair"></canvas>
                </div>
                <div class="flex justify-end mt-2">
                    <button type="button" id="clear" class="text-sm text-red-500 hover:text-red-700 font-bold">مسح التوقيع وإعادة المحاولة</button>
                </div>
            </div>

            <!-- أزرار الإجراءات -->
            <div class="space-y-3">
                <button id="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-4 rounded-xl shadow-md transition duration-200 flex items-center justify-center gap-2">
                    <span>أقر بموافقتي وأوقّع العقد</span>
                    <svg id="spinner" class="w-5 h-5 hidden animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                </button>
                <button id="reject" type="button" class="w-full bg-red-100 hover:bg-red-200 text-red-700 font-bold py-3 px-4 rounded-xl shadow-sm transition duration-200 flex items-center justify-center gap-2">
                    <span>رفض العقد</span>
                    <svg id="reject-spinner" class="w-5 h-5 hidden animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                </button>
            </div>
            <p id="error-message" class="text-red-500 text-sm mt-3 text-center hidden"></p>
        </div>
    </div>

    <script>
        const canvas = document.getElementById('signature-pad');
        const signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgb(248, 250, 252)', // slate-50
            penColor: 'rgb(15, 23, 42)' // slate-900
        });

        // لضبط دقة Canvas على الموبايل وعدم تمطط الخط
        function resizeCanvas() {
            const ratio =  Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext("2d").scale(ratio, ratio);
            signaturePad.clear();
        }
        window.addEventListener("resize", resizeCanvas);
        resizeCanvas();

        document.getElementById('clear').addEventListener('click', () => signaturePad.clear());

        document.getElementById('reject').addEventListener('click', function () {
            if(!confirm('هل أنت متأكد من رفضك لهذا العقد؟ سيتم إخطار الشركة بذلك.')) return;
            
            const btn = this;
            const spinner = document.getElementById('reject-spinner');
            
            btn.disabled = true;
            btn.classList.add('opacity-75', 'cursor-not-allowed');
            spinner.classList.remove('hidden');
            document.getElementById('error-message').classList.add('hidden');

            axios.post('/sign/{{ $contract->verification_token ?? '' }}/reject', {
                _token: '{{ csrf_token() }}'
            })
            .then(res => {
                window.location.href = '/verify?contract_number={{ $contract->contract_number ?? '' }}&rejected=true';
            })
            .catch(err => {
                btn.disabled = false;
                btn.classList.remove('opacity-75', 'cursor-not-allowed');
                spinner.classList.add('hidden');
                
                let msg = 'حدث خطأ، يرجى المحاولة مرة أخرى.';
                if(err.response && err.response.data.message) msg = err.response.data.message;
                
                document.getElementById('error-message').innerText = msg;
                document.getElementById('error-message').classList.remove('hidden');
            });
        });

        document.getElementById('submit').addEventListener('click', function () {
            if (signaturePad.isEmpty()) {
                document.getElementById('error-message').innerText = 'يرجى رسم توقيعك أولاً في المربع.';
                document.getElementById('error-message').classList.remove('hidden');
                return;
            }

            const btn = this;
            const spinner = document.getElementById('spinner');
            
            btn.disabled = true;
            btn.classList.add('opacity-75', 'cursor-not-allowed');
            spinner.classList.remove('hidden');
            document.getElementById('error-message').classList.add('hidden');

            // إرسال الصورة كـ Base64
            axios.post('/sign/{{ $contract->verification_token ?? '' }}', {
                signature: signaturePad.toDataURL('image/png'),
                _token: '{{ csrf_token() }}'
            })
            .then(res => {
                // توجيه العميل لصفحة التحقق مع رسالة نجاح
                window.location.href = '/verify?contract_number={{ $contract->contract_number ?? '' }}&signed=true';
            })
            .catch(err => {
                btn.disabled = false;
                btn.classList.remove('opacity-75', 'cursor-not-allowed');
                spinner.classList.add('hidden');
                
                let msg = 'حدث خطأ، يرجى المحاولة مرة أخرى.';
                if(err.response && err.response.data.message) msg = err.response.data.message;
                
                document.getElementById('error-message').innerText = msg;
                document.getElementById('error-message').classList.remove('hidden');
            });
        });
    </script>
</body>
</html>
