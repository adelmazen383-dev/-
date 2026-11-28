<x-app-layout>
    <x-slot name="header">فاتورة مقدم خدمة: {{ $invoice->invoice_number }}</x-slot>

    <div class="space-y-5">
        <div class="card flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="font-mono text-base font-black text-indigo-700">{{ $invoice->invoice_number }}</span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $invoice->isPaid() ? 'bg-emerald-100 text-emerald-800' : ($invoice->status === 'failed' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }}">
                        {{ $invoice->statusLabel() }}
                    </span>
                    @if($invoice->isWalletCredited())
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800">
                            أُضيفت لمحفظة مقدم الخدمة ({{ number_format((float) $invoice->amount, 2) }} ر.س)
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-600 mt-1">
                    مقدم الخدمة: <a href="{{ route('admin.providers.show', $invoice->provider) }}" class="font-extrabold text-indigo-700 hover:underline">{{ $invoice->provider->business_name }}</a>
                    · نوع الخدمة: <strong>{{ $invoice->provider->service_type_label }}</strong>
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.providers.invoices.index') }}" class="btn btn-ghost btn-sm">← كل الفواتير</a>
                <a href="{{ $invoice->public_payment_url }}" target="_blank" class="btn btn-ghost btn-sm">رابط العميل ↗</a>

                @if(!$invoice->isPaid())
                    @if($invoice->telr_order_ref || $invoice->tamara_order_id)
                        <form method="POST" action="{{ route('admin.providers.invoices.check-status', $invoice) }}" class="inline">
                            @csrf
                            <button type="submit" class="btn btn-indigo btn-sm">التحقق من البوابة</button>
                        </form>
                    @endif

                    <form method="POST" action="{{ route('admin.providers.invoices.confirm-paid', $invoice) }}" class="inline"
                          onsubmit="return confirm('هل تأكدت من سداد الفاتورة وتريد تأكيدها وإضافة {{ number_format((float) $invoice->amount, 2) }} ر.س إلى محفظة مقدم الخدمة؟')">
                        @csrf
                        <button type="submit" class="btn btn-emerald btn-sm">تأكيد السداد وإضافة للمحفظة</button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Financial & Gateway Breakdown -->
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div class="card py-3.5">
                <div class="text-xs text-slate-500 font-bold">صافي مقدم الخدمة (للمحفظة)</div>
                <div class="text-xl font-black text-emerald-700 mt-1">{{ number_format((float) $invoice->amount, 2) }} ر.س</div>
            </div>
            <div class="card py-3.5">
                <div class="text-xs text-slate-500 font-bold">رسوم البوابة</div>
                <div class="text-xl font-black text-slate-800 mt-1">{{ number_format((float) $invoice->gateway_fee_amount, 2) }} ر.س</div>
                <div class="text-[11px] text-slate-400">({{ $invoice->gateway_fee_percentage }}%)</div>
            </div>
            <div class="card py-3.5">
                <div class="text-xs text-slate-500 font-bold">إجمالي ما يدفعه العميل</div>
                <div class="text-xl font-black text-indigo-700 mt-1">{{ number_format((float) $invoice->total_payable, 2) }} ر.س</div>
            </div>
            <div class="card py-3.5">
                <div class="text-xs text-slate-500 font-bold">وسيلة الدفع / المرجع</div>
                <div class="text-sm font-black text-slate-800 mt-1">{{ $invoice->paymentMethodCleanLabel() }}</div>
                <div class="text-[11px] font-mono text-slate-500 truncate">{{ $invoice->tamara_order_id ?: ($invoice->telr_order_ref ?: '—') }}</div>
            </div>
        </div>

        <!-- Invoice Template Preview -->
        <div class="max-w-3xl mx-auto">
            @include($invoice->templateView(), [
                'invoice' =>$invoice,
                'provider' =>$invoice->provider,
                'attachmentUrl' =>$invoice->quote_attachment_path ? route('admin.providers.invoices.attachment', $invoice) : null,
            ])
        </div>
    </div>
</x-app-layout>
