<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#064e3b">
    <title>توقيع العقد الإلكتروني | {{ $contract->contract_number }} — منصة ثقة</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <style>
        * { font-family: 'Cairo', sans-serif; margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #064e3b;
            background-image:
                radial-gradient(circle at 50% 0%, #065f46 0%, transparent 60%),
                radial-gradient(circle at 10% 90%, #022c22 0%, transparent 70%);
            color: #f8fafc;
            min-height: 100vh;
        }
        .sign-container { max-width: 520px; margin: 0 auto; padding: 20px 16px 40px; }
        .sign-header { text-align: center; padding: 18px 0 22px; }
        .sign-header .logo {
            width: 64px; height: 64px;
            background: #ffffff;
            border-radius: 16px;
            display: inline-flex; align-items: center; justify-content: center;
            padding: 8px;
            margin-bottom: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
        }
        .sign-header .logo img { width: 100%; height: 100%; object-fit: contain; }
        .info-card {
            background: rgba(2, 44, 34, 0.65);
            border: 1px solid rgba(52, 211, 153, 0.25);
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 18px;
        }
        .canvas-wrapper {
            background: #ffffff;
            border-radius: 14px;
            padding: 4px;
            margin-bottom: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.25);
        }
        #signature-canvas { width: 100%; height: 200px; border-radius: 10px; cursor: crosshair; touch-action: none; }
        .btn-block {
            width: 100%; padding: 13px; border-radius: 12px;
            font-weight: 800; font-size: 15px; border: none; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.2s ease;
        }
        .btn-sign { background: linear-gradient(135deg, #10b981, #059669); color: #ffffff; margin-bottom: 10px; box-shadow: 0 4px 15px rgba(16,185,129,0.35); }
        .btn-reject-btn { background: rgba(244,63,94,0.14); color: #fda4af; border: 1px solid rgba(244,63,94,0.3); }
        .btn-clear { background: none; border: 1px solid rgba(255,255,255,0.2); color: #d1fae5; padding: 7px 16px; border-radius: 10px; font-size: 13px; font-weight: 700; cursor: pointer; margin-bottom: 14px; }
        .error-msg { color: #fda4af; font-size: 13px; text-align: center; margin-top: 12px; display: none; font-weight: 700; }
        .success-screen { text-align: center; padding: 48px 20px; }
        .spinner { width: 18px; height: 18px; border: 3px solid rgba(255,255,255,0.3); border-top-color: #ffffff; border-radius: 50%; animation: spin 0.8s linear infinite; display: none; }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <div class="sign-container" id="sign-form">
        <div class="sign-header">
            <div class="logo">
                <img src="{{ asset('images/image.png') }}" alt="منصة ثقة">
            </div>
            <h1 style="font-size:21px; font-weight:900; color:#ffffff; margin-bottom:4px;">
                توقيع عقد الإيجار الإلكتروني
            </h1>
            <p style="color:#6ee7b7; font-size:14px; font-weight:700;">
                {{ $role === 'lessor' ? 'الطرف الأول — المالك (' . $contract->lessor_name . ')' : 'الطرف الثاني — المستأجر (' . $contract->lessee_name . ')' }}
            </p>
            <p style="color:#a7f3d0; font-size:12px; margin-top:4px;">رقم العقد: {{ $contract->contract_number }}</p>
        </div>

        <p style="color:#d1fae5; font-size:13px; font-weight:700; margin-bottom:8px;">يرجى مراجعة العقد بالكامل قبل التوقيع:</p>
        <div class="info-card" style="padding: 0; height: 440px; overflow: hidden;">
            @if($contract->pdf_path)
                <iframe src="{{ asset($contract->pdf_path) }}" width="100%" height="100%" style="border:none; border-radius: 16px; background: white;">
                    <p style="padding:16px;">متصفحك لا يدعم عرض PDF مباشرة. <a href="{{ asset($contract->pdf_path) }}" target="_blank" style="color:#34d399;">اضغط هنا لفتح العقد</a></p>
                </iframe>
            @endif
        </div>
        @if($contract->pdf_path)
            <div style="text-align:center; margin-bottom:16px;">
                <a href="{{ asset($contract->pdf_path) }}" target="_blank" style="color:#6ee7b7; font-size:12.5px; font-weight:700; text-decoration:underline;">
                    فتح نسخة العقد PDF في نافذة مستقلة
                </a>
            </div>
        @endif

        <p style="color:#d1fae5; font-size:13px; font-weight:700; margin-bottom:8px;">ارسم توقيعك داخل المربع أدناه:</p>
        <div class="canvas-wrapper">
            <canvas id="signature-canvas"></canvas>
        </div>
        <button type="button" class="btn-clear" id="clear">مسح التوقيع</button>

        <button type="button" class="btn-block btn-sign" id="submit">
            <span>أقر بموافقتي وأوقّع العقد</span>
            <div class="spinner" id="spinner"></div>
        </button>
        <button type="button" class="btn-block btn-reject-btn" id="reject">
            <span>رفض العقد</span>
            <div class="spinner" id="reject-spinner"></div>
        </button>

        <p class="error-msg" id="error-message"></p>
    </div>

    <div class="sign-container" id="success-screen" style="display:none;">
        <div class="success-screen">
            <svg style="width:72px; height:72px; color:#34d399; margin:0 auto 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h2 id="success-title" style="font-size:22px; font-weight:900; color:#ffffff; margin-bottom:8px;">تم التوقيع بنجاح</h2>
            <p id="success-message" style="color:#d1fae5; font-size:14px; line-height:1.8;"></p>
            <div id="success-download"></div>
        </div>
    </div>

    <script>
        const canvas = document.getElementById('signature-canvas');
        canvas.width = canvas.offsetWidth;
        canvas.height = 200;
        const signaturePad = new SignaturePad(canvas, { backgroundColor: 'rgb(255,255,255)', penColor: 'rgb(15,23,42)' });

        window.addEventListener('resize', () => {
            canvas.width = canvas.offsetWidth;
            canvas.height = 200;
            signaturePad.clear();
        });

        document.getElementById('clear').addEventListener('click', () => signaturePad.clear());

        function showError(msg) {
            const el = document.getElementById('error-message');
            el.innerText = msg;
            el.style.display = 'block';
        }
        function hideError() { document.getElementById('error-message').style.display = 'none'; }

        document.getElementById('reject').addEventListener('click', function () {
            if (!confirm('هل أنت متأكد من رفض هذا العقد؟')) return;
            const btn = this;
            const spinner = document.getElementById('reject-spinner');
            btn.disabled = true; btn.style.opacity = '0.6';
            spinner.style.display = 'block';
            hideError();

            axios.post('{{ route('provider-sign.reject', $contract->verification_token) }}', { _token: '{{ csrf_token() }}' })
                .then(() => {
                    document.getElementById('sign-form').innerHTML =
                        '<div class="success-screen"><h2 style="font-size:22px;font-weight:900;color:#fda4af;margin-bottom:8px;">تم رفض العقد</h2><p style="color:#d1fae5;font-size:14px;">تم إشعار مقدم الخدمة بقرارك.</p></div>';
                })
                .catch(err => {
                    btn.disabled = false; btn.style.opacity = '1'; spinner.style.display = 'none';
                    showError(err.response?.data?.message || 'حدث خطأ، يرجى المحاولة مرة أخرى.');
                });
        });

        document.getElementById('submit').addEventListener('click', function () {
            if (signaturePad.isEmpty()) { showError('يرجى رسم توقيعك أولاً داخل المربع.'); return; }
            hideError();
            const btn = this;
            const spinner = document.getElementById('spinner');
            btn.disabled = true; btn.style.opacity = '0.6';
            spinner.style.display = 'block';

            axios.post('{{ route('provider-sign.store', $contract->verification_token) }}', {
                _token: '{{ csrf_token() }}',
                signature: signaturePad.toDataURL('image/png')
            })
                .then((response) => {
                    document.getElementById('sign-form').style.display = 'none';
                    document.getElementById('success-screen').style.display = 'block';

                    const data = response.data;
                    document.getElementById('success-message').innerText = data.message;

                    if (data.is_complete && data.download_url) {
                        document.getElementById('success-title').innerText = 'تم اعتماد وتوقيع العقد بالكامل';
                        document.getElementById('success-download').innerHTML =
                            `<a href="${data.download_url}" target="_blank" style="display:inline-block; margin-top:20px; padding:12px 24px; background:linear-gradient(135deg, #10b981, #059669); color:white; border-radius:12px; font-weight:800; text-decoration:none; box-shadow:0 4px 15px rgba(16,185,129,0.35);">تحميل نسختك من العقد الموقّع PDF</a>`;
                    } else {
                        document.getElementById('success-title').innerText = 'تم حفظ توقيعك بنجاح';
                    }
                })
                .catch(err => {
                    btn.disabled = false; btn.style.opacity = '1'; spinner.style.display = 'none';
                    showError(err.response?.data?.message || 'حدث خطأ، يرجى المحاولة مرة أخرى.');
                });
        });
    </script>
</body>
</html>
