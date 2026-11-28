<x-app-layout>
    <x-slot name="header">ملف مقدم الخدمة: {{ $provider->business_name }}</x-slot>

    <div class="space-y-6">
        <!-- Top Header & Account Toggle -->
        <div class="card flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h2 class="text-lg font-black text-slate-900">{{ $provider->business_name }}</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $provider->serviceTypeEnum()->badgeClasses() }}">
                        {{ $provider->serviceTypeEnum()->icon() }} {{ $provider->service_type_label }}
                    </span>
                    @if($provider->property_kind)
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">{{ $provider->property_kind }}</span>
                    @endif
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $provider->hasActiveSubscription() ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                        {{ $provider->subscriptionStatusLabel() }}
                    </span>
                </div>
                <p class="text-xs text-slate-600 mt-1.5">
                    صاحب النشاط: <strong>{{ $provider->name }}</strong>·
                    الجوال: <strong dir="ltr">{{ $provider->phone }}</strong>·
                    البريد: <strong dir="ltr">{{ $provider->email }}</strong>
                    @if($provider->city) · المدينة: <strong>{{ $provider->city }}</strong>@endif
                </p>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('admin.providers.index') }}" class="btn btn-ghost btn-sm">← القائمة</a>
                <form method="POST" action="{{ route('admin.providers.toggle-active', $provider) }}">
                    @csrf
                    <button type="submit" class="btn {{ $provider->is_active ? 'btn-rose' : 'btn-emerald' }} btn-sm">
                        {{ $provider->is_active ? 'إيقاف الحساب' : 'إعادة تفعيل الحساب' }}
                    </button>
                </form>
            </div>
        </div>

        <!-- Service Description + KYC Uploaded Documents + Bank Details -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <div class="lg:col-span-2 card space-y-4">
                <div>
                    <h3 class="text-sm font-black text-slate-900 mb-1.5">تفاصيل الخدمة المقدمة</h3>
                    <p class="text-xs sm:text-sm text-slate-700 bg-slate-50 border border-slate-100 rounded-xl p-3 leading-relaxed">
                        {{ $provider->service_description ?: 'لا يوجد وصف مضاف.' }}
                    </p>
                    <div class="flex flex-wrap gap-4 mt-2.5 text-xs text-slate-600">
                        @if($provider->national_id_number)
                            <span>رقم الهوية: <strong class="font-mono">{{ $provider->national_id_number }}</strong></span>
                        @endif
                        @if($provider->commercial_register)
                            <span>السجل / وثيقة العمل الحر: <strong class="font-mono">{{ $provider->commercial_register }}</strong></span>
                        @endif
                    </div>
                </div>

                <!-- Uploaded KYC Documents -->
                <div class="pt-3 border-t border-slate-100">
                    <h3 class="text-sm font-black text-slate-900 mb-2.5">الوثائق والمستندات المرفوعة للتحقق</h3>
                    @php $docs = $provider->uploadedDocuments(); @endphp
                    @if(empty($docs))
                        <p class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-xl p-3 font-bold">
                            لم يتم رفع وثائق مرفقة بعد.
                        </p>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($docs as $docKey =>$docInfo)
                                @php $docUrl = route('admin.providers.document', [$provider, $docKey]); @endphp
                                <div class="rounded-xl border border-slate-200 bg-slate-50 overflow-hidden flex flex-col">
                                    @if(!empty($docInfo['is_image']))
                                        <a href="{{ $docUrl }}" target="_blank" class="block bg-slate-900/5 h-36 overflow-hidden">
                                            <img src="{{ $docUrl }}" alt="{{ $docInfo['label'] }}" loading="lazy"
                                                 class="w-full h-full object-cover hover:scale-105 transition duration-200">
                                        </a>
                                    @else
                                        <a href="{{ $docUrl }}" target="_blank"
                                           class="h-28 bg-gradient-to-br from-rose-50 to-amber-50 flex flex-col items-center justify-center gap-1 border-b border-slate-200 hover:bg-rose-100/60 transition">
                                            <span class="text-[11px] font-black text-rose-800">ملف PDF جاهز للعرض</span>
                                        </a>
                                    @endif
                                    <div class="p-3 flex items-center justify-between gap-2 bg-white">
                                        <span class="text-xs font-extrabold text-slate-800 truncate">{{ $docInfo['label'] }}</span>
                                        <div class="flex items-center gap-1.5 shrink-0">
                                            <a href="{{ $docUrl }}" target="_blank"
                                               class="px-2.5 py-1 rounded-lg bg-sky-50 text-sky-800 hover:bg-sky-100 text-[11px] font-bold">
                                                فتح ↗
                                            </a>
                                            <a href="{{ $docUrl }}?download=1"
                                               class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 text-[11px] font-bold">
                                                تحميل 
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Bank Info Card -->
            <div class="card">
                <h3 class="text-sm font-black text-slate-900 mb-3">بيانات الحساب البنكي للتحويل</h3>
                <div class="space-y-2 text-xs">
                    <div class="p-2.5 rounded-lg bg-slate-50">
                        <span class="text-slate-400 block">اسم البنك</span>
                        <strong class="text-slate-900">{{ $provider->bank_name ?: 'غير محدد' }}</strong>
                    </div>
                    <div class="p-2.5 rounded-lg bg-slate-50">
                        <span class="text-slate-400 block">اسم صاحب الحساب</span>
                        <strong class="text-slate-900">{{ $provider->bank_account_name ?: 'غير محدد' }}</strong>
                    </div>
                    <div class="p-2.5 rounded-lg bg-slate-50">
                        <span class="text-slate-400 block">رقم الآيبان (IBAN)</span>
                        <div class="flex items-center justify-between gap-2 mt-0.5">
                            <strong class="font-mono text-indigo-800 break-all" dir="ltr">{{ $provider->iban ?: 'غير محدد' }}</strong>
                            @if($provider->iban)
                                <button type="button" onclick="navigator.clipboard.writeText('{{ $provider->iban }}').then(() =>alert('تم نسخ رقم الآيبان!'))" class="btn btn-ghost btn-sm">نسخ</button>
                            @endif
                        </div>
                    </div>
                    @if($provider->bank_owner_confirmed)
                        <div class="p-2 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 font-bold text-[11px]">
                            أقرّ مقدم الخدمة أن الحساب البنكي بنفس اسم صاحب النشاط.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Subscription Management + Wallet Settlement -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <!-- Monthly Subscription Control -->
            <div class="card border-emerald-200">
                <h3 class="text-sm font-black text-slate-900 mb-3">إدارة الاشتراك الشهري (تفعيل إصدار الفواتير)</h3>
                <div class="p-3 rounded-xl mb-4 {{ $provider->hasActiveSubscription() ? 'bg-emerald-50 border border-emerald-200' : 'bg-amber-50 border border-amber-200' }} text-xs">
                    <div>الحالة الحالية: <strong>{{ $provider->subscriptionStatusLabel() }}</strong></div>
                    @if($provider->subscription_ends_at)
                        <div class="mt-0.5">ينتهي في: <strong>{{ $provider->subscription_ends_at->format('Y-m-d H:i') }}</strong>
                            @if($provider->hasActiveSubscription()) (متبقي {{ $provider->subscriptionDaysRemaining() }} يوم) @endif
                        </div>
                    @endif
                </div>

                <form method="POST" action="{{ route('admin.providers.subscription.activate', $provider) }}" class="space-y-3">
                    @csrf
                    @php
                        $planPrices = [
                            1  => (float) ($subConfig['plans'][1]['price'] ?? 199),
                            3  => (float) ($subConfig['plans'][3]['price'] ?? 549),
                            6  => (float) ($subConfig['plans'][6]['price'] ?? 999),
                            12 => (float) ($subConfig['plans'][12]['price'] ?? 1799),
                        ];
                    @endphp
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="form-label">مدة الاشتراك</label>
                            <select name="duration_months" id="admin_sub_duration" class="form-input"
                                    onchange="const m={1:{{ $planPrices[1] }},3:{{ $planPrices[3] }},6:{{ $planPrices[6] }},12:{{ $planPrices[12] }}}; const el=document.getElementById('admin_sub_amount'); if(el && m[this.value]!==undefined) el.value=m[this.value].toFixed(2);">
                                <option value="1">شهر واحد ({{ number_format($planPrices[1], 2) }} ر.س)</option>
                                <option value="3">3 أشهر ({{ number_format($planPrices[3], 2) }} ر.س)</option>
                                <option value="6">6 أشهر ({{ number_format($planPrices[6], 2) }} ر.س)</option>
                                <option value="12">12 شهراً ({{ number_format($planPrices[12], 2) }} ر.س)</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">قيمة الاشتراك (ر.س)</label>
                            <input type="number" step="0.01" min="0" id="admin_sub_amount" name="amount" value="{{ number_format($planPrices[1], 2, '.', '') }}" class="form-input font-bold" dir="ltr">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="form-label">مرجع السداد (اختياري)</label>
                            <input type="text" name="payment_reference" class="form-input" placeholder="رقم الحوالة">
                        </div>
                        <div>
                            <label class="form-label">ملاحظة (اختياري)</label>
                            <input type="text" name="notes" class="form-input" placeholder="ملاحظة إدارية">
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 pt-1">
                        <button type="submit" class="btn btn-emerald">
                            {{ $provider->hasActiveSubscription() ? 'تجديد / تمديد الاشتراك' : 'تفعيل الاشتراك الشهري الآن' }}
                        </button>
                    </div>
                </form>

                @if($provider->hasActiveSubscription())
                    <form method="POST" action="{{ route('admin.providers.subscription.suspend', $provider) }}" class="mt-3 pt-3 border-t border-slate-100"
                          onsubmit="return confirm('هل أنت متأكد من إيقاف الاشتراك؟ لن يتمكن مقدم الخدمة من إنشاء فواتير جديدة.')">
                        @csrf
                        <button type="submit" class="btn btn-rose btn-sm">إيقاف الاشتراك فوراً</button>
                    </form>
                @endif
            </div>

            <!-- Wallet Settlement & Zeroing -->
            <div class="card border-indigo-200">
                <h3 class="text-sm font-black text-slate-900 mb-3">محفظة مقدم الخدمة والتحويل والتصفير</h3>
                <div class="grid grid-cols-3 gap-2.5 mb-4 text-center">
                    <div class="p-3 rounded-xl bg-indigo-50 border border-indigo-200">
                        <div class="text-[11px] font-bold text-indigo-700">الرصيد الحالي</div>
                        <div class="text-lg font-black text-indigo-900 mt-0.5">{{ number_format((float) $provider->wallet_balance, 2) }}</div>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                        <div class="text-[11px] font-bold text-slate-500">إجمالي الأرباح</div>
                        <div class="text-lg font-black text-slate-800 mt-0.5">{{ number_format((float) $provider->total_earned, 2) }}</div>
                    </div>
                    <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200">
                        <div class="text-[11px] font-bold text-emerald-700">تم تحويله</div>
                        <div class="text-lg font-black text-emerald-800 mt-0.5">{{ number_format((float) $provider->total_settled, 2) }}</div>
                    </div>
                </div>

                @if((float) $provider->wallet_balance >0)
                    <form method="POST" action="{{ route('admin.providers.wallet.settle', $provider) }}" class="space-y-3"
                          onsubmit="return confirm('هل تم تحويل المبلغ إلى الحساب البنكي وتريد تصفير/خصم الرصيد من المحفظة؟')">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="form-label">طريقة التسوية</label>
                                <select name="settle_mode" id="settle_mode" class="form-input" onchange="document.getElementById('partial-amt-box').classList.toggle('hidden', this.value !== 'partial')">
                                    <option value="full">تحويل كامل الرصيد وتصفير المحفظة ({{ number_format((float) $provider->wallet_balance, 2) }} ر.س)</option>
                                    <option value="partial">تحويل مبلغ جزئي محدد</option>
                                </select>
                            </div>
                            <div id="partial-amt-box" class="hidden">
                                <label class="form-label">المبلغ المحوّل (ر.س)</label>
                                <input type="number" step="0.01" min="0.01" max="{{ (float) $provider->wallet_balance }}" name="amount" class="form-input" dir="ltr">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="form-label">رقم مرجع الحوالة البنكية</label>
                                <input type="text" name="reference" class="form-input" placeholder="مثال: TRF-20261128">
                            </div>
                            <div>
                                <label class="form-label">ملاحظة التسوية</label>
                                <input type="text" name="notes" class="form-input" placeholder="تسوية نهاية اليوم">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-indigo w-full">
                            تأكيد التحويل وتصفير المحفظة الآن
                        </button>
                    </form>
                @else
                    <div class="p-4 rounded-xl bg-slate-50 text-center text-xs font-bold text-slate-500">
                        رصيد المحفظة مصفّر حالياً (0.00 ر.س)
                    </div>
                @endif
            </div>
        </div>

        <!-- Admin-Only Provider Details Editor -->
        <details class="card">
            <summary class="text-sm font-black text-slate-900 cursor-pointer select-none">
                تعديل بيانات مقدم الخدمة أو نوع خدمته أو حسابه البنكي (صلاحية الإدارة فقط)
            </summary>
            <form method="POST" action="{{ route('admin.providers.update', $provider) }}" class="mt-4 pt-4 border-t border-slate-100 space-y-3.5">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="form-label">اسم صاحب النشاط</label>
                        <input type="text" name="name" value="{{ old('name', $provider->name) }}" required class="form-input">
                    </div>
                    <div>
                        <label class="form-label">اسم المنشأة</label>
                        <input type="text" name="business_name" value="{{ old('business_name', $provider->business_name) }}" required class="form-input">
                    </div>
                    <div>
                        <label class="form-label">نوع الخدمة</label>
                        <select name="service_type" class="form-input">
                            @foreach($serviceTypes as $t)
                                <option value="{{ $t['value'] }}" {{ old('service_type', $provider->service_type) === $t['value'] ? 'selected' : '' }}>{{ $t['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">نوع العقار (إن وجد)</label>
                        <input type="text" name="property_kind" value="{{ old('property_kind', $provider->property_kind) }}" class="form-input" placeholder="شقة / فيلا / شاليه / جناح فندقي">
                    </div>
                    <div>
                        <label class="form-label">اسم الخدمة المخصصة (لأخرى)</label>
                        <input type="text" name="custom_service_name" value="{{ old('custom_service_name', $provider->custom_service_name) }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">المدينة</label>
                        <input type="text" name="city" value="{{ old('city', $provider->city) }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">رقم الهوية</label>
                        <input type="text" name="national_id_number" value="{{ old('national_id_number', $provider->national_id_number) }}" class="form-input" dir="ltr">
                    </div>
                    <div>
                        <label class="form-label">رقم السجل / وثيقة العمل الحر</label>
                        <input type="text" name="commercial_register" value="{{ old('commercial_register', $provider->commercial_register) }}" class="form-input" dir="ltr">
                    </div>
                    <div>
                        <label class="form-label">اسم البنك</label>
                        <input type="text" name="bank_name" value="{{ old('bank_name', $provider->bank_name) }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">اسم صاحب الحساب البنكي</label>
                        <input type="text" name="bank_account_name" value="{{ old('bank_account_name', $provider->bank_account_name) }}" class="form-input">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="form-label">رقم الآيبان (IBAN)</label>
                        <input type="text" name="iban" value="{{ old('iban', $provider->iban) }}" class="form-input font-mono" dir="ltr">
                    </div>
                </div>
                <div>
                    <label class="form-label">وصف الخدمة</label>
                    <textarea name="service_description" rows="2" required class="form-input">{{ old('service_description', $provider->service_description) }}</textarea>
                </div>
                <button type="submit" class="btn btn-indigo btn-sm">حفظ التعديلات الإدارية</button>
            </form>
        </details>

        <!-- Provider Invoices -->
        <div class="card">
            <h3 class="text-sm font-black text-slate-900 mb-3">فواتير {{ $provider->business_name }}</h3>
            @if($invoices->isEmpty())
                <p class="text-center py-6 text-xs text-slate-400">لم ينشئ مقدم الخدمة أي فواتير بعد.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>رقم الفاتورة</th>
                                <th>العميل</th>
                                <th>العنوان</th>
                                <th>المبلغ الصافي</th>
                                <th>الحالة</th>
                                <th>المحفظة</th>
                                <th>التاريخ</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoices as $inv)
                                <tr>
                                    <td class="font-mono text-xs font-bold text-indigo-700">{{ $inv->invoice_number }}</td>
                                    <td>{{ $inv->customer_name }}</td>
                                    <td>{{ $inv->title }}</td>
                                    <td class="font-black">{{ number_format((float) $inv->amount, 2) }} ر.س</td>
                                    <td>
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $inv->isPaid() ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                            {{ $inv->statusLabel() }}
                                        </span>
                                    </td>
                                    <td class="text-xs font-bold">{{ $inv->isWalletCredited() ? 'أُضيفت' : '—' }}</td>
                                    <td class="text-xs text-slate-500">{{ $inv->created_at->format('Y-m-d') }}</td>
                                    <td><a href="{{ route('admin.providers.invoices.show', $inv) }}" class="btn btn-ghost btn-sm">تفاصيل</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $invoices->links() }}</div>
            @endif
        </div>

        <!-- Wallet Transactions & Subscription History -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <div class="card">
                <h3 class="text-sm font-black text-slate-900 mb-3">سجل حركات المحفظة</h3>
                @if($transactions->isEmpty())
                    <p class="text-xs text-slate-400 text-center py-5">لا توجد حركات بعد.</p>
                @else
                    <div class="space-y-2 text-xs">
                        @foreach($transactions as $tx)
                            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between gap-2">
                                <div>
                                    <div class="font-extrabold {{ $tx->type === 'credit' ? 'text-emerald-700' : 'text-indigo-700' }}">{{ $tx->typeLabel() }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $tx->reference }} · {{ $tx->notes }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $tx->created_at->format('Y-m-d H:i') }}</div>
                                </div>
                                <div class="text-left shrink-0">
                                    <div class="font-black {{ $tx->type === 'credit' ? 'text-emerald-700' : 'text-rose-700' }}" dir="ltr">
                                        {{ $tx->type === 'credit' ? '+' : '-' }}{{ number_format((float) $tx->amount, 2) }} ر.س
                                    </div>
                                    <div class="text-[10.5px] text-slate-500">الرصيد: {{ number_format((float) $tx->balance_after, 2) }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="card">
                <h3 class="text-sm font-black text-slate-900 mb-3">سجل الاشتراكات</h3>
                @if($subscriptions->isEmpty())
                    <p class="text-xs text-slate-400 text-center py-5">لم يتم تفعيل أي اشتراك بعد.</p>
                @else
                    <div class="space-y-2 text-xs">
                        @foreach($subscriptions as $sub)
                            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between gap-2">
                                <div>
                                    <div class="font-extrabold text-slate-900">{{ $sub->plan_name }}</div>
                                    <div class="text-[11px] text-slate-500">
                                        {{ $sub->starts_at->format('Y-m-d') }} ← {{ $sub->ends_at->format('Y-m-d') }}
                                        @if($sub->activatedByAdmin) · بواسطة: {{ $sub->activatedByAdmin->name }} @endif
                                    </div>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold {{ $sub->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600' }}">
                                    {{ $sub->status === 'active' ? 'نشط' : 'منتهي/ملغي' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
