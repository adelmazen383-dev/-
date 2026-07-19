<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>توقيع العقد | {{ $contract->contract_number }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <style>
        * { font-family: 'Cairo', sans-serif; margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #0f172a; color: #e2e8f0; min-height: 100vh; }

        .sign-container { max-width: 480px; margin: 0 auto; padding: 20px 16px; }

        .sign-header {
            text-align: center;
            padding: 24px 0;
        }
        .sign-header .logo {
            width: 56px; height: 56px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 16px;
            display: inline-flex; align-items: center; justify-content: center;
            margin-bottom: 12px;
            box-shadow: 0 8px 25px rgba(99,102,241,0.3);
        }
        .sign-header .logo svg { width: 28px; height: 28px; color: white; }

        .info-card {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .info-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid rgba(255,255,255,0.04); }
        .info-row:last-child { border: none; }
        .info-label { color: #64748b; font-size: 13px; font-weight: 600; }
        .info-value { color: #e2e8f0; font-size: 14px; font-weight: 700; }

        .canvas-wrapper {
            background: white;
            border-radius: 16px;
            padding: 4px;
            margin-bottom: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }
        #signature-canvas {
            width: 100%;
            height: 200px;
            border-radius: 12px;
            cursor: crosshair;
            touch-action: none;
        }

        .btn-block {
            width: 100%;
            padding: 14px;
            border-radius: 14px;
            font-weight: 800;
            font-size: 16px;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        .btn-sign {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            box-shadow: 0 4px 15px rgba(16,185,129,0.3);
            margin-bottom: 10px;
        }
        .btn-sign:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(16,185,129,0.4); }
        .btn-reject-btn {
            background: rgba(244,63,94,0.1);
            color: #fb7185;
            border: 1px solid rgba(244,63,94,0.2);
        }
        .btn-reject-btn:hover { background: rgba(244,63,94,0.2); }
        .btn-clear {
            background: none;
            border: 1px solid rgba(255,255,255,0.1);
            color: #94a3b8;
            padding: 8px 16px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            margin-bottom: 16px;
        }

        .error-msg { color: #fb7185; font-size: 13px; text-align: center; margin-top: 12px; display: none; font-weight: 600; }
        .success-screen { text-align: center; padding: 40px 20px; }
        .success-screen svg { width: 80px; height: 80px; color: #34d399; margin: 0 auto 16px; }

        .spinner { width: 20px; height: 20px; border: 3px solid rgba(255,255,255,0.3); border-top-color: white; border-radius: 50%; animation: spin 0.8s linear infinite; display: none; }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <div class="sign-container" id="sign-form">
        <!-- Header -->
        <div class="sign-header">
            <div class="logo">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <h1 style="font-size:22px;font-weight:800;color:white;margin-bottom:4px;">
                توقيع عقد الإيجار 
                <span style="font-size:16px; color:#a5b4fc;">({{ $role === 'lessor' ? 'الطرف الأول - المؤجر' : 'الطرف الثاني - المستأجر' }})</span>
            </h1>
            <p style="color:#64748b;font-size:13px;">يرجى مراجعة تفاصيل العقد ثم التوقيع أدناه</p>
        </div>

        <!-- Contract Info -->
        <div class="info-card">
            <div class="info-row">
                <span class="info-label">رقم العقد</span>
                <span class="info-value" style="color:#a5b4fc;font-family:monospace;">{{ $contract->contract_number }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">المؤجر</span>
                <span class="info-value">{{ $contract->lessor->name ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">المستأجر</span>
                <span class="info-value">{{ $contract->customer->name ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">العقار</span>
                <span class="info-value" style="font-size:12px;">{{ Str::limit($contract->property_details, 40) }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">قيمة الإيجار</span>
                <span class="info-value" style="color:#34d399;">{{ number_format($contract->rent_amount) }} ر.س</span>
            </div>
            <div class="info-row">
                <span class="info-label">المدة</span>
                <span class="info-value">{{ $contract->start_date?->format('Y/m/d') }} — {{ $contract->end_date?->format('Y/m/d') }}</span>
            </div>
        </div>

        <!-- Signature Area -->
        <p style="color:#94a3b8;font-size:13px;font-weight:700;margin-bottom:8px;">ارسم توقيعك هنا 👇</p>
        <div class="canvas-wrapper">
            <canvas id="signature-canvas"></canvas>
        </div>
        <button class="btn-clear" id="clear">مسح التوقيع</button>

        <!-- Buttons -->
        <button class="btn-block btn-sign" id="submit">
            <span>أقر بموافقتي وأوقّع العقد</span>
            <div class="spinner" id="spinner"></div>
        </button>
        <button class="btn-block btn-reject-btn" id="reject">
            <span>رفض العقد</span>
            <div class="spinner" id="reject-spinner"></div>
        </button>

        <p class="error-msg" id="error-message"></p>
    </div>

    <!-- Success Screen (hidden) -->
    <div class="sign-container" id="success-screen" style="display:none;">
        <div class="success-screen">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <h2 id="success-title" style="font-size:24px;font-weight:800;color:white;margin-bottom:8px;">تم التوقيع بنجاح ✅</h2>
            <p id="success-message" style="color:#64748b;font-size:14px;"></p>
            <div id="success-download"></div>
        </div>
    </div>

    <script>
        const canvas = document.getElementById('signature-canvas');
        canvas.width = canvas.offsetWidth;
        canvas.height = 200;
        const signaturePad = new SignaturePad(canvas, { backgroundColor: 'rgb(255,255,255)', penColor: 'rgb(15,23,42)' });

        window.addEventListener('resize', () => { canvas.width = canvas.offsetWidth; canvas.height = 200; signaturePad.clear(); });

        document.getElementById('clear').addEventListener('click', () => signaturePad.clear());

        function showError(msg) {
            const el = document.getElementById('error-message');
            el.innerText = msg;
            el.style.display = 'block';
        }
        function hideError() { document.getElementById('error-message').style.display = 'none'; }

        // Reject
        document.getElementById('reject').addEventListener('click', function() {
            if(!confirm('هل أنت متأكد من رفض هذا العقد؟')) return;
            const btn = this;
            const spinner = document.getElementById('reject-spinner');
            btn.disabled = true; btn.style.opacity = '0.6';
            spinner.style.display = 'block';
            hideError();

            axios.post('/sign/{{ $contract->verification_token }}/reject', { _token: '{{ csrf_token() }}' })
            .then(() => {
                document.getElementById('sign-form').innerHTML = '<div class="success-screen"><h2 style="font-size:24px;font-weight:800;color:#fb7185;margin-bottom:8px;">تم رفض العقد</h2><p style="color:#64748b;font-size:14px;">تم إخطار الشركة بقرارك.</p></div>';
            })
            .catch(err => {
                btn.disabled = false; btn.style.opacity = '1'; spinner.style.display = 'none';
                showError(err.response?.data?.message || 'حدث خطأ، يرجى المحاولة مرة أخرى.');
            });
        });

        // Sign
        document.getElementById('submit').addEventListener('click', function() {
            if(signaturePad.isEmpty()) { showError('يرجى رسم توقيعك أولاً في المربع.'); return; }
            hideError();
            const btn = this;
            const spinner = document.getElementById('spinner');
            btn.disabled = true; btn.style.opacity = '0.6';
            spinner.style.display = 'block';

            axios.post('/sign/{{ $contract->verification_token }}', {
                _token: '{{ csrf_token() }}',
                signature: signaturePad.toDataURL('image/png')
            })
            .then((response) => {
                document.getElementById('sign-form').style.display = 'none';
                document.getElementById('success-screen').style.display = 'block';

                const data = response.data;
                document.getElementById('success-message').innerText = data.message;

                if(data.is_complete && data.download_url) {
                    // Lessor signed — contract complete, show download
                    document.getElementById('success-title').innerText = 'العقد مكتمل ✅';
                    document.getElementById('success-download').innerHTML = `<a href="${data.download_url}" target="_blank" style="display:inline-block; margin-top:20px; padding:12px 24px; background:linear-gradient(135deg, #6366f1, #8b5cf6); color:white; border-radius:12px; font-weight:bold; text-decoration:none; box-shadow:0 4px 15px rgba(99,102,241,0.3);">📄 تحميل نسختك من العقد</a>`;
                } else {
                    // Lessee signed — waiting for lessor
                    document.getElementById('success-title').innerText = 'تم حفظ التوقيع ✅';
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
