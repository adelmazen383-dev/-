<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\ProviderInvoice;
use App\Models\ServiceProvider;
use App\Services\ProviderPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    public function __construct(private ProviderPaymentService $paymentService)
    {
    }

    public function index(Request $request)
    {
        /** @var ServiceProvider $provider */
        $provider = Auth::guard('provider')->user();

        $query = $provider->invoices()->latest();

        if ($request->filled('search')) {
            $s = trim((string) $request->search);
            $query->where(function ($q) use ($s) {
                $q->where('invoice_number', 'like', "%{$s}%")
                    ->orWhere('customer_name', 'like', "%{$s}%")
                    ->orWhere('customer_phone', 'like', "%{$s}%")
                    ->orWhere('title', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status') && in_array($request->status, ['pending', 'paid', 'failed', 'cancelled'], true)) {
            $query->where('status', $request->status);
        }

        $invoices = $query->paginate(15)->withQueryString();

        return view('provider.invoices.index', compact('provider', 'invoices'));
    }

    public function create()
    {
        /** @var ServiceProvider $provider */
        $provider = Auth::guard('provider')->user();

        return view('provider.invoices.create', compact('provider'));
    }

    public function store(Request $request)
    {
        /** @var ServiceProvider $provider */
        $provider = Auth::guard('provider')->user();

        // Double defense-in-depth subscription check (alongside provider.subscribed middleware)
        if (!$provider->hasActiveSubscription()) {
            return redirect()->route('provider.dashboard')
                ->with('error', 'يجب أن يكون اشتراكك الشهري نشطاً لتتمكن من إنشاء فواتير جديدة.');
        }

        $validated = $request->validate([
            'customer_name'   => ['required', 'string', 'max:180'],
            'customer_phone'  => ['required', 'string', 'min:9', 'max:25'],
            'customer_email'  => ['nullable', 'email', 'max:180'],
            'title'           => ['required', 'string', 'max:200'],
            'description'     => ['nullable', 'string', 'max:2000'],
            'amount'          => ['required', 'numeric', 'min:1', 'max:500000'],
            'notes'           => ['nullable', 'string', 'max:1000'],
            'service_details' => ['nullable', 'array'],
            'service_details.*' => ['nullable', 'string', 'max:500'],
        ], [
            'customer_name.required'  => 'يرجى إدخال اسم العميل.',
            'customer_phone.required' => 'يرجى إدخال رقم جوال العميل.',
            'title.required'          => 'يرجى إدخال عنوان الخدمة أو الباقة.',
            'amount.required'         => 'يرجى إدخال مبلغ الفاتورة.',
            'amount.min'              => 'مبلغ الفاتورة يجب أن لا يقل عن 1 ريال.',
        ]);

        $amount = round((float) $validated['amount'], 2);

        // Clean empty keys from service_details
        $details = collect($validated['service_details'] ?? [])
            ->map(fn ($v) => is_string($v) ? trim($v) : $v)
            ->filter(fn ($v) => $v !== null && $v !== '')
            ->all();

        if ($provider->service_type === 'other' && empty($details['custom_service_type']) && $provider->custom_service_name) {
            $details['custom_service_type'] = $provider->custom_service_name;
        }

        $invoice = ProviderInvoice::create([
            'service_provider_id'    => $provider->id,
            'service_type'           => $provider->service_type,
            'customer_name'          => trim($validated['customer_name']),
            'customer_phone'         => ServiceProvider::normalizePhone($validated['customer_phone']),
            'customer_email'         => $validated['customer_email'] ?? null,
            'title'                  => trim($validated['title']),
            'description'            => $validated['description'] ?? null,
            'service_details'        => $details,
            'amount'                 => $amount,
            'gateway_fee_percentage' => 0.00,
            'gateway_fee_amount'     => 0.00,
            'total_payable'          => $amount,
            'payment_method'         => 'all',
            'status'                 => 'pending',
            'notes'                  => $validated['notes'] ?? null,
        ]);

        return redirect()->route('provider.invoices.show', $invoice)
            ->with('success', '✅ تم إنشاء الفاتورة رقم ' . $invoice->invoice_number . ' بنجاح! شارك رابط الدفع مع العميل الآن.');
    }

    public function show(ProviderInvoice $invoice)
    {
        /** @var ServiceProvider $provider */
        $provider = Auth::guard('provider')->user();
        abort_unless($invoice->service_provider_id === $provider->id, 403);

        $invoice->load('walletTransaction');

        return view('provider.invoices.show', compact('provider', 'invoice'));
    }

    public function print(ProviderInvoice $invoice)
    {
        /** @var ServiceProvider $provider */
        $provider = Auth::guard('provider')->user();
        abort_unless($invoice->service_provider_id === $provider->id, 403);

        return view('provider.invoices.print', compact('provider', 'invoice'));
    }

    public function checkStatus(ProviderInvoice $invoice)
    {
        /** @var ServiceProvider $provider */
        $provider = Auth::guard('provider')->user();
        abort_unless($invoice->service_provider_id === $provider->id, 403);

        if ($invoice->isPaid()) {
            return back()->with('success', 'الفاتورة مدفوعة ومؤكدة ومضافة إلى رصيد محفظتك بالفعل.');
        }

        if ($invoice->payment_method === 'tamara' && $invoice->tamara_order_id) {
            $res = $this->paymentService->verifyTamaraOrder($invoice);
            if (!empty($res['paid'])) {
                $this->paymentService->markPaidAndCreditWallet($invoice, 'tamara', null, [
                    'tamara_status' => $res['status'] ?? 'authorised',
                ]);
                return back()->with('success', '✅ تم التحقق من الدفع عبر تمارا! تمت إضافة ' . number_format((float) $invoice->amount, 2) . ' ر.س إلى محفظتك.');
            }
            if (!empty($res['failed'])) {
                $invoice->update(['status' => 'failed', 'tamara_status' => $res['status'] ?? 'declined']);
                return back()->with('error', 'فشلت محاولة الدفع لدى تمارا. يمكن للعميل المحاولة مرة أخرى عبر نفس الرابط.');
            }
            return back()->with('warning', 'الطلب لا يزال قيد الانتظار لدى تمارا.');
        }

        if ($invoice->telr_order_ref) {
            $res = $this->paymentService->checkTelrOrder($invoice->telr_order_ref);
            if ($res['success'] && ($res['code'] ?? 0) === 3) {
                $this->paymentService->markPaidAndCreditWallet($invoice);
                return back()->with('success', '✅ تم التحقق من الدفع بنجاح! تمت إضافة ' . number_format((float) $invoice->amount, 2) . ' ر.س إلى محفظتك.');
            }
            if ($res['success'] && in_array($res['code'] ?? 0, [-1, -2], true)) {
                $invoice->update(['status' => 'failed']);
                return back()->with('error', 'لم تكتمل عملية الدفع (' . ($res['message'] ?? 'مرفوضة') . ').');
            }
            return back()->with('warning', 'عملية الدفع لا تزال بانتظار إتمام العميل.');
        }

        return back()->with('warning', 'لم يبدأ العميل جلسة دفع إلكتروني لهذه الفاتورة بعد.');
    }
}
