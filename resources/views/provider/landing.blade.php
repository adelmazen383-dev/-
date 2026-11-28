<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">
    <meta name="theme-color" content="#064e3b">

    <title>بوابة مقدمي الخدمات — منصة ثقة | إصدار فواتير إلكترونية وتحصيل مدفوعات في السعودية</title>
    <meta name="description" content="انضم إلى بوابة مقدمي الخدمات في منصة ثقة (thiqa.me) لإصدار فواتير إلكترونية فاخرة وتحصيل مدفوعات عملائك عبر Apple Pay ومدى وفيزا وتابي وتمارا (4 دفعات) لمقدمي الشقق والأجنحة الفندقية ومنسقي الحفلات والمصورين والخدمات الحرة في السعودية.">
    <meta name="keywords" content="منصة ثقة, بوابة مقدمي الخدمات, فواتير إلكترونية السعودية, رابط دفع تابي, رابط دفع تمارا, فواتير منسقي حفلات, فواتير مصورين, فواتير شقق فندقية مخدومة, تحصيل مدفوعات Apple Pay مدى">
    <meta name="author" content="مؤسسة موقع أمر التجارية — منصة ثقة">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <link rel="canonical" href="{{ url('/providers') }}">
    <link rel="alternate" hreflang="ar-SA" href="{{ url('/providers') }}">
    <link rel="alternate" hreflang="x-default" href="{{ url('/providers') }}">
    <link rel="icon" type="image/png" href="{{ asset('images/image.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/image.png') }}">

    <!-- Open Graph / Social Sharing -->
    <meta property="og:type" content="website">
    <meta property="og:locale" content="ar_SA">
    <meta property="og:site_name" content="منصة ثقة">
    <meta property="og:title" content="بوابة مقدمي الخدمات — منصة ثقة | فواتير إلكترونية وتحصيل فوري">
    <meta property="og:description" content="أصدر فواتير إلكترونية احترافية لعملائك بخيارات الدفع Apple Pay، مدى، فيزا، تابي وتمارا، واستلم مستحقاتك في محفظتك البنكية بكل سهولة.">
    <meta property="og:url" content="{{ url('/providers') }}">
    <meta property="og:image" content="{{ asset('images/og-thiqa.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="بوابة مقدمي الخدمات — منصة ثقة">

    <!-- Twitter / X Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="بوابة مقدمي الخدمات — منصة ثقة">
    <meta name="twitter:description" content="نظام متكامل لمقدمي الخدمات لإصدار فواتير إلكترونية وتحصيل المدفوعات بالتقسيط والدفع الفوري في السعودية.">
    <meta name="twitter:image" content="{{ asset('images/og-thiqa.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])

    <!-- JSON-LD Structured Data for Google Rich Results -->
    @php
        $schema = [
            '@context' => 'https://schema.org',
            '@graph'   => [
                [
                    '@type'           => 'BreadcrumbList',
                    'itemListElement' => [
                        [
                            '@type'    => 'ListItem',
                            'position' => 1,
                            'name'     => 'منصة ثقة — الرئيسية',
                            'item'     => url('/'),
                        ],
                        [
                            '@type'    => 'ListItem',
                            'position' => 2,
                            'name'     => 'بوابة مقدمي الخدمات',
                            'item'     => url('/providers'),
                        ],
                    ],
                ],
                [
                    '@type'               => 'SoftwareApplication',
                    'name'                => 'منصة ثقة — بوابة مقدمي الخدمات',
                    'applicationCategory' => 'BusinessApplication',
                    'operatingSystem'     => 'Web',
                    'inLanguage'          => 'ar-SA',
                    'url'                 => url('/providers'),
                    'image'               => asset('images/og-thiqa.png'),
                    'description'         => 'منصة سعودية لإصدار الفواتير الإلكترونية وتحصيل المدفوعات لمقدمي الخدمات (الأجنحة الفندقية والشقق، منسقي المناسبات، المصورين، والخدمات الحرة).',
                    'provider'            => [
                        '@type' => 'Organization',
                        'name'  => 'منصة ثقة',
                        'url'   => url('/'),
                        'logo'  => asset('images/image.png'),
                    ],
                ],
                [
                    '@type'      => 'Service',
                    'name'       => 'إصدار الفواتير الإلكترونية وتحصيل المدفوعات لمقدمي الخدمات',
                    'serviceType'=> 'Electronic Invoicing and Payment Collection',
                    'areaServed' => [
                        '@type' => 'Country',
                        'name'  => 'Saudi Arabia',
                    ],
                    'provider'   => [
                        '@type' => 'Organization',
                        'name'  => 'منصة ثقة — مؤسسة موقع أمر التجارية',
                        'url'   => url('/'),
                    ],
                ],
                [
                    '@type'      => 'FAQPage',
                    'mainEntity' => [
                        [
                            '@type'          => 'Question',
                            'name'           => 'من يمكنه التسجيل في بوابة مقدمي الخدمات بمنصة ثقة؟',
                            'acceptedAnswer' => [
                                '@type' => 'Answer',
                                'text'  => 'يمكن لأصحاب الأجنحة الفندقية المخدومة والشقق والفلل والشاليهات، ومنسقي الحفلات والمناسبات، والمصورين، ومقدمي الخدمات الحرة الأخرى التسجيل وإصدار فواتيرهم الإلكترونية.',
                            ],
                        ],
                        [
                            '@type'          => 'Question',
                            'name'           => 'ما هي وسائل الدفع المتاحة لعملائي في الفواتير؟',
                            'acceptedAnswer' => [
                                '@type' => 'Answer',
                                'text'  => 'تدعم الفواتير الدفع الفوري عبر Apple Pay ومدى وفيزا وماستركارد، بالإضافة إلى التقسيط على 4 دفعات عبر تابي (Tabby) وتمارا (Tamara).',
                            ],
                        ],
                        [
                            '@type'          => 'Question',
                            'name'           => 'كيف أستلم مبالغ الفواتير المدفوعة؟',
                            'acceptedAnswer' => [
                                '@type' => 'Answer',
                                'text'  => 'فور سداد العميل وتأكيد الدفع، يُضاف صافي قيمة الفاتورة تلقائياً إلى رصيد محفظتك في المنصة، وتقوم الإدارة بتحويل الرصيد إلى حسابك البنكي وتصفير المحفظة بشكل دوري.',
                            ],
                        ],
                        [
                            '@type'          => 'Question',
                            'name'           => 'كيف يتم تفعيل خاصية إنشاء الفواتير؟',
                            'acceptedAnswer' => [
                                '@type' => 'Answer',
                                'text'  => 'بعد التسجيل ورفع الوثائق المطلوبة حسب نوع نشاطك، يتم تفعيل الاشتراك الشهري عبر الإدارة ليُفتح لك إصدار فواتير إلكترونية غير محدودة طوال فترة اشتراكك.',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>

    <style>
        * { font-family: 'Cairo', sans-serif; box-sizing: border-box; }
        .thiqa-emerald-bg {
            background: #064e3b;
            background-image:
                radial-gradient(circle at 50% 10%, #065f46 0%, transparent 60%),
                radial-gradient(circle at 90% 40%, #047857 0%, transparent 50%),
                radial-gradient(circle at 10% 85%, #022c22 0%, transparent 70%);
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 antialiased">
    <!-- Header matching Core Thiqa Emerald Identity -->
    <header class="sticky top-0 z-30 bg-[#064e3b]/95 backdrop-blur border-b border-emerald-400/20">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between gap-3">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 text-white no-underline">
                <div class="w-10 h-10 rounded-xl bg-white p-1 flex items-center justify-center shadow">
                    <img src="{{ asset('images/image.png') }}" alt="شعار منصة ثقة" class="w-full h-full object-contain">
                </div>
                <div>
                    <div class="text-sm sm:text-base font-black text-emerald-300 leading-tight">منصة ثقة</div>
                    <div class="text-[11px] text-emerald-100/85 font-bold">بوابة مقدمي الخدمات</div>
                </div>
            </a>
            <nav class="hidden md:flex items-center gap-5 text-xs font-bold text-emerald-100/85">
                <a href="{{ route('home') }}" class="hover:text-emerald-300 transition">الرئيسية</a>
                <a href="#sectors" class="hover:text-emerald-300 transition">القطاعات المدعومة</a>
                <a href="#faq" class="hover:text-emerald-300 transition">الأسئلة الشائعة</a>
                <a href="{{ route('verify.index') }}" class="hover:text-emerald-300 transition">التحقق من عقد</a>
            </nav>
            <div class="flex items-center gap-2">
                <a href="{{ route('login') }}" class="px-3.5 py-2 rounded-xl border border-emerald-300/30 text-emerald-50 hover:bg-emerald-800/60 text-xs sm:text-sm font-bold transition">
                    تسجيل الدخول
                </a>
                <a href="{{ route('provider.register') }}" class="px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 text-xs sm:text-sm font-black shadow transition">
                    سجّل كمقدم خدمة
                </a>
            </div>
        </div>
    </header>

    <main>
        <!-- Hero matching Core Thiqa Deep Emerald Green Identity -->
        <section class="thiqa-emerald-bg text-white py-14 sm:py-20 px-4">
            <div class="max-w-4xl mx-auto text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-white p-2.5 shadow-xl border border-emerald-400/30 mb-4">
                    <img src="{{ asset('images/image.png') }}" alt="منصة ثقة لتوثيق العقود وبوابة مقدمي الخدمات" class="w-full h-full object-contain">
                </div>
                <div>
                    <span class="inline-block px-3.5 py-1 rounded-full bg-emerald-500/15 border border-emerald-400/35 text-emerald-200 text-xs font-extrabold mb-4">
                        نظام الفواتير الإلكترونية وتحصيل المدفوعات في السعودية
                    </span>
                </div>
                <h1 class="text-2xl sm:text-4xl font-black leading-tight">
                    أصدر فواتيرك الإلكترونية باحترافية وقدّم لعملائك
                    <span class="text-emerald-300">Apple Pay ومدى وتابي وتمارا</span>
                </h1>
                <p class="text-sm sm:text-base text-emerald-100/85 max-w-2xl mx-auto mt-4 leading-relaxed">
                    بوابة مخصصة لمقدمي الخدمات ضمن منصة ثقة في المملكة العربية السعودية لإصدار فواتير إلكترونية مصممة حسب نشاطك، مع محفظة مالية فورية وتحويل مستحقاتك مباشرة لحسابك البنكي.
                </p>
                <div class="mt-7 flex flex-col sm:flex-row items-center justify-center gap-3">
                    <a href="{{ route('provider.register') }}"
                       class="w-full sm:w-auto px-7 py-3.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-sm sm:text-base shadow-lg transition">
                        ابدأ التسجيل كمقدم خدمة الآن
                    </a>
                    <a href="{{ route('login') }}"
                       class="w-full sm:w-auto px-7 py-3.5 rounded-xl bg-emerald-950/60 hover:bg-emerald-900/75 border border-emerald-400/35 text-emerald-50 font-extrabold text-sm sm:text-base transition">
                        لدي حساب — تسجيل الدخول
                    </a>
                </div>
            </div>
        </section>

        <!-- 4 Supported Service Categories -->
        <section id="sectors" class="max-w-6xl mx-auto px-4 py-14">
            <div class="text-center mb-10">
                <h2 class="text-xl sm:text-2xl font-black text-slate-900">فواتير مصممة خصيصاً لأربعة قطاعات رئيسية</h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">لكل نشاط نموذجه الخاص وبياناته التفصيلية وتصميم فاتورته الإلكترونية الاحترافي</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <article class="bg-white rounded-2xl border border-emerald-200/80 p-5 shadow-sm hover:border-emerald-500 transition">
                    <h3 class="text-base font-black text-emerald-950">الأجنحة الفندقية والشقق</h3>
                    <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">
                        نموذج مخصص للشقق والفلل والشاليهات والأجنحة الفندقية المخدومة يشمل بيانات المستأجر، رقم الدور والشقة، تواريخ الدخول والخروج، الليالي، والتأمين.
                    </p>
                </article>

                <article class="bg-white rounded-2xl border border-emerald-200/80 p-5 shadow-sm hover:border-emerald-500 transition">
                    <h3 class="text-base font-black text-emerald-950">منسقو الحفلات والمناسبات</h3>
                    <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">
                        نموذج طلب منسق متكامل لتسجيل تفاصيل المناسبة والموقع والتاريخ والوقت، وإرفاق صورة عرض السعر وتحصيل الدفعات بسهولة.
                    </p>
                </article>

                <article class="bg-white rounded-2xl border border-emerald-200/80 p-5 shadow-sm hover:border-emerald-500 transition">
                    <h3 class="text-base font-black text-emerald-950">المصورون الفوتوغرافيون</h3>
                    <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">
                        نموذج خاص بالمصورين يشمل نوع التصوير والباقة وموقع وتاريخ ووقت الحضور ومدة التصوير والمصور المسؤول وتفاصيل الدفعات.
                    </p>
                </article>

                <article class="bg-white rounded-2xl border border-emerald-200/80 p-5 shadow-sm hover:border-emerald-500 transition">
                    <h3 class="text-base font-black text-emerald-950">خدمات أخرى متنوعة</h3>
                    <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">
                        مرونة كاملة لأصحاب الخدمات الحرة والأنشطة التجارية الأخرى لتخصيص نوع الخدمة وإصدار فواتير إلكترونية فورية للعملاء.
                    </p>
                </article>
            </div>
        </section>

        <!-- FAQ Section (matches FAQPage JSON-LD 100%) -->
        <section id="faq" class="bg-white border-t border-slate-200 py-14 px-4">
            <div class="max-w-3xl mx-auto">
                <h2 class="text-xl font-black text-slate-900 text-center mb-8">الأسئلة الشائعة حول بوابة مقدمي الخدمات</h2>
                <div class="space-y-3">
                    <div class="rounded-2xl border border-slate-200 p-4 bg-slate-50">
                        <h3 class="text-sm font-black text-emerald-950">من يمكنه التسجيل في بوابة مقدمي الخدمات بمنصة ثقة؟</h3>
                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">يمكن لأصحاب الأجنحة الفندقية المخدومة والشقق والفلل والشاليهات، ومنسقي الحفلات والمناسبات، والمصورين، ومقدمي الخدمات الحرة الأخرى التسجيل وإصدار فواتيرهم الإلكترونية.</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 p-4 bg-slate-50">
                        <h3 class="text-sm font-black text-emerald-950">ما هي وسائل الدفع المتاحة لعملائي في الفواتير؟</h3>
                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">تدعم الفواتير الدفع الفوري عبر Apple Pay ومدى وفيزا وماستركارد، بالإضافة إلى التقسيط على 4 دفعات عبر تابي (Tabby) وتمارا (Tamara).</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 p-4 bg-slate-50">
                        <h3 class="text-sm font-black text-emerald-950">كيف أستلم مبالغ الفواتير المدفوعة؟</h3>
                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">فور سداد العميل وتأكيد الدفع، يُضاف صافي قيمة الفاتورة تلقائياً إلى رصيد محفظتك في المنصة، وتقوم الإدارة بتحويل الرصيد إلى حسابك البنكي وتصفير المحفظة بشكل دوري.</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 p-4 bg-slate-50">
                        <h3 class="text-sm font-black text-emerald-950">كيف يتم تفعيل خاصية إنشاء الفواتير؟</h3>
                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">بعد التسجيل ورفع الوثائق المطلوبة حسب نوع نشاطك، يتم تفعيل الاشتراك الشهري عبر الإدارة ليُفتح لك إصدار فواتير إلكترونية غير محدودة طوال فترة اشتراكك.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="bg-[#022c22] text-emerald-200/75 text-center py-7 px-4 text-xs border-t border-emerald-400/20">
        <div class="font-extrabold text-white mb-2">منصة ثقة — مؤسسة موقع أمر التجارية</div>
        <div class="flex flex-wrap items-center justify-center gap-4 mb-3 text-emerald-300 font-bold">
            <a href="{{ route('home') }}" class="hover:text-white">الرئيسية</a>
            <a href="{{ route('login') }}" class="hover:text-white">تسجيل الدخول</a>
            <a href="{{ route('provider.register') }}" class="hover:text-white">التسجيل كمقدم خدمة</a>
            <a href="/terms" class="hover:text-white">الشروط والأحكام</a>
            <a href="/privacy" class="hover:text-white">سياسة الخصوصية</a>
            <a href="/refund-policy" class="hover:text-white">سياسة الاسترجاع</a>
        </div>
        <div>جميع الحقوق محفوظة &copy; {{ date('Y') }}</div>
    </footer>
</body>
</html>
