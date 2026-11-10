<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ثقة | منصة توثيق العقود وإدارة الحجوزات</title>
    <meta name="description" content="منصة ثقة لتوثيق عقود الإيجار وإدارة حجوزات الوحدات السكنية والفندقية - حلول دفع إلكتروني آمنة">
    <meta name="keywords" content="توثيق عقود, إدارة حجوزات, وحدات سكنية, فندقية, دفع إلكتروني, Apple Pay, Visa, Mada, تابي, تمارا">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Cairo', sans-serif; }

        body {
            background: #064e3b;
            background-image: radial-gradient(circle at 50% 20%, #065f46 0%, #022c22 100%);
            min-height: 100vh;
            color: #f8fafc;
        }

        /* ─── Navbar ─── */
        .navbar {
            width: 100%;
            padding: 16px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            max-width: 1200px;
            margin: 0 auto;
        }
        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: #f8fafc;
        }
        .navbar-brand img {
            height: 36px;
            object-fit: contain;
        }
        .navbar-brand span {
            font-size: 22px;
            font-weight: 800;
            color: #34d399;
        }
        .navbar-links {
            display: flex;
            gap: 16px;
            align-items: center;
        }
        .navbar-links a {
            color: rgba(248, 250, 252, 0.7);
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: color 0.3s;
        }
        .navbar-links a:hover { color: #34d399; }
        .btn-login {
            background: rgba(16, 185, 129, 0.2);
            border: 1px solid rgba(16, 185, 129, 0.5);
            color: #34d399;
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 14px;
            text-decoration: none;
            transition: all 0.3s;
        }
        .btn-login:hover {
            background: rgba(16, 185, 129, 0.35);
            transform: translateY(-1px);
        }

        /* ─── Hero ─── */
        .hero {
            text-align: center;
            padding: 60px 24px 40px;
            max-width: 900px;
            margin: 0 auto;
        }
        .hero h1 {
            font-size: 42px;
            font-weight: 900;
            line-height: 1.4;
            margin-bottom: 16px;
            background: linear-gradient(135deg, #34d399, #6ee7b7, #a7f3d0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .hero p {
            font-size: 18px;
            color: rgba(248, 250, 252, 0.7);
            line-height: 1.8;
            max-width: 700px;
            margin: 0 auto 32px;
        }

        /* ─── Features ─── */
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 24px 60px;
        }
        .feature-card {
            background: rgba(6, 78, 59, 0.6);
            border: 1px solid rgba(16, 185, 129, 0.2);
            border-radius: 16px;
            padding: 28px 24px;
            backdrop-filter: blur(12px);
            transition: all 0.3s;
        }
        .feature-card:hover {
            transform: translateY(-4px);
            border-color: rgba(16, 185, 129, 0.5);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.3);
        }
        .feature-icon {
            width: 52px;
            height: 52px;
            background: rgba(16, 185, 129, 0.15);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 16px;
        }
        .feature-card h3 {
            font-size: 18px;
            font-weight: 700;
            color: #34d399;
            margin-bottom: 8px;
        }
        .feature-card p {
            font-size: 14px;
            color: rgba(248, 250, 252, 0.65);
            line-height: 1.7;
        }

        /* ─── Payment Methods ─── */
        .payment-section {
            text-align: center;
            padding: 40px 24px 60px;
            max-width: 900px;
            margin: 0 auto;
        }
        .payment-section h2 {
            font-size: 28px;
            font-weight: 800;
            color: #34d399;
            margin-bottom: 12px;
        }
        .payment-section > p {
            color: rgba(248, 250, 252, 0.6);
            margin-bottom: 32px;
            font-size: 15px;
        }
        .payment-methods {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 16px;
        }
        .payment-badge {
            background: rgba(6, 78, 59, 0.7);
            border: 1px solid rgba(16, 185, 129, 0.25);
            border-radius: 12px;
            padding: 16px 24px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            font-size: 15px;
            color: #d1fae5;
        }
        .payment-badge svg {
            width: 32px;
            height: 20px;
        }

        /* ─── Properties Showcase ─── */
        .properties-section {
            padding: 40px 24px 60px;
            max-width: 1100px;
            margin: 0 auto;
        }
        .properties-section h2 {
            text-align: center;
            font-size: 28px;
            font-weight: 800;
            color: #34d399;
            margin-bottom: 12px;
        }
        .properties-section > p {
            text-align: center;
            color: rgba(248, 250, 252, 0.6);
            margin-bottom: 32px;
            font-size: 15px;
        }
        .properties-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        .property-card {
            background: rgba(6, 78, 59, 0.6);
            border: 1px solid rgba(16, 185, 129, 0.2);
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.3s;
        }
        .property-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.3);
        }
        .property-img {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(6, 78, 59, 0.4));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
        }
        .property-info {
            padding: 20px;
        }
        .property-info h3 {
            font-size: 17px;
            font-weight: 700;
            color: #f8fafc;
            margin-bottom: 8px;
        }
        .property-info .location {
            color: rgba(248, 250, 252, 0.5);
            font-size: 13px;
            margin-bottom: 12px;
        }
        .property-info .price {
            font-size: 20px;
            font-weight: 800;
            color: #34d399;
        }
        .property-info .price span {
            font-size: 13px;
            font-weight: 400;
            color: rgba(248, 250, 252, 0.5);
        }

        /* ─── Stats ─── */
        .stats {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 32px;
            padding: 40px 24px;
            max-width: 900px;
            margin: 0 auto;
        }
        .stat-item {
            text-align: center;
        }
        .stat-item .number {
            font-size: 36px;
            font-weight: 900;
            color: #34d399;
        }
        .stat-item .label {
            font-size: 14px;
            color: rgba(248, 250, 252, 0.5);
            font-weight: 600;
        }

        /* ─── Footer ─── */
        .footer {
            background: rgba(2, 44, 34, 0.5);
            border-top: 1px solid rgba(16, 185, 129, 0.15);
            padding: 40px 24px;
            margin-top: 40px;
        }
        .footer-content {
            max-width: 1100px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 32px;
        }
        .footer h4 {
            color: #34d399;
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 16px;
        }
        .footer p, .footer a {
            color: rgba(248, 250, 252, 0.5);
            font-size: 14px;
            line-height: 1.8;
            text-decoration: none;
        }
        .footer a:hover { color: #34d399; }
        .footer-links {
            list-style: none;
        }
        .footer-links li {
            margin-bottom: 8px;
        }
        .footer-bottom {
            text-align: center;
            padding-top: 24px;
            margin-top: 24px;
            border-top: 1px solid rgba(16, 185, 129, 0.1);
            color: rgba(248, 250, 252, 0.35);
            font-size: 13px;
        }

        /* ─── Responsive ─── */
        @media (max-width: 768px) {
            .hero h1 { font-size: 28px; }
            .hero p { font-size: 15px; }
            .navbar-links { display: none; }
            .stats { gap: 20px; }
            .stat-item .number { font-size: 28px; }
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar">
        <a href="/" class="navbar-brand">
            <span>ثقة</span>
        </a>
        <div class="navbar-links">
            <a href="#features">المميزات</a>
            <a href="#properties">العقارات</a>
            <a href="#payment">طرق الدفع</a>
            <a href="{{ route('login') }}" class="btn-login">تسجيل الدخول</a>
        </div>
    </nav>

    <!-- Hero -->
    <section class="hero">
        <h1>منصة توثيق العقود وإدارة الحجوزات</h1>
        <p>
            نقدم حلولاً متكاملة لإدارة حجوزات الوحدات السكنية والفندقية مع نظام دفع إلكتروني آمن يدعم جميع وسائل الدفع المعتمدة في المملكة العربية السعودية
        </p>
    </section>

    <!-- Features -->
    <section class="features" id="features">
        <div class="feature-card">
            <div class="feature-icon">📄</div>
            <h3>توثيق العقود رقمياً</h3>
            <p>إنشاء وإدارة عقود الإيجار إلكترونياً مع التوقيع الرقمي والتوثيق القانوني الكامل</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">🏠</div>
            <h3>إدارة الوحدات</h3>
            <p>إدارة شاملة للوحدات السكنية والفندقية - الحجوزات والأسعار والتوافر في مكان واحد</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">💳</div>
            <h3>دفع إلكتروني آمن</h3>
            <p>نظام دفع متكامل يدعم Apple Pay، مدى، Visa، Mastercard مع حماية كاملة للبيانات</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">📱</div>
            <h3>فواتير إلكترونية</h3>
            <p>إنشاء وإرسال فواتير إلكترونية للعملاء مع روابط دفع مباشرة عبر الواتساب أو البريد</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">✍️</div>
            <h3>التوقيع الإلكتروني</h3>
            <p>توقيع العقود إلكترونياً من أي مكان بأمان تام مع الحفاظ على صحة التوقيع القانونية</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">📊</div>
            <h3>تقارير وإحصائيات</h3>
            <p>لوحة تحكم شاملة مع تقارير مفصلة عن الإيرادات والحجوزات والعملاء</p>
        </div>
    </section>

    <!-- Properties Showcase -->
    <section class="properties-section" id="properties">
        <h2>نماذج من العقارات المدارة</h2>
        <p>وحدات سكنية وفندقية مميزة في أفضل المواقع بالمملكة</p>
        <div class="properties-grid">
            <div class="property-card">
                <div class="property-img">🏢</div>
                <div class="property-info">
                    <h3>جناح فندقي فاخر - جدة</h3>
                    <div class="location">📍 شارع الكورنيش، جدة</div>
                    <div class="price">350 <span>ر.س / ليلة</span></div>
                </div>
            </div>
            <div class="property-card">
                <div class="property-img">🏨</div>
                <div class="property-info">
                    <h3>شقة مفروشة - الرياض</h3>
                    <div class="location">📍 طريق الملك فهد، الرياض</div>
                    <div class="price">5,000 <span>ر.س / شهرياً</span></div>
                </div>
            </div>
            <div class="property-card">
                <div class="property-img">🏘️</div>
                <div class="property-info">
                    <h3>استوديو عصري - الدمام</h3>
                    <div class="location">📍 شارع الملك سعود، الدمام</div>
                    <div class="price">2,800 <span>ر.س / شهرياً</span></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats -->
    <div class="stats">
        <div class="stat-item">
            <div class="number">+500</div>
            <div class="label">عقد موثّق</div>
        </div>
        <div class="stat-item">
            <div class="number">+200</div>
            <div class="label">وحدة مدارة</div>
        </div>
        <div class="stat-item">
            <div class="number">+1000</div>
            <div class="label">عميل سعيد</div>
        </div>
        <div class="stat-item">
            <div class="number">+15</div>
            <div class="label">مدينة سعودية</div>
        </div>
    </div>

    <!-- Payment Methods -->
    <section class="payment-section" id="payment">
        <h2>طرق الدفع المعتمدة</h2>
        <p>ندعم جميع وسائل الدفع الإلكتروني الشائعة في المملكة العربية السعودية</p>
        <div class="payment-methods">
            <div class="payment-badge">
                <svg viewBox="0 0 38 24" fill="none"><rect width="38" height="24" rx="4" fill="#000"/><text x="19" y="15" text-anchor="middle" fill="#fff" font-size="9" font-weight="bold">Apple Pay</text></svg>
                Apple Pay
            </div>
            <div class="payment-badge">
                <svg viewBox="0 0 38 24" fill="none"><rect width="38" height="24" rx="4" fill="#1A1F71"/><text x="19" y="15" text-anchor="middle" fill="#fff" font-size="10" font-weight="bold">VISA</text></svg>
                Visa
            </div>
            <div class="payment-badge">
                <svg viewBox="0 0 38 24" fill="none"><rect width="38" height="24" rx="4" fill="#EB001B"/><circle cx="15" cy="12" r="7" fill="#EB001B"/><circle cx="23" cy="12" r="7" fill="#F79E1B"/><path d="M19 6.5a7 7 0 0 1 0 11" fill="#FF5F00"/></svg>
                Mastercard
            </div>
            <div class="payment-badge">
                <svg viewBox="0 0 38 24" fill="none"><rect width="38" height="24" rx="4" fill="#0E4C92"/><text x="19" y="15" text-anchor="middle" fill="#fff" font-size="9" font-weight="bold">mada</text></svg>
                مدى
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div>
                <h4>عن ثقة</h4>
                <p>
                    منصة ثقة هي نظام متكامل لتوثيق عقود الإيجار وإدارة حجوزات الوحدات السكنية والفندقية.
                    نوفر حلول دفع إلكتروني آمنة ومتوافقة مع الأنظمة السعودية.
                </p>
                <p style="margin-top: 12px;">
                    <strong style="color: #34d399;">مؤسسة موقع أمر التجارية</strong><br>
                    المملكة العربية السعودية
                </p>
            </div>
            <div>
                <h4>روابط مهمة</h4>
                <ul class="footer-links">
                    <li><a href="{{ route('login') }}">تسجيل الدخول</a></li>
                    <li><a href="/terms">الشروط والأحكام</a></li>
                    <li><a href="/privacy">سياسة الخصوصية</a></li>
                    <li><a href="/refund-policy">سياسة الاسترجاع والإلغاء</a></li>
                </ul>
            </div>
            <div>
                <h4>تواصل معنا</h4>
                <p>📧 <a href="mailto:info@thiqa.me" style="color: inherit; text-decoration: none;">info@thiqa.me</a></p>
                <p>📞 <a href="tel:+966597819851" style="color: #34d399; text-decoration: none; font-weight: 700;">+966 59 781 9851</a></p>
                <p>💬 <a href="https://wa.me/966597819851" target="_blank" style="color: #34d399; text-decoration: none;">مراسلة عبر واتساب</a></p>
                <p>📍 المملكة العربية السعودية</p>
                <p style="margin-top: 16px;">
                    <a href="https://thiqa.me" style="color: #34d399;">🌐 thiqa.me</a>
                </p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© {{ date('Y') }} ثقة - منصة توثيق العقود. جميع الحقوق محفوظة. مؤسسة موقع أمر التجارية.</p>
        </div>
    </footer>

</body>
</html>
