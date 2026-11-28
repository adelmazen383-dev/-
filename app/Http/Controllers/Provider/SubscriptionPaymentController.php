<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\ProviderSubscriptionOrder;
use App\Models\ServiceProvider;
use App\Services\ProviderSubscriptionPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionPaymentController extends Controller
{
    public function __construct(
        private ProviderSubscriptionPaymentService $subPaymentService
    ) {}

    /**
     * Authenticated Provider clicks "اشترك الآن" on a plan card -> redirects directly to gateway checkout.
     * Price is ALWAYS read live from Admin settings on the server (never trusted from client input).
     */
    public function checkout(Request $request)
    {
        /** @var ServiceProvider $provider */
        $provider = Auth::guard('provider')->user();

        $validated = $request->validate([
            'duration_months' => ['required', 'integer', 'in:1,3,6,12'],
            'method'          => ['nullable', 'string', 'in:telr,tamara'],
        ]);

        $result = $this->subPaymentService->startCheckout(
            $provider,
            (int) $validated['duration_months'],
            (string) ($validated['method'] ?? 'telr')
        );

        if (! $result['success']) {
            return redirect()->route('provider.subscription')->with('error', $result['error']);
        }

        return redirect()->away($result['url']);
    }

    /**
     * Authenticated Provider refreshes gateway status on a pending subscription order.
     */
    public function checkStatus(ProviderSubscriptionOrder $order)
    {
        /** @var ServiceProvider $provider */
        $provider = Auth::guard('provider')->user();

        if ((int) $order->service_provider_id !== (int) $provider->id) {
            abort(403);
        }

        $res = $this->subPaymentService->verifyAndMarkPaid($order);

        if (! empty($res['paid'])) {
            return redirect()->route('provider.subscription')
                ->with('success', 'تم تأكيد استلام سداد الاشتراك بنجاح! طلبك الآن بانتظار موافقة الإدارة لتفعيل الحساب فوراً.');
        }

        return redirect()->route('provider.subscription')
            ->with('warning', $res['error'] ?? 'لم يتم تأكيد الدفع بعد.');
    }

    /**
     * Public Telr callback for subscription checkout (verifies server-to-server!).
     */
    public function telrCallback(Request $request)
    {
        $token  = (string) $request->query('token', '');
        $status = (string) $request->query('status', '');

        $order = ProviderSubscriptionOrder::where('public_token', $token)->first();
        if (! $order) {
            return redirect()->route('login')->with('error', 'طلب الاشتراك غير موجود.');
        }

        if ($status === 'cancelled' || $status === 'declined') {
            if ($order->isPendingPayment()) {
                $order->update(['status' => ProviderSubscriptionOrder::STATUS_FAILED]);
            }
            $msg = $status === 'cancelled' ? 'تم إلغاء عملية الدفع. يمكنك المحاولة مجدداً في أي وقت.' : 'تم رفض عملية الدفع من البنك. يُرجى المحاولة ببطاقة أخرى.';
            return $this->redirectAfterCallback($order, 'error', $msg);
        }

        $res = $this->subPaymentService->verifyAndMarkPaid($order);
        if (! empty($res['paid'])) {
            return $this->redirectAfterCallback(
                $order,
                'success',
                'تم استلام سداد اشتراكك بنجاح! طلبك الآن بانتظار موافقة الإدارة لتفعيل الاشتراك وفتح إصدار الفواتير فوراً.'
            );
        }

        return $this->redirectAfterCallback(
            $order,
            'warning',
            $res['error'] ?? 'لم يتم تأكيد عملية الدفع بعد. يمكنك الضغط على «تحديث حالة الدفع» بعد قليل.'
        );
    }

    /**
     * Public Tamara callback for subscription checkout (verifies & authorises server-to-server!).
     */
    public function tamaraCallback(Request $request)
    {
        $token   = (string) $request->input('token', $request->query('token', ''));
        $status  = strtolower((string) $request->input('status', $request->query('status', '')));
        $orderId = (string) $request->input('orderId', $request->query('orderId', ''));

        $order = ProviderSubscriptionOrder::where('public_token', $token)->first();
        if (! $order) {
            return redirect()->route('login')->with('error', 'طلب الاشتراك غير موجود.');
        }

        if (in_array($status, ['cancel', 'cancelled', 'failure', 'failed', 'declined'], true)) {
            if ($order->isPendingPayment()) {
                $order->update(['status' => ProviderSubscriptionOrder::STATUS_FAILED, 'tamara_status' => $status]);
            }
            return $this->redirectAfterCallback($order, 'error', 'لم تكتمل عملية الدفع عبر تمارا. يمكنك المحاولة مرة أخرى.');
        }

        $res = $this->subPaymentService->verifyAndMarkPaid($order, $orderId ?: null);
        if (! empty($res['paid'])) {
            return $this->redirectAfterCallback(
                $order,
                'success',
                'تم تأكيد سداد اشتراكك عبر تمارا بنجاح! طلبك الآن بانتظار موافقة الإدارة لتفعيل الاشتراك فوراً.'
            );
        }

        return $this->redirectAfterCallback(
            $order,
            'warning',
            $res['error'] ?? 'جاري التحقق من عملية تمارا.'
        );
    }

    /**
     * Public Tamara server-to-server webhook for subscription checkout.
     */
    public function tamaraWebhook(Request $request)
    {
        if (! $this->subPaymentService->verifyTamaraWebhookRequest($request)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $tamaraOrderId = (string) $request->input('order_id', '');
        $orderRefId    = (string) $request->input('order_reference_id', '');

        $order = null;
        if ($tamaraOrderId !== '') {
            $order = ProviderSubscriptionOrder::where('tamara_order_id', $tamaraOrderId)->first();
        }
        if (! $order && $orderRefId !== '') {
            $order = ProviderSubscriptionOrder::where('tamara_order_reference_id', $orderRefId)->first();
        }

        if (! $order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $this->subPaymentService->verifyAndMarkPaid($order, $tamaraOrderId ?: null);

        return response()->json(['received' => true]);
    }

    private function redirectAfterCallback(ProviderSubscriptionOrder $order, string $flashKey, string $message)
    {
        if (Auth::guard('provider')->check()) {
            return redirect()->route('provider.subscription')->with($flashKey, $message);
        }

        return redirect()->route('login')->with($flashKey, $message);
    }
}
