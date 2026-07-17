<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>التحقق من العقد | نظام العقود</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Cairo', sans-serif; margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #0f172a; color: #e2e8f0; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .verify-container { max-width: 520px; width: 100%; }
        .verify-header { text-align: center; margin-bottom: 32px; }
        .verify-header .logo {
            width: 64px; height: 64px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 18px;
            display: inline-flex; align-items: center; justify-content: center;
            margin-bottom: 16px;
            box-shadow: 0 8px 25px rgba(99,102,241,0.3);
        }
        .verify-header .logo svg { width: 32px; height: 32px; color: white; }
        .search-card {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 20px;
            padding: 28px;
            margin-bottom: 24px;
        }
        .search-input {
            width: 100%;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 14px;
            color: #e2e8f0;
            padding: 14px 18px;
            font-size: 16px;
            margin-bottom: 12px;
            transition: all 0.3s;
        }
        .search-input:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.15); }
        .search-input::placeholder { color: #475569; }
        .search-btn {
            width: 100%;
            padding: 14px;
            border-radius: 14px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            font-weight: 800;
            font-size: 16px;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(99,102,241,0.3);
        }
        .search-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(99,102,241,0.4); }
        .result-card {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 20px;
            padding: 28px;
            animation: fadeIn 0.5s ease;
        }
        .status-badge {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 20px;
            border-radius: 30px;
            font-size: 15px;
            font-weight: 800;
        }
        .status-signed { background: rgba(16,185,129,0.15); color: #34d399; }
        .status-sent, .status-viewed, .status-draft { background: rgba(245,158,11,0.15); color: #fbbf24; }
        .status-rejected { background: rgba(244,63,94,0.15); color: #fb7185; }
        .status-cancelled { background: rgba(107,114,128,0.15); color: #9ca3af; }
        .info-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,0.04); }
        .info-row:last-child { border: none; }
        .info-label { color: #64748b; font-size: 13px; }
        .info-value { color: #e2e8f0; font-weight: 700; font-size: 14px; }
        .not-found { text-align: center; padding: 24px; color: #fb7185; font-weight: 700; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>
    <div class="verify-container">
        <!-- Header -->
        <div class="verify-header">
            <div class="logo">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <h1 style="font-size:24px;font-weight:800;color:white;">التحقق من صحة العقد</h1>
            <p style="color:#64748b;font-size:14px;margin-top:4px;">أدخل رقم العقد للتحقق من حالته</p>
        </div>

        <!-- Search -->
        <div class="search-card">
            <form method="GET" action="{{ route('verify.index') }}">
                <input type="text" name="contract_number" value="{{ request('contract_number') }}"
                       class="search-input" placeholder="مثال: RENT-2026-000001" dir="ltr" required>
                <button type="submit" class="search-btn">🔍 تحقق الآن</button>
            </form>
        </div>

        <!-- Result -->
        @if(request('contract_number'))
            @if($contract)
                @php
                    $statusClass = ['draft'=>'status-draft','sent'=>'status-sent','viewed'=>'status-viewed','signed'=>'status-signed','rejected'=>'status-rejected','cancelled'=>'status-cancelled'];
                    $statusLabel = ['draft'=>'مسودة','sent'=>'مُرسل','viewed'=>'قيد المراجعة','signed'=>'✅ عقد صالح وموقّع','rejected'=>'مرفوض من العميل','cancelled'=>'ملغى'];
                @endphp
                <div class="result-card">
                    <div style="text-align:center;margin-bottom:20px;">
                        <span class="status-badge {{ $statusClass[$contract->status] ?? 'status-draft' }}">
                            {{ $statusLabel[$contract->status] ?? $contract->status }}
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">رقم العقد</span>
                        <span class="info-value" style="color:#a5b4fc;font-family:monospace;">{{ $contract->contract_number }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">اسم المستأجر</span>
                        <span class="info-value">{{ Str::mask($contract->customer->name ?? '—', '*', 4) }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">تاريخ الإنشاء</span>
                        <span class="info-value">{{ $contract->created_at->format('Y/m/d') }}</span>
                    </div>
                    @if($contract->signed_at)
                    <div class="info-row">
                        <span class="info-label">تاريخ التوقيع</span>
                        <span class="info-value" style="color:#34d399;">{{ $contract->signed_at->format('Y/m/d H:i') }}</span>
                    </div>
                    @endif
                </div>
            @else
                <div class="result-card not-found">
                    <svg style="width:48px;height:48px;margin:0 auto 12px;color:#fb7185;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <p>لم يتم العثور على عقد بهذا الرقم</p>
                </div>
            @endif
        @endif

        <p style="text-align:center;color:#334155;font-size:12px;margin-top:24px;">&copy; {{ date('Y') }} نظام إدارة العقود</p>
    </div>
</body>
</html>
