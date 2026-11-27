@extends('layouts.provider', ['title' => 'فاتورة ' . $invoice->invoice_number, 'header' => 'تفاصيل الفاتورة ' . $invoice->invoice_number])

@section('content')
    <div class="space-y-5">
        <!-- Action Bar -->
        <div class="p-card flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="font-mono text-sm font-black text-indigo-700">{{ $invoice->invoice_number }}</span>
                    @if($invoice->isPaid())
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">✅ مدفوعة ومؤكدة</span>
                    @elseif($invoice->status === 'failed')
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800">❌ فشلت العملية</span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">⏳ بانتظار سداد العميل</span>
                    @endif
                    @if($invoice->isWalletCredited())
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800">💰 أُضيفت لمحفظتك ({{ number_format((float) $invoice->amount, 2) }} ر.س)</span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 mt-1">تاريخ الإصدار: {{ $invoice->created_at->format('Y-m-d H:i') }}</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button type="button"
                        onclick="navigator.clipboard.writeText('{{ $invoice->public_payment_url }}').then(() => alert('تم نسخ رابط الفاتورة والدفع للعميل!'))"
                        class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-extrabold">
                    🔗 نسخ رابط الدفع للعميل
                </button>

                @php
                    $waPhone = preg_replace('/[^0-9]/', '', (string) $invoice->customer_phone);
                    if (str_starts_with($waPhone, '05')) { $waPhone = '966' . substr($waPhone, 1); }
                    $waText = urlencode("مرحباً {$invoice->customer_name}،\nإليك رابط فاتورة ({$invoice->title}) بقيمة " . number_format((float) $invoice->amount, 2) . " ر.س:\n{$invoice->public_payment_url}");
                @endphp
                <a href="https://wa.me/{{ $waPhone }}?text={{ $waText }}" target="_blank"
                   class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-extrabold">
                    💬 إرسال عبر واتساب
                </a>

                <a href="{{ route('provider.invoices.print', $invoice) }}" target="_blank"
                   class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold">
                    🖨️ طباعة
                </a>

                @if(!$invoice->isPaid())
                    <form method="POST" action="{{ route('provider.invoices.check-status', $invoice) }}" class="inline">
                        @csrf
                        <button type="submit" class="px-3.5 py-2 rounded-xl bg-amber-100 hover:bg-amber-200 text-amber-900 text-xs font-extrabold">
                            🔄 تحديث حالة الدفع
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Customer Payment Link Box -->
        <div class="p-card bg-slate-50">
            <label class="block text-xs font-extrabold text-slate-700 mb-1.5">رابط الفاتورة والدفع العام (شاركه مع عميلك):</label>
            <div class="flex flex-col sm:flex-row gap-2">
                <input type="text" readonly value="{{ $invoice->public_payment_url }}" class="p-input bg-white font-mono text-xs" dir="ltr">
                <a href="{{ $invoice->public_payment_url }}" target="_blank"
                   class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold text-center shrink-0">
                    معاينة صفحة العميل ↗
                </a>
            </div>
        </div>

        <!-- Invoice Template Preview -->
        <div>
            @include($invoice->templateView(), [
                'invoice'       => $invoice,
                'provider'      => $provider,
                'attachmentUrl' => $invoice->quote_attachment_path ? route('provider.invoices.attachment', $invoice) : null,
            ])
        </div>
    </div>
@endsection
