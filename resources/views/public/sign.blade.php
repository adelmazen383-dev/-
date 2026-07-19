<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>توقيع العقد | {{ $contract->contract_number ?? '12345' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
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
        body { font-family: 'Tajawal', sans-serif; background-color: #f3f4f6; }
        .canvas-container {
            position: relative;
            width: 100%;
            height: 250px;
            border: 2px dashed #cbd5e1;
            border-radius: 0.5rem;
            background-color: #ffffff;
            touch-action: none;
        }
        canvas {
            width: 100%;
            height: 100%;
            border-radius: 0.5rem;
        }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col items-center justify-center p-4">

    <div class="max-w-2xl w-full bg-white rounded-2xl shadow-xl overflow-hidden">
        <!-- Header -->
        <div class="bg-indigo-600 p-6 text-center">
            <h1 class="text-2xl font-bold text-slate-800 mb-2">توقيع العقد الإلكتروني</h1>
            <p class="text-indigo-100">رقم العقد: {{ $contract->contract_number ?? 'CON-2026-001' }}</p>
        </div>

        <!-- Contract Info Summary -->
        <div class="p-6 border-b border-gray-100">
            <h2 class="text-lg font-bold text-gray-800 mb-4">ملخص العقد</h2>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="block text-gray-500">الطرف الأول (المالك)</span>
                    <span class="font-bold text-gray-800">{{ $contract->owner_name ?? 'شركة العقارات' }}</span>
                </div>
                <div>
                    <span class="block text-gray-500">الطرف الثاني (المستأجر)</span>
                    <span class="font-bold text-gray-800">{{ $contract->client->name ?? 'أحمد محمد' }}</span>
                </div>
                <div>
                    <span class="block text-gray-500">قيمة الإيجار</span>
                    <span class="font-bold text-indigo-600">{{ $contract->rent_amount ?? '5000' }} ريال</span>
                </div>
                <div>
                    <span class="block text-gray-500">المدة</span>
                    <span class="font-bold text-gray-800">{{ $contract->duration ?? '12 شهر' }}</span>
                </div>
            </div>
            
            <div class="mt-4 text-center">
                <a href="#" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium underline">
                    الاطلاع على مسودة العقد بالكامل (PDF)
                </a>
            </div>
        </div>

        <!-- Signature Area -->
        <div class="p-6 bg-slate-50">
            <h3 class="text-lg font-bold text-gray-800 mb-2">يرجى التوقيع أدناه</h3>
            <p class="text-sm text-gray-500 mb-4">توقيعك هنا يعتبر موافقة نهائية وملزمة قانونياً على جميع بنود العقد.</p>
            
            <div class="canvas-container mb-3">
                <canvas id="signature-pad"></canvas>
            </div>
            
            <div class="flex justify-end mb-6">
                <button type="button" id="clear-btn" class="text-sm text-red-500 hover:text-red-700 font-medium px-3 py-1 bg-red-50 rounded-lg">
                    مسح التوقيع
                </button>
            </div>

            <form id="sign-form" action="{{ route('contracts.sign.submit', $token ?? 'dummy-token') }}" method="POST">
                <!-- CSRF Token would go here in Laravel -->
                <input type="hidden" name="signature" id="signature-input">
                
                <div class="flex items-start gap-3 mb-6">
                    <input type="checkbox" id="agree" class="mt-1 w-5 h-5 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500" required>
                    <label for="agree" class="text-sm text-gray-700">
                        أقر بأنني اطلعت على كافة بنود العقد وأوافق عليها تماماً، وأتحمل المسؤولية القانونية المترتبة على هذا التوقيع.
                    </label>
                </div>

                <button type="submit" id="submit-btn" class="w-full bg-indigo-600 hover:bg-indigo-700 text-slate-800 font-bold py-4 px-6 rounded-xl transition-colors shadow-lg shadow-indigo-200 text-lg flex justify-center items-center gap-2">
                    <span>أوافق وأوقّع العقد</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const canvas = document.getElementById('signature-pad');
            const clearBtn = document.getElementById('clear-btn');
            const form = document.getElementById('sign-form');
            const signatureInput = document.getElementById('signature-input');
            const submitBtn = document.getElementById('submit-btn');
            
            // Adjust canvas resolution for high-DPI displays
            function resizeCanvas() {
                const ratio =  Math.max(window.devicePixelRatio || 1, 1);
                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                canvas.getContext("2d").scale(ratio, ratio);
                signaturePad.clear(); // otherwise drawing will look weird
            }

            window.addEventListener("resize", resizeCanvas);

            const signaturePad = new SignaturePad(canvas, {
                penColor: "rgb(15, 23, 42)", // slate-900
                backgroundColor: "rgba(255, 255, 255, 0)" // transparent
            });
            
            resizeCanvas();

            clearBtn.addEventListener('click', function () {
                signaturePad.clear();
            });

            form.addEventListener('submit', function(e) {
                if (signaturePad.isEmpty()) {
                    e.preventDefault();
                    alert("يرجى رسم التوقيع أولاً قبل الموافقة.");
                    return;
                }
                
                // Get base64 string and put it in the hidden input
                const dataURL = signaturePad.toDataURL('image/png');
                signatureInput.value = dataURL;

                // Disable button and show loading state
                submitBtn.disabled = true;
                submitBtn.innerHTML = `
                    <svg class="animate-spin h-5 w-5 text-slate-800" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>جاري حفظ التوقيع...</span>
                `;
            });
        });
    </script>
</body>
</html>
