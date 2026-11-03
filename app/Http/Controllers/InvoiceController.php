<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\TelrService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    public function __construct(
        private TelrService $telrService
    ) {}

    /* ── List all invoices ── */
    public function index(Request $request)
    {
        $query = Invoice::latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('national_id', 'like', "%{$search}%")
                  ->orWhere('apartment_number', 'like', "%{$search}%")
                  ->orWhere('suite_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $invoices = $query->paginate(15)->withQueryString();
        return view('invoices.index', compact('invoices'));
    }

    /* ── Create form ── */
    public function create()
    {
        return view('invoices.create');
    }

    /* ── Store invoice & call Telr ── */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name'    => ['required', 'string', 'max:255'],
            'phone'            => ['required', 'string', 'regex:/^[0-9+\s\-]{7,20}$/'],
            'national_id'      => ['required', 'string', 'regex:/^[0-9]{10,14}$/'],
            'apartment_number' => ['required', 'string', 'max:50'],
            'suite_number'     => ['nullable', 'string', 'max:50'],
            'check_in_date'    => ['nullable', 'date'],
            'check_out_date'   => ['nullable', 'date', 'after_or_equal:check_in_date'],
            'amount'           => ['required', 'numeric', 'min:1', 'max:9999999'],
            'agreed_amount'    => ['nullable', 'numeric', 'min:0'],
            'amount_paid'      => ['nullable', 'numeric', 'min:0'],
            'deposit_amount'   => ['nullable', 'numeric', 'min:0'],
            'payment_method'   => ['nullable', 'string', 'in:all,standard,apple_pay,tabby,tamara'],
            'property_name'     => ['nullable', 'string', 'max:255'],
            'property_location' => ['nullable', 'string', 'max:255'],
            'property_phone'    => ['nullable', 'string', 'max:50'],
            'notes'            => ['nullable', 'string', 'max:1000'],
        ], [
            'phone.regex'      => 'رقم الهاتف غير صحيح.',
            'national_id.regex' => 'الرقم القومي يجب أن يكون من 10 إلى 14 رقم.',
            'amount.min'       => 'المبلغ يجب أن يكون أكبر من صفر.',
            'check_out_date.after_or_equal' => 'تاريخ الخروج يجب أن يكون بعد تاريخ الدخول.',
        ]);

        // Generate unique invoice number
        $validated['invoice_number'] = 'INV-' . strtoupper(Str::random(8));
        $validated['status'] = 'pending';

        // Use suite_number as apartment_number if suite is provided and apartment is same
        if (!empty($validated['suite_number']) && empty($validated['apartment_number'])) {
            $validated['apartment_number'] = $validated['suite_number'];
        }

        // Calculate remaining
        $agreedAmount = $validated['agreed_amount'] ?? $validated['amount'];
        $amountPaid   = $validated['amount_paid'] ?? 0;
        $validated['agreed_amount']    = $agreedAmount;
        $validated['remaining_amount'] = max(0, $agreedAmount - $amountPaid);
        $validated['deposit_amount']   = (isset($validated['deposit_amount']) && $validated['deposit_amount'] !== '') ? (float)$validated['deposit_amount'] : 0.00;

        // Payment method & Tabby / Tamara 15% fee calculation
        $paymentMethod = $validated['payment_method'] ?? 'all';
        $baseAmount    = (float)$validated['amount'] + (float)$validated['deposit_amount'];

        if (in_array($paymentMethod, ['tabby', 'tamara'])) {
            $feePercentage = 15.00;
            $feeAmount     = round($baseAmount * 0.15, 2);
            $totalPayable  = $baseAmount + $feeAmount;
        } else {
            $feePercentage = 0.00;
            $feeAmount     = 0.00;
            $totalPayable  = $baseAmount;
        }

        $validated['payment_method'] = $paymentMethod;
        $validated['fee_percentage'] = $feePercentage;
        $validated['fee_amount']     = $feeAmount;
        $validated['total_payable']  = $totalPayable;

        $validated['user_id']  = auth()->id();
        $validated['template'] = auth()->user()?->invoice_template ?? 'taotheeq';

        $invoice = Invoice::create($validated);

        // Call Telr API to get the payment URL
        $telrResponse = $this->telrService->createOrder($invoice, $paymentMethod);

        if ($telrResponse['success']) {
            $updateData = [
                'telr_transaction_url' => $telrResponse['url'],
                'telr_order_ref'       => $telrResponse['ref'],
            ];
            if ($paymentMethod === 'tabby') {
                $updateData['tabby_url'] = $telrResponse['url'];
            } elseif ($paymentMethod === 'tamara') {
                $updateData['tamara_url'] = $telrResponse['url'];
            }
            $invoice->update($updateData);

            return redirect()
                ->route('invoices.show', $invoice)
                ->with('success', 'تم إنشاء الفاتورة وكارت الحجز ورابط الدفع بنجاح.');
        }

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('error', 'تم إنشاء الفاتورة، لكن تعذر الاتصال ببوابة Telr: ' . ($telrResponse['error'] ?? 'خطأ غير معروف') . '. يمكنك إعادة المحاولة أو إدخال رابط دفع يدوياً.');
    }

    /* ── Show invoice details ── */
    public function show(Invoice $invoice)
    {
        return view('invoices.show', compact('invoice'));
    }

    /* ── Booking Card (printable / shareable) ── */
    public function bookingCard(Invoice $invoice)
    {
        return view('invoices.booking-card', compact('invoice'));
    }

    /* ── Public Booking Card via Token ── */
    public function publicBookingCard(string $token)
    {
        $invoice = Invoice::where('booking_card_token', $token)->firstOrFail();
        return view('invoices.booking-card-public', compact('invoice'));
    }

    /* ── Invoice Print (Taotheeq / Sindi dynamic) ── */
    public function invoicePrint(Invoice $invoice)
    {
        $design = request('design');
        if ($design === 'sindi' || (!$design && $invoice->isSindi())) {
            return view('invoices.sindi-print', compact('invoice'));
        }
        return view('invoices.invoice-print', compact('invoice'));
    }

    /* ── Public Invoice via Token ── */
    public function publicInvoice(string $token)
    {
        $invoice = Invoice::where('booking_card_token', $token)->firstOrFail();
        $design = request('design');
        if ($design === 'sindi' || (!$design && $invoice->isSindi())) {
            return view('invoices.sindi-print', compact('invoice'));
        }
        return view('invoices.invoice-print', compact('invoice'));
    }

    /* ── Telr return URL (public — customer returns here after payment) ── */
    public function telrCallback(Request $request)
    {
        $status        = $request->query('status', 'unknown');
        $invoiceNumber = $request->query('invoice');

        // Try to auto-check the invoice if we can identify it
        if ($invoiceNumber) {
            $invoice = Invoice::where('invoice_number', $invoiceNumber)->first();
            if ($invoice && $invoice->isPending() && $invoice->telr_order_ref) {
                $check = $this->telrService->checkOrder($invoice->telr_order_ref);
                if (isset($check['success']) && $check['success'] && ($check['code'] ?? 0) == 3) {
                    $invoice->update([
                        'status'  => 'paid',
                        'paid_at' => now(),
                        'amount_paid'      => $invoice->amount,
                        'remaining_amount' => max(0, ($invoice->agreed_amount ?? $invoice->amount) - $invoice->amount),
                    ]);
                }
            }
        }

        return view('invoices.callback', compact('status', 'invoice'));
    }

    /* ── Manual "Check Status" button for admin ── */
    public function checkStatus(Invoice $invoice)
    {
        if (!$invoice->telr_order_ref) {
            return back()->with('error', 'لا يوجد رقم مرجعي لهذه الفاتورة. أعد إنشاء رابط الدفع.');
        }

        $telrResponse = $this->telrService->checkOrder($invoice->telr_order_ref);

        if (!$telrResponse['success']) {
            return back()->with('error', 'فشل التحقق من حالة الطلب: ' . ($telrResponse['error'] ?? 'خطأ غير معروف'));
        }

        $code = $telrResponse['code'] ?? 0;

        if ($code == 3) {
            $invoice->update([
                'status'           => 'paid',
                'paid_at'          => now(),
                'amount_paid'      => $invoice->amount,
                'remaining_amount' => max(0, ($invoice->agreed_amount ?? $invoice->amount) - $invoice->amount),
            ]);
            return redirect()
                ->route('contracts.create', ['invoice_id' => $invoice->id])
                ->with('success', '✅ تم التحقق من سداد الفاتورة بنجاح! تم توجيهك لإنشاء العقد وتعبئة كافة البيانات تلقائياً.');
        }

        if ($code == 2) {
            return back()->with('error', '⏳ العملية قيد المعالجة لم تكتمل بعد.');
        }

        // -1 declined, -2 cancelled, etc.
        $invoice->update(['status' => 'failed']);
        return back()->with('error', '❌ العملية فشلت أو تم رفضها. الحالة: ' . ($telrResponse['message'] ?? 'غير معروف'));
    }

    /* ── Retry generating a Telr payment link ── */
    public function retryTelr(Invoice $invoice)
    {
        if ($invoice->isPaid()) {
            return back()->with('error', 'هذه الفاتورة مدفوعة بالفعل.');
        }

        $telrResponse = $this->telrService->createOrder($invoice);

        if ($telrResponse['success']) {
            $invoice->update([
                'telr_transaction_url' => $telrResponse['url'],
                'telr_order_ref'       => $telrResponse['ref'],
                'status'               => 'pending',
            ]);
            return back()->with('success', 'تم تجديد رابط الدفع بنجاح.');
        }

        return back()->with('error', 'فشل الاتصال ببوابة Telr: ' . ($telrResponse['error'] ?? 'خطأ غير معروف'));
    }

    /* ── Manually update / enter payment link ── */
    public function updatePaymentLink(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'telr_transaction_url' => ['nullable', 'url', 'max:1000'],
            'tabby_url'            => ['nullable', 'url', 'max:1000'],
            'tamara_url'           => ['nullable', 'url', 'max:1000'],
            'telr_order_ref'       => ['nullable', 'string', 'max:100'],
            'payment_method'       => ['nullable', 'string', 'in:all,standard,apple_pay,tabby,tamara'],
        ], [
            'telr_transaction_url.url' => 'رابط الدفع يجب أن يكون رابطاً صحيحاً يبدأ بـ http:// أو https://',
            'tabby_url.url'            => 'رابط تابي يجب أن يكون رابطاً صحيحاً يبدأ بـ http:// أو https://',
            'tamara_url.url'           => 'رابط تمارا يجب أن يكون رابطاً صحيحاً يبدأ بـ http:// أو https://',
        ]);

        $updateData = [
            'telr_transaction_url' => $validated['telr_transaction_url'] ?? $invoice->telr_transaction_url,
            'tabby_url'            => $validated['tabby_url'] ?? $invoice->tabby_url,
            'tamara_url'           => $validated['tamara_url'] ?? $invoice->tamara_url,
            'telr_order_ref'       => $validated['telr_order_ref'] ?: $invoice->telr_order_ref,
        ];

        if (!empty($validated['payment_method'])) {
            $method        = $validated['payment_method'];
            $baseAmount    = $invoice->base_amount;
            $feePercentage = in_array($method, ['tabby', 'tamara']) ? 15.00 : 0.00;
            $feeAmount     = in_array($method, ['tabby', 'tamara']) ? round($baseAmount * 0.15, 2) : 0.00;
            $totalPayable  = $baseAmount + $feeAmount;

            $updateData['payment_method'] = $method;
            $updateData['fee_percentage'] = $feePercentage;
            $updateData['fee_amount']     = $feeAmount;
            $updateData['total_payable']  = $totalPayable;
        }

        $invoice->update($updateData);

        return back()->with('success', 'تم حفظ وتحديث إعدادات وروابط الدفع للفاتورة بنجاح.');
    }

    /* ── Customer selects payment method on public card (Apple Pay / Tabby / Tamara) ── */
    public function selectPaymentMethod(Request $request, string $token)
    {
        $invoice = Invoice::where('booking_card_token', $token)->firstOrFail();

        $validated = $request->validate([
            'method' => ['required', 'string', 'in:all,standard,apple_pay,tabby,tamara'],
        ]);

        $method = $validated['method'];

        if ($invoice->isPaid()) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'error' => 'الفاتورة مسجلة كمدفوعة بالفعل.']);
            }
            return redirect()->route('booking-card.show', $token)->with('error', 'الفاتورة مسجلة كمدفوعة بالفعل.');
        }

        $baseAmount    = $invoice->base_amount;
        $feePercentage = in_array($method, ['tabby', 'tamara']) ? 15.00 : 0.00;
        $feeAmount     = in_array($method, ['tabby', 'tamara']) ? round($baseAmount * 0.15, 2) : 0.00;
        $totalPayable  = $baseAmount + $feeAmount;

        $invoice->update([
            'payment_method' => $method,
            'fee_percentage' => $feePercentage,
            'fee_amount'     => $feeAmount,
            'total_payable'  => $totalPayable,
        ]);

        // Tabby handling
        if ($method === 'tabby') {
            if ($invoice->tabby_url) {
                return redirect()->away($invoice->tabby_url);
            }
            if ($request->wantsJson()) {
                return response()->json([
                    'success'            => true,
                    'action'             => 'open_modal',
                    'method'             => 'tabby',
                    'base_amount'        => $baseAmount,
                    'fee_amount'         => $feeAmount,
                    'total_payable'      => $totalPayable,
                    'installment_amount' => round($totalPayable / 4, 2),
                ]);
            }
            return redirect()->route('booking-card.show', ['token' => $token, 'open_modal' => 'tabby']);
        }

        // Tamara handling
        if ($method === 'tamara') {
            if ($invoice->tamara_url) {
                return redirect()->away($invoice->tamara_url);
            }
            if ($request->wantsJson()) {
                return response()->json([
                    'success'            => true,
                    'action'             => 'open_modal',
                    'method'             => 'tamara',
                    'base_amount'        => $baseAmount,
                    'fee_amount'         => $feeAmount,
                    'total_payable'      => $totalPayable,
                    'installment_amount' => round($totalPayable / 4, 2),
                ]);
            }
            return redirect()->route('booking-card.show', ['token' => $token, 'open_modal' => 'tamara']);
        }

        // Apple Pay / Card / Standard payment handling via Telr
        $telrResponse = $this->telrService->createOrder($invoice, $method);

        if ($telrResponse['success']) {
            $invoice->update([
                'telr_transaction_url' => $telrResponse['url'],
                'telr_order_ref'       => $telrResponse['ref'],
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success'            => true,
                    'url'                => $telrResponse['url'],
                    'method'             => $method,
                    'base_amount'        => $baseAmount,
                    'fee_amount'         => $feeAmount,
                    'total_payable'      => $totalPayable,
                    'installment_amount' => round($totalPayable / 4, 2),
                ]);
            }

            return redirect()->away($telrResponse['url']);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => false,
                'error'   => $telrResponse['error'] ?? 'تعذر توليد رابط الدفع، يرجى المحاولة لاحقاً.',
            ], 422);
        }

        return redirect()->route('booking-card.show', $token)->with('error', 'تعذر توليد رابط الدفع: ' . ($telrResponse['error'] ?? 'خطأ غير معروف'));
    }

    /* ── Confirm BNPL payment (Tabby / Tamara) ── */
    public function confirmBnplPayment(Request $request, string $token)
    {
        $invoice = Invoice::where('booking_card_token', $token)->firstOrFail();

        $validated = $request->validate([
            'method' => ['required', 'string', 'in:tabby,tamara'],
            'phone'  => ['required', 'string', 'max:25'],
        ]);

        $method = $validated['method'];
        $baseAmount = $invoice->base_amount;
        $feeAmount = round($baseAmount * 0.15, 2);
        $totalPayable = $baseAmount + $feeAmount;
        $methodLabel = $method === 'tabby' ? 'تابي Tabby' : 'تمارا Tamara';

        $invoice->update([
            'payment_method'   => $method,
            'fee_percentage'   => 15.00,
            'fee_amount'       => $feeAmount,
            'total_payable'    => $totalPayable,
            'status'           => 'paid',
            'paid_at'          => now(),
            'amount_paid'      => $invoice->amount,
            'remaining_amount' => max(0, ($invoice->agreed_amount ?? $invoice->amount) - $invoice->amount),
            'notes'            => trim(($invoice->notes ?? '') . " | تم السداد بالتقسيط عبر {$methodLabel} على 4 دفعات (هاتف: {$validated['phone']})"),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success'      => true,
                'message'      => "✅ تم تأكيد عملية الدفع بالتقسيط بنجاح عبر {$methodLabel}!",
                'redirect_url' => route('booking-card.show', ['token' => $token, 'paid_bnpl' => 1]),
            ]);
        }

        return redirect()->route('booking-card.show', ['token' => $token, 'paid_bnpl' => 1])
            ->with('success', "✅ تم تأكيد عملية الدفع بالتقسيط بنجاح عبر {$methodLabel}!");
    }

    /* ── Mark as paid manually (cash / bank transfer / testing) ── */
    public function markAsPaid(Invoice $invoice)
    {
        if ($invoice->isPaid()) {
            return back()->with('error', 'هذه الفاتورة مسجلة كمدفوعة بالفعل.');
        }

        $invoice->update([
            'status'           => 'paid',
            'paid_at'          => now(),
            'amount_paid'      => $invoice->amount,
            'remaining_amount' => max(0, ($invoice->agreed_amount ?? $invoice->amount) - $invoice->amount),
        ]);

        return redirect()
            ->route('contracts.create', ['invoice_id' => $invoice->id])
            ->with('success', '✅ تم تسجيل الفاتورة كمدفوعة بنجاح! تم توجيهك لإنشاء العقد وتعبئة بيانات المستأجر والوحدة تلقائياً.');
    }
}
