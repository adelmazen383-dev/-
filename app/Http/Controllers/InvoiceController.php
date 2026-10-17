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
        $validated['deposit_amount']   = $validated['deposit_amount'] ?? 500;

        $validated['user_id']  = auth()->id();
        $validated['template'] = auth()->user()?->invoice_template ?? 'taotheeq';

        $invoice = Invoice::create($validated);

        // Call Telr API to get the payment URL
        $telrResponse = $this->telrService->createOrder($invoice);

        if ($telrResponse['success']) {
            $invoice->update([
                'telr_transaction_url' => $telrResponse['url'],
                'telr_order_ref'       => $telrResponse['ref'],
            ]);
            return redirect()
                ->route('invoices.show', $invoice)
                ->with('success', 'تم إنشاء الفاتورة وكارت الحجز ورابط الدفع بنجاح.');
        }

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('error', 'تم إنشاء الفاتورة لكن فشل الاتصال ببوابة Telr. يمكنك إعادة المحاولة لاحقاً.');
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

        return view('invoices.callback', compact('status'));
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
            return back()->with('success', '✅ تم التحقق — الفاتورة مدفوعة! يمكنك الآن إنشاء العقد.');
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

        return back()->with('error', 'فشل الاتصال ببوابة Telr. تحقق من البيانات وحاول مرة أخرى.');
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

        return back()->with('success', '✅ تم تسجيل الفاتورة كمدفوعة بنجاح وتحديث المبلغ المتبقي.');
    }
}
