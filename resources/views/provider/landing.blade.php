<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">
    <meta name="theme-color" content="#141311">

    <title>بوابة مقدمي الخدمات — منصة ثقة | إصدار فواتير إلكترونية وتحصيل مدفوعات في السعودية</title>
    <meta name="description" content="انضم إلى بوابة مقدمي الخدمات في منصة ثقة لإصدار فواتير إلكترونية فاخرة وتحصيل مدفوعات عملائك عبر Apple Pay ومدى وفيزا وتابي وتمارا (4 دفعات) لمقدمي الشقق والأجنحة الفندقية ومنسقي الحفلات والمصورين والخدمات الحرة في السعودية.">
    <meta name="keywords" content="منصة ثقة, بوابة مقدمي الخدمات, فواتير إلكترونية السعودية, رابط دفع تابي, رابط دفع تمارا, فواتير منسقي حفلات, فواتير مصورين, فواتير شقق فندقية مخدومة, تحصيل مدفوعات Apple Pay مدى">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <link rel="canonical" href="{{ url('/providers') }}">

    <!-- Open Graph / Social Sharing -->
    <meta property="og:type" content="website">
    <meta property="og:locale" content="ar_SA">
    <meta property="og:site_name" content="منصة ثقة">
    <meta property="og:title" content="بوابة مقدمي الخدمات — منصة ثقة | فواتير إلكترونية وتحصيل فوري">
    <meta property="og:description" content="أصدر فواتير إلكترونية احترافية لعملائك بخيارات الدفع Apple Pay، مدى، فيزا، تابي وتمارا، واستلم مستحقاتك في محفظتك البنكية بكل سهولة.">
    <meta property="og:url" content="{{ url('/providers') }}">
    <meta property="og:image" content="{{ asset('images/thiqa-gold-logo.png') }}">

    <!-- Twitter / X Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="بوابة مقدمي الخدمات — منصة ثقة">
    <meta name="twitter:description" content="نظام مستقل لمقدمي الخدمات لإصدار فواتير إلكترونية وتحصيل المدفوعات بالتقسيط والدفع الفوري في السعودية.">
    <meta name="twitter:image" content="{{ asset('images/thiqa-gold-logo.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])

    <!-- JSON-LD Structured Data for Google Rich Results -->
    @php
        $schema = [
            '@context' => 'https://schema.org',
            '@graph'   => [
                [
                    '@type'               => 'SoftwareApplication',
                    'name'                => 'منصة ثقة — بوابة مقدمي الخدمات',
                    'applicationCategory' => 'BusinessApplication',
                    'operatingSystem'     => 'Web',
                    'inLanguage'          => 'ar-SA',
                    'url'                 => url('/providers'),
                    'description'         => 'منصة سعودية لإصدار الفواتير الإلكترونية وتحصيل المدفوعات لمقدمي الخدمات (الأجنحة الفندقية والشقق، منسقي المناسبات، المصورين، والخدمات الحرة).',
                    'provider'            => [
                        '@type' => 'Organization',
                        'name'  => 'منصة ثقة',
                        'url'   => url('/'),
                        'logo'  => asset('images/thiqa-gold-logo.png'),
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
    </style>
</head>
<body class="bg-slate-50 text-slate-900 antialiased">
    <!-- Header -->
    <header class="sticky top-0 z-30 bg-[#141311]/95 backdrop-blur border-b border-[#c8a464]/25">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between gap-3">
            <a href="{{ route('provider.landing') }}" class="flex items-center gap-2.5 text-white no-underline">
                <img src="{{ asset('images/thiqa-gold-logo.png') }}" alt="شعار منصة ثقة الذهبي" class="w-10 h-10 object-contain">
                <div>
                    <div class="text-sm sm:text-base font-black leading-tight">منصة ثقة | THIQA</div>
                    <div class="text-[11px] text-[#c8a464] font-bold">بوابة مقدمي الخدمات</div>
                </div>
            </a>
            <div class="flex items-center gap-2">
                <a href="{{ route('provider.login') }}" class="px-3.5 py-2 rounded-xl border border-white/20 text-white hover:bg-white/10 text-xs sm:text-sm font-bold transition">
                    تسجيل الدخول
                </a>
                <a href="{{ route('provider.register') }}" class="px-4 py-2 rounded-xl bg-[#c8a464] hover:bg-[#b89250] text-[#141311] text-xs sm:text-sm font-black transition">
                    سجّل كمقدم خدمة
                </a>
            </div>
        </div>
    </header>

    <main>
        <!-- Hero -->
        <section class="bg-gradient-to-b from-[#141311] via-[#1c1a16] to-slate-900 text-white py-14 sm:py-20 px-4">
            <div class="max-w-4xl mx-auto text-center">
                <img src="{{ asset('images/thiqa-gold-logo.png') }}" alt="منصة ثقة" class="w-20 h-20 mx-auto mb-4 object-contain">
                <span class="inline-block px-3.5 py-1 rounded-full bg-[#c8a464]/15 border border-[#c8a464]/40 text-[#e5cfa0] text-xs font-extrabold mb-4">
                    ✨ نظام الفواتير الإلكترونية وتحصيل المدفوعات في السعودية
                </span>
                <h1 class="text-2xl sm:text-4xl font-black leading-tight">
                    أصدر فواتيرك الإلكترونية باحترافية وقدّم لعملائك
                    <span class="text-[#c8a464]">Apple Pay ومدى وتابي وتمارا</span>
                </h1>
                <p class="text-sm sm:text-base text-slate-300 max-w-2xl mx-auto mt-4 leading-relaxed">
                    بوابة مستقلة ومخصصة لمقدمي الخدمات في المملكة العربية السعودية لإصدار فواتير إلكترونية مصممة حسب نشاطك، مع محفظة مالية فورية وتحويل مستحقاتك مباشرة لحسابك البنكي.
                </p>
                <div class="mt-7 flex flex-col sm:flex-row items-center justify-center gap-3">
                    <a href="{{ route('provider.register') }}"
                       class="w-full sm:w-auto px-7 py-3.5 rounded-xl bg-[#c8a464] hover:bg-[#b89250] text-[#141311] font-black text-sm sm:text-base shadow-lg transition">
                        ابدأ التسجيل كمقدم خدمة الآن
                    </a>
                    <a href="{{ route('provider.login') }}"
                       class="w-full sm:w-auto px-7 py-3.5 rounded-xl bg-white/10 hover:bg-white/15 border border-white/20 text-white font-extrabold text-sm sm:text-base transition">
                        لدي حساب — تسجيل الدخول
                    </a>
                </div>
            </div>
        </section>

        <!-- 4 Supported Service Categories -->
        <section class="max-w-6xl mx-auto px-4 py-14">
            <div class="text-center mb-10">
                <h2 class="text-xl sm:text-2xl font-black text-slate-900">فواتير مصممة خصيصاً لأربعة قطاعات رئيسية</h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">لكل نشاط نموذجه الخاص وبياناته التفصيلية وتصميم فاتورته الإلكترونية الاحترافي</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <article class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                    <div class="text-3xl mb-2">🏨</div>
                    <h3 class="text-base font-black text-slate-900">الأجنحة الفندقية والشقق</h3>
                    <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">
                        نموذج مخصص للشقق والفلل والشاليهات والأجنحة الفندقية المخدومة يشمل بيانات المستأجر، رقم الدور والشقة، تواريخ الدخول والخروج، الليالي، والتأمين.
                    </p>
                </article>

                <article class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                    <div class="text-3xl mb-2">🎀</div>
                    <h3 class="text-base font-black text-slate-900">منسقو الحفلات والمناسبات</h3>
                    <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">
                        نموذج طلب منسق متكامل لتسجيل تفاصيل المناسبة والموقع والتاريخ والوقت، وإرفاق صورة عرض السعر وتحصيل الدفعات بسهولة.
                    </p>
                </article>

                <article class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                    <div class="text-3xl mb-2">📸</div>
                    <h3 class="text-base font-black text-slate-900">المصورون الفوتوغرافيون</h3>
                    <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">
                        نموذج خاص بالمصورين يشمل نوع التصوير والباقة وموقع وتاريخ ووقت الحضور ومدة التصوير والمصور المسؤول وتفاصيل الدفعات.
                    </p>
                </article>

                <article class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                    <div class="text-3xl mb-2">✨</div>
                    <h3 class="text-base font-black text-slate-900">خدمات أخرى متنوعة</h3>
                    <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">
                        مرونة كاملة لأصحاب الخدمات الحرة والأنشطة التجارية الأخرى لتخصيص نوع الخدمة وإصدار فواتير إلكترونية فورية للعملاء.
                    </p>
                </article>
            </div>
        </section>

        <!-- FAQ Section -->
        <section class="bg-white border-t border-slate-200 py-14 px-4">
            <div class="max-w-3xl mx-auto">
                <h2 class="text-xl font-black text-slate-900 text-center mb-8">الأسئلة الشائعة حول بوابة مقدمي الخدمات</h2>
                <div class="space-y-3">
                    <div class="rounded-2xl border border-slate-200 p-4 bg-slate-50">
                        <h3 class="text-sm font-black text-slate-900">كيف أستلم مبالغ الفواتير المدفوعة؟</h3>
                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">بمجرد سداد العميل عبر الرابط الإلكتروني وتأكيد العملية، يُضاف صافي مبلغ الفاتورة فوراً إلى رصيد محفظتك، وتقوم الإدارة بتحويل الرصيد إلى حسابك البنكي المسجل وتصفير المحفظة.</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 p-4 bg-slate-50">
                        <h3 class="text-sm font-black text-slate-900">هل يدعم النظام التقسيط عبر تابي وتمارا؟</h3>
                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">نعم، يستطيع عملاؤك السداد عبر Apple Pay ومدى وفيزا وماستركارد، أو تقسيم المبلغ على 4 دفعات عبر تابي (Tabby) وتمارا (Tamara).</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 p-4 bg-slate-50">
                        <h3 class="text-sm font-black text-slate-900">كيف يتم تفعيل إنشاء الفواتير بعد التسجيل؟</h3>
                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">بعد تسجيلك ورفع الوثائق المطلوبة، تتواصل مع الإدارة لتفعيل الاشتراك الشهري، وبمجرد التفعيل يُفتح لك إصدار الفواتير الإلكترونية فوراً.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="bg-[#141311] text-slate-400 text-center py-6 px-4 text-xs border-t border-[#c8a464]/20">
        <div class="font-extrabold text-white mb-1">منصة ثقة | THIQA — بوابة مقدمي الخدمات</div>
        <div>جميع الحقوق محفوظة &copy; {{ date('Y') }}</div>
    </footer>
</body>
</html>
