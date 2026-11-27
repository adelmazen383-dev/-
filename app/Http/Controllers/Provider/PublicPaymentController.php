<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\ProviderInvoice;
use App\Services\ProviderPaymentService;
use Illuminate\Http\Request;

class PublicPaymentController extends Controller
{
    public function __construct(private ProviderPaymentService $paymentService)
    {
    }

    /**
     * Public invoice & payment selection page for a service provider's customer.
     */
    public function show(string $token)
    {
        $invoice = ProviderInvoice::with('provider')
            ->where('public_token', $token)
            ->firstOrFail();

        $provider = $invoice->provider;

        return view('provider.public.pay', compact('invoice', 'provider'));
    }

    /**
     * Customer selects a payment method (Apple Pay, Mada/Visa, Tabby, Tamara).
     */
    public function selectMethod(Request $request, string $token)
    {
        $invoice = ProviderInvoice::with('provider')
            ->where('public_token', $token)
            ->firstOrFail();

        if ($invoice->isPaid()) {
            return redirect()->route('provider-pay.show', $token)
                ->with('success', 'تم سداد هذه الفاتورة بنجاح مسبقاً.');
        }

        if ($invoice->status === 'cancelled') {
            return redirect()->route('provider-pay.show', $token)
                ->with('error', 'هذه الفاتورة ملغاة ولا يمكن سدادها.');
        }

        $validated = $request->validate([
            'method' => ['required', 'string', 'in:standard,apple_pay,tabby,tamara'],
        ]);

        $method = $validated['method'];

        if ($method === 'tamara') {
            $res = $this->paymentService->createTamaraCheckout($invoice);
            if ($res['success']) {
                return redirect()->away($res['url']);
            }
            return redirect()->route('provider-pay.show', $token)
                ->with('error', $res['error'] ?? 'تعذر التحويل لبوابة تمارا حالياً.');
        }

        // Apple Pay / Mada / Visa / Tabby via Telr
        $res = $this->paymentService->createTelrOrder($invoice, $method);
        if ($res['success']) {
            return redirect()->away($res['url']);
        }

        return redirect()->route('provider-pay.show', $token)
            ->with('error', $res['error'] ?? 'تعذر التحويل لبوابة الدفع حالياً.');
    }

    /**
     * Telr callback for Service Provider invoices (isolated from core site /telr/callback).
     */
    public function telrCallback(Request $request)
    {
        $invoiceNumber = $request->query('invoice');
        $status        = $request->query('status');

        $invoice = ProviderInvoice::where('invoice_number', $invoiceNumber)->first();
        if (!$invoice) {
            abort(404);
        }

        if ($invoice->isPaid()) {
            return redirect()->route('provider-pay.show', $invoice->public_token)
                ->with('success', '✅ تم سداد الفاتورة بنجاح!');
        }

        if ($status === 'success' && $invoice->telr_order_ref) {
            $check = $this->paymentService->checkTelrOrder($invoice->telr_order_ref);
            if ($check['success'] && ($check['code'] ?? 0) === 3) {
                $this->paymentService->markPaidAndCreditWallet($invoice);
                return redirect()->route('provider-pay.show', $invoice->public_token)
                    ->with('success', '✅ تم تأكيد سداد الفاتورة بنجاح! شكراً لك.');
            }
        }

        if (in_array($status, ['declined', 'cancelled'], true)) {
            $invoice->update(['status' => 'failed']);
            return redirect()->route('provider-pay.show', $invoice->public_token)
                ->with('error', 'لم تكتمل عملية الدفع. يمكنك المحاولة مجدداً أو اختيار وسيلة دفع أخرى.');
        }

        return redirect()->route('provider-pay.show', $invoice->public_token)
            ->with('warning', 'جارٍ التحقق من حالة العملية مع بوابة الدفع.');
    }

    /**
     * Tamara callback for Service Provider invoices (isolated from core site /payment/tamara/callback).
     */
    public function tamaraCallback(Request $request)
    {
        $token   = $request->query('token');
        $status  = strtolower((string) $request->query('status', ''));
        $orderId = $request->query('orderId') ?: $request->query('order_id');

        $invoice = ProviderInvoice::where('public_token', $token)->first();
        if (!$invoice) {
            abort(404);
        }

        if ($invoice->isPaid()) {
            return redirect()->route('provider-pay.show', $invoice->public_token)
                ->with('success', '✅ تم سداد الفاتورة عبر تمارا بنجاح!');
        }

        if ($status === 'cancel' || $status === 'failure') {
            $invoice->update([
                'status'          => 'failed',
                'tamara_order_id' => $orderId ?: $invoice->tamara_order_id,
                'tamara_status'   => $status === 'cancel' ? 'canceled' : 'declined',
            ]);

            return redirect()->route('provider-pay.show', $invoice->public_token)
                ->with('error', 'لم تكتمل عملية الدفع عبر تمارا. يمكنك المحاولة مجدداً أو اختيار وسيلة دفع أخرى.');
        }

        $res = $this->paymentService->verifyTamaraOrder($invoice, is_string($orderId) ? $orderId : null);

        if (!empty($res['paid'])) {
            $this->paymentService->markPaidAndCreditWallet($invoice, 'tamara', null, [
                'tamara_order_id' => $res['order_id'] ?? $invoice->tamara_order_id,
                'tamara_status'   => $res['status'] ?? 'authorised',
            ]);

            return redirect()->route('provider-pay.show', $invoice->public_token)
                ->with('success', '✅ تم إتمام الدفع بالتقسيط عبر تمارا بنجاح!');
        }

        if (!empty($res['failed'])) {
            $invoice->update([
                'status'          => 'failed',
                'tamara_order_id' => $res['order_id'] ?? $invoice->tamara_order_id,
                'tamara_status'   => $res['status'] ?? 'declined',
            ]);
            return redirect()->route('provider-pay.show', $invoice->public_token)
                ->with('error', 'تعذر اعتماد الطلب لدى تمارا. يمكنك المحاولة مجدداً.');
        }

        return redirect()->route('provider-pay.show', $invoice->public_token)
            ->with('warning', 'تم استلام طلبك وجارٍ تأكيد الحالة من تمارا.');
    }

    /**
     * Tamara webhook for Service Provider invoices (isolated from core site /payment/tamara/webhook).
     */
    public function tamaraWebhook(Request $request)
    {
        if (!$this->paymentService->verifyTamaraWebhookRequest($request)) {
            return response()->json(['status' => 'unauthorized'], 401);
        }

        $orderId    = $request->input('order_id');
        $orderRef   = $request->input('order_reference_id');
        $eventType  = strtolower((string) $request->input('event_type', ''));

        $invoice = ProviderInvoice::where(function ($q) use ($orderId, $orderRef) {
            if ($orderId) {
                $q->orWhere('tamara_order_id', $orderId);
            }
            if ($orderRef) {
                $q->orWhere('tamara_order_reference_id', $orderRef);
            }
        })->first();

        if (!$invoice) {
            return response()->json(['status' => 'ignored'], 200);
        }

        if (in_array($eventType, ['order_approved', 'order_authorised', 'order_captured'], true)) {
            if (!$invoice->isPaid()) {
                $res = $this->paymentService->verifyTamaraOrder($invoice, is_string($orderId) ? $orderId : null);
                if (!empty($res['paid'])) {
                    $this->paymentService->markPaidAndCreditWallet($invoice, 'tamara', null, [
                        'tamara_order_id' => $res['order_id'] ?? $invoice->tamara_order_id,
                        'tamara_status'   => $res['status'] ?? 'authorised',
                    ]);
                }
            }
        } elseif (in_array($eventType, ['order_declined', 'order_canceled', 'order_expired'], true)) {
            if (!$invoice->isPaid()) {
                $invoice->update([
                    'status'        => 'failed',
                    'tamara_status' => str_replace('order_', '', $eventType),
                ]);
            }
        }

        return response()->json(['status' => 'ok'], 200);
    }
}
