<?php

namespace App\Services;

use App\Models\ProviderSubscriptionOrder;
use App\Models\ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Isolated direct subscription payment & Admin-approval workflow for Service Providers.
 * Always reads LIVE Admin-configured plan prices from ProviderSubscriptionConfigService
 * so any price edit in the Admin panel takes effect immediately on the very next checkout.
 */
class ProviderSubscriptionPaymentService
{
    private string $telrStoreId;
    private string $telrAuthKey;
    private string $telrTestMode;
    private string $telrApiUrl = 'https://secure.telr.com/gateway/order.json';

    private string $tamaraApiToken;
    private string $tamaraNotificationToken;
    private string $tamaraApiUrl;

    public function __construct(
        private ProviderSubscriptionService $subscriptionService
    ) {
        $this->telrStoreId  = (string) config('services.telr.store_id', '');
        $this->telrAuthKey  = (string) config('services.telr.auth_key', '');
        $this->telrTestMode = (string) config('services.telr.test_mode', '1');

        $this->tamaraApiToken          = (string) config('services.tamara.api_token', '');
        $this->tamaraNotificationToken = (string) config('services.tamara.notification_token', '');
        $this->tamaraApiUrl            = rtrim((string) config('services.tamara.api_url', 'https://api.tamara.co'), '/');
    }

    public function isTelrConfigured(): bool
    {
        return $this->telrStoreId !== '' && $this->telrAuthKey !== '';
    }

    public function isTamaraConfigured(): bool
    {
        return $this->tamaraApiToken !== '';
    }

    /**
     * Create a subscription payment order using the LIVE price configured by Admin
     * and open a direct gateway checkout session (Telr or Tamara).
     */
    public function startCheckout(ServiceProvider $provider, int $months, string $method = 'telr'): array
    {
        $config = ProviderSubscriptionConfigService::getConfig();
        $plan   = $config['plans'][$months] ?? null;

        if (! $plan || empty($plan['enabled'])) {
            return ['success' => false, 'error' => 'هذه الباقة غير متاحة حالياً. يُرجى اختيار باقة مفعّلة.'];
        }

        $price = round((float) $plan['price'], 2);
        if ($price <= 0) {
            return ['success' => false, 'error' => 'سعر هذه الباقة غير مهيأ للدفع الإلكتروني بعد. يُرجى التواصل مع الإدارة.'];
        }

        // Prevent duplicate paid request while one is already awaiting Admin approval
        $existingPendingApproval = $provider->subscriptionOrders()
            ->where('status', ProviderSubscriptionOrder::STATUS_PAID_PENDING_APPROVAL)
            ->latest()
            ->first();

        if ($existingPendingApproval) {
            return [
                'success' => false,
                'error'   => 'لديك بالفعل طلب اشتراك مدفوع (' . $existingPendingApproval->plan_name . ') بانتظار موافقة الإدارة وتفعيله.',
            ];
        }

        $method = $method === 'tamara' ? 'tamara' : 'telr';

        $order = ProviderSubscriptionOrder::create([
            'service_provider_id' => $provider->id,
            'duration_months'     => $months,
            'plan_name'           => (string) $plan['label'],
            'amount'              => $price,
            'payment_method'      => $method,
            'status'              => ProviderSubscriptionOrder::STATUS_PENDING_PAYMENT,
        ]);

        if ($method === 'tamara') {
            return $this->createTamaraCheckout($order, $provider);
        }

        return $this->createTelrCheckout($order, $provider);
    }

    private function createTelrCheckout(ProviderSubscriptionOrder $order, ServiceProvider $provider): array
    {
        if (! $this->isTelrConfigured()) {
            $order->update(['status' => ProviderSubscriptionOrder::STATUS_FAILED]);
            return ['success' => false, 'error' => 'بوابة الدفع الإلكتروني غير مهيأة حالياً.'];
        }

        $latinName = $this->transliterateArabic((string) $provider->name);
        $parts     = preg_split('/\s+/', trim($latinName));
        $firstName = ! empty($parts[0]) && strlen($parts[0]) >= 2 ? $parts[0] : 'Provider';
        $surname   = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : 'Thiqa';
        if (strlen($surname) < 2) {
            $surname = 'Thiqa';
        }

        $cleanPhone = preg_replace('/[^0-9+]/', '', (string) $provider->phone);
        if (strlen($cleanPhone) < 7) {
            $cleanPhone = '0500000000';
        }

        $email = filter_var($provider->email, FILTER_VALIDATE_EMAIL)
            ? $provider->email
            : 'provider.' . $provider->id . '@mail.thiqa.me';

        $payload = [
            'ivp_method'   => 'create',
            'ivp_store'    => $this->telrStoreId,
            'ivp_authkey'  => $this->telrAuthKey,
            'ivp_cart'     => $order->order_number . '-' . time(),
            'ivp_test'     => $this->telrTestMode,
            'ivp_amount'   => number_format((float) $order->amount, 2, '.', ''),
            'ivp_currency' => 'SAR',
            'ivp_desc'     => 'Thiqa Provider Subscription ' . $order->duration_months . 'M (' . $order->order_number . ')',
            'ivp_framed'   => 0,
            'return_auth'  => route('provider-sub-pay.telr.callback', ['token' => $order->public_token, 'status' => 'success']),
            'return_decl'  => route('provider-sub-pay.telr.callback', ['token' => $order->public_token, 'status' => 'declined']),
            'return_can'   => route('provider-sub-pay.telr.callback', ['token' => $order->public_token, 'status' => 'cancelled']),
            'bill_fname'   => $firstName,
            'bill_sname'   => $surname,
            'bill_addr1'   => 'King Fahd Road',
            'bill_addr2'   => 'District Riyadh',
            'bill_city'    => 'Riyadh',
            'bill_region'  => 'Riyadh',
            'bill_country' => 'SA',
            'bill_zip'     => '12271',
            'bill_email'   => $email,
            'bill_phone'   => $cleanPhone,
        ];

        try {
            $response = Http::asForm()->timeout(15)->post($this->telrApiUrl, $payload);

            if ($response->successful() && isset($response['order']['url'])) {
                $order->update([
                    'telr_order_ref'       => $response['order']['ref'],
                    'telr_transaction_url' => $response['order']['url'],
                ]);

                return [
                    'success' => true,
                    'order'   => $order->fresh(),
                    'url'     => $response['order']['url'],
                ];
            }

            $errMsg = $response['error']['message'] ?? ($response['error']['note'] ?? 'تعذر فتح صفحة الدفع حالياً.');
            $order->update(['status' => ProviderSubscriptionOrder::STATUS_FAILED]);

            Log::error('Provider Subscription Telr create failed', [
                'order'  => $order->order_number,
                'status' => $response->status(),
            ]);

            return ['success' => false, 'error' => $errMsg];
        } catch (\Throwable $e) {
            $order->update(['status' => ProviderSubscriptionOrder::STATUS_FAILED]);
            Log::error('Provider Subscription Telr exception', ['order' => $order->order_number, 'error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'حدث خطأ أثناء الاتصال ببوابة الدفع.'];
        }
    }

    private function createTamaraCheckout(ProviderSubscriptionOrder $order, ServiceProvider $provider): array
    {
        if (! $this->isTamaraConfigured()) {
            $order->update(['status' => ProviderSubscriptionOrder::STATUS_FAILED]);
            return ['success' => false, 'error' => 'بوابة تمارا غير مهيأة حالياً.'];
        }

        $amountFloat = (float) number_format((float) $order->amount, 2, '.', '');
        $cleanPhone  = preg_replace('/[^0-9]/', '', (string) $provider->phone);
        $e164Phone   = $this->formatPhoneE164($cleanPhone);
        $email       = filter_var($provider->email, FILTER_VALIDATE_EMAIL)
            ? $provider->email
            : 'provider.' . $provider->id . '@mail.thiqa.me';

        $nameParts = preg_split('/\s+/', trim((string) $provider->name));
        $firstName = $nameParts[0] ?? 'مقدم';
        $lastName  = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : $firstName;

        $orderRefId = $order->order_number . '-' . now()->timestamp . '-' . strtoupper(Str::random(4));

        $payload = [
            'order_reference_id' => $orderRefId,
            'order_number'       => $order->order_number,
            'total_amount'       => ['amount' => $amountFloat, 'currency' => 'SAR'],
            'description'        => mb_substr($order->plan_name . ' — منصة ثقة', 0, 200),
            'country_code'       => 'SA',
            'payment_type'       => 'PAY_BY_INSTALMENTS',
            'instalments'        => 4,
            'locale'             => 'ar_SA',
            'items'              => [[
                'reference_id'    => $order->order_number,
                'type'            => 'Digital',
                'name'            => mb_substr($order->plan_name, 0, 120),
                'sku'             => 'SUB-' . $order->duration_months . 'M',
                'quantity'        => 1,
                'unit_price'      => ['amount' => $amountFloat, 'currency' => 'SAR'],
                'discount_amount' => ['amount' => 0.0, 'currency' => 'SAR'],
                'tax_amount'      => ['amount' => 0.0, 'currency' => 'SAR'],
                'total_amount'    => ['amount' => $amountFloat, 'currency' => 'SAR'],
            ]],
            'consumer'           => [
                'first_name'   => $firstName,
                'last_name'    => $lastName,
                'phone_number' => $e164Phone,
                'email'        => $email,
            ],
            'billing_address'    => [
                'first_name'   => $firstName,
                'last_name'    => $lastName,
                'line1'        => 'Saudi Arabia',
                'city'         => $provider->city ?: 'Riyadh',
                'country_code' => 'SA',
                'phone_number' => $e164Phone,
            ],
            'shipping_address'   => [
                'first_name'   => $firstName,
                'last_name'    => $lastName,
                'line1'        => 'Saudi Arabia',
                'city'         => $provider->city ?: 'Riyadh',
                'country_code' => 'SA',
                'phone_number' => $e164Phone,
            ],
            'shipping_amount'    => ['amount' => 0.0, 'currency' => 'SAR'],
            'tax_amount'         => ['amount' => 0.0, 'currency' => 'SAR'],
            'discount'           => ['name' => 'None', 'amount' => ['amount' => 0.0, 'currency' => 'SAR']],
            'merchant_url'       => [
                'success'      => route('provider-sub-pay.tamara.callback', ['token' => $order->public_token, 'status' => 'success']),
                'failure'      => route('provider-sub-pay.tamara.callback', ['token' => $order->public_token, 'status' => 'failure']),
                'cancel'       => route('provider-sub-pay.tamara.callback', ['token' => $order->public_token, 'status' => 'cancel']),
                'notification' => route('provider-sub-pay.tamara.webhook'),
            ],
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->tamaraApiToken,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ])->timeout(20)->post("{$this->tamaraApiUrl}/checkout", $payload);

            $data = $response->json() ?? [];

            if ($response->successful() && ! empty($data['checkout_url'])) {
                $order->update([
                    'tamara_url'                => $data['checkout_url'],
                    'tamara_order_id'           => $data['order_id'] ?? null,
                    'tamara_checkout_id'        => $data['checkout_id'] ?? null,
                    'tamara_order_reference_id' => $orderRefId,
                    'tamara_status'             => $data['status'] ?? 'new',
                ]);

                return [
                    'success' => true,
                    'order'   => $order->fresh(),
                    'url'     => $data['checkout_url'],
                ];
            }

            $errMsg = $data['message'] ?? 'تعذر إنشاء جلسة الدفع عبر تمارا.';
            $order->update(['status' => ProviderSubscriptionOrder::STATUS_FAILED]);

            return ['success' => false, 'error' => $errMsg];
        } catch (\Throwable $e) {
            $order->update(['status' => ProviderSubscriptionOrder::STATUS_FAILED]);
            Log::error('Provider Subscription Tamara exception', ['order' => $order->order_number, 'error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'حدث خطأ أثناء الاتصال ببوابة تمارا.'];
        }
    }

    /**
     * Verify payment server-to-server with Telr or Tamara.
     * Upon verified payment, moves order to `paid_pending_approval` ONLY (does NOT auto-activate subscription!).
     */
    public function verifyAndMarkPaid(ProviderSubscriptionOrder $order, ?string $tamaraOrderId = null): array
    {
        if ($order->isPaidPendingApproval() || $order->isApproved()) {
            return ['success' => true, 'paid' => true, 'order' => $order];
        }

        if ($order->payment_method === 'tamara') {
            return $this->verifyTamaraOrder($order, $tamaraOrderId);
        }

        return $this->verifyTelrOrder($order);
    }

    private function verifyTelrOrder(ProviderSubscriptionOrder $order): array
    {
        if (! $order->telr_order_ref) {
            return ['success' => false, 'paid' => false, 'error' => 'لا يوجد مرجع دفع مسجل لهذا الطلب.'];
        }

        try {
            $response = Http::asForm()->timeout(15)->post($this->telrApiUrl, [
                'ivp_method'  => 'check',
                'ivp_store'   => $this->telrStoreId,
                'ivp_authkey' => $this->telrAuthKey,
                'order_ref'   => $order->telr_order_ref,
            ]);

            if (! $response->successful() || ! isset($response['order']['status'])) {
                return ['success' => false, 'paid' => false, 'error' => 'تعذر التحقق من حالة الدفع لدى البوابة.'];
            }

            $code = (int) $response['order']['status']['code'];
            $text = (string) ($response['order']['status']['text'] ?? '');

            if ($code === 3) {
                $this->markOrderPaidPendingApproval($order);
                return ['success' => true, 'paid' => true, 'order' => $order->fresh()];
            }

            if (in_array($code, [-1, -2], true)) {
                $order->update(['status' => ProviderSubscriptionOrder::STATUS_FAILED]);
            }

            return ['success' => true, 'paid' => false, 'error' => 'لم يتم تأكيد الدفع بعد (' . ($text ?: 'قيد الانتظار') . ').'];
        } catch (\Throwable $e) {
            Log::error('Provider Subscription Telr verify exception', ['order' => $order->order_number, 'error' => $e->getMessage()]);
            return ['success' => false, 'paid' => false, 'error' => 'تعذر التحقق من الدفع حالياً.'];
        }
    }

    private function verifyTamaraOrder(ProviderSubscriptionOrder $order, ?string $tamaraOrderId = null): array
    {
        $targetId = $tamaraOrderId ?: $order->tamara_order_id;
        if (! $targetId || ! $this->isTamaraConfigured()) {
            return ['success' => false, 'paid' => false, 'error' => 'لا يوجد رقم طلب تمارا للتحقق.'];
        }

        try {
            $resp = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->tamaraApiToken,
                'Accept'        => 'application/json',
            ])->timeout(15)->get("{$this->tamaraApiUrl}/orders/{$targetId}");

            $data = $resp->json() ?? [];
            if (! $resp->successful()) {
                return ['success' => false, 'paid' => false, 'error' => $data['message'] ?? 'تعذر الاستعلام من تمارا.'];
            }

            $status = strtolower((string) ($data['status'] ?? 'unknown'));

            if ($status === 'approved') {
                $authResp = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->tamaraApiToken,
                    'Accept'        => 'application/json',
                ])->timeout(15)->post("{$this->tamaraApiUrl}/orders/{$targetId}/authorise");

                $authData = $authResp->json() ?? [];
                if (! $authResp->successful()) {
                    return ['success' => false, 'paid' => false, 'error' => 'فشل اعتماد عملية تمارا.'];
                }

                $status       = strtolower((string) ($authData['status'] ?? 'authorised'));
                $autoCaptured = ! empty($authData['auto_captured']);

                if (! $autoCaptured && $status === 'authorised') {
                    $capAmount = (float) number_format((float) $order->amount, 2, '.', '');
                    $capResp = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $this->tamaraApiToken,
                        'Content-Type'  => 'application/json',
                        'Accept'        => 'application/json',
                    ])->timeout(15)->post("{$this->tamaraApiUrl}/payments/capture", [
                        'order_id'      => $targetId,
                        'total_amount'  => ['amount' => $capAmount, 'currency' => 'SAR'],
                        'shipping_info' => [
                            'shipped_at'       => now()->toIso8601String(),
                            'shipping_company' => 'Digital Subscription',
                        ],
                    ]);
                    if ($capResp->successful()) {
                        $status = strtolower((string) ($capResp->json('status') ?? 'fully_captured'));
                    }
                }
            }

            $paidStatuses   = ['authorised', 'fully_captured', 'partially_captured', 'captured'];
            $failedStatuses = ['declined', 'canceled', 'cancelled', 'expired', 'failed'];

            if (in_array($status, $paidStatuses, true)) {
                $this->markOrderPaidPendingApproval($order, [
                    'tamara_order_id' => $targetId,
                    'tamara_status'   => $status,
                ]);
                return ['success' => true, 'paid' => true, 'order' => $order->fresh()];
            }

            if (in_array($status, $failedStatuses, true)) {
                $order->update([
                    'status'        => ProviderSubscriptionOrder::STATUS_FAILED,
                    'tamara_status' => $status,
                ]);
            }

            return ['success' => true, 'paid' => false, 'error' => 'لم يتم تأكيد سداد تمارا بعد (' . $status . ').'];
        } catch (\Throwable $e) {
            Log::error('Provider Subscription Tamara verify exception', ['order' => $order->order_number, 'error' => $e->getMessage()]);
            return ['success' => false, 'paid' => false, 'error' => 'تعذر التحقق من تمارا حالياً.'];
        }
    }

    private function markOrderPaidPendingApproval(ProviderSubscriptionOrder $order, array $extra = []): void
    {
        DB::transaction(function () use ($order, $extra) {
            $locked = ProviderSubscriptionOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if (in_array($locked->status, [
                ProviderSubscriptionOrder::STATUS_PAID_PENDING_APPROVAL,
                ProviderSubscriptionOrder::STATUS_APPROVED,
            ], true)) {
                return;
            }

            $locked->update(array_merge($extra, [
                'status'  => ProviderSubscriptionOrder::STATUS_PAID_PENDING_APPROVAL,
                'paid_at' => $locked->paid_at ?: now(),
            ]));
        });
    }

    /**
     * Admin approves a paid subscription order -> atomically activates the provider's subscription!
     */
    public function approveOrder(ProviderSubscriptionOrder $order, int $adminId, ?string $notes = null): array
    {
        return DB::transaction(function () use ($order, $adminId, $notes) {
            $locked = ProviderSubscriptionOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($locked->isApproved()) {
                return ['success' => false, 'error' => 'تمت الموافقة على هذا الطلب وتفعيله مسبقاً.'];
            }

            if (! $locked->isPaidPendingApproval()) {
                return ['success' => false, 'error' => 'لا يمكن الموافقة على هذا الطلب لأنه غير مدفوع أو تم رفضه.'];
            }

            $provider = $locked->provider()->firstOrFail();

            $subscription = $this->subscriptionService->activate(
                $provider,
                $adminId,
                (int) $locked->duration_months,
                (float) $locked->amount,
                $locked->order_number,
                $notes ?: ('تم التفعيل بعد التحقق من الدفع الإلكتروني (' . $locked->paymentMethodLabel() . ') وموافقة الإدارة')
            );

            $locked->update([
                'status'                   => ProviderSubscriptionOrder::STATUS_APPROVED,
                'provider_subscription_id' => $subscription->id,
                'approved_by'              => $adminId,
                'approved_at'              => now(),
                'admin_notes'              => $notes,
            ]);

            return [
                'success'      => true,
                'order'        => $locked->fresh(),
                'subscription' => $subscription,
            ];
        });
    }

    /**
     * Admin rejects a paid subscription order (subscription stays inactive).
     */
    public function rejectOrder(ProviderSubscriptionOrder $order, int $adminId, ?string $notes = null): array
    {
        return DB::transaction(function () use ($order, $adminId, $notes) {
            $locked = ProviderSubscriptionOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($locked->isApproved()) {
                return ['success' => false, 'error' => 'تم اعتماد هذا الطلب مسبقاً ولا يمكن رفضه.'];
            }

            $locked->update([
                'status'      => ProviderSubscriptionOrder::STATUS_REJECTED,
                'approved_by' => $adminId,
                'approved_at' => now(),
                'admin_notes' => $notes ?: 'تم رفض طلب الاشتراك من الإدارة',
            ]);

            return ['success' => true, 'order' => $locked->fresh()];
        });
    }

    public function verifyTamaraWebhookRequest(Request $request): bool
    {
        if ($this->tamaraNotificationToken === '') {
            return false;
        }

        $token = $request->query('tamaraToken');
        if (! is_string($token) || trim($token) === '') {
            $token = $request->bearerToken();
        }
        if (! is_string($token) || trim($token) === '') {
            return false;
        }

        $parts = explode('.', trim($token));
        if (count($parts) !== 3) {
            return false;
        }

        [$headerB64, $payloadB64, $sigB64] = $parts;
        $header  = json_decode($this->base64UrlDecode($headerB64), true);
        $payload = json_decode($this->base64UrlDecode($payloadB64), true);

        if (! is_array($header) || ! is_array($payload) || (($header['alg'] ?? '') !== 'HS256')) {
            return false;
        }

        $expectedSig = hash_hmac('sha256', "{$headerB64}.{$payloadB64}", $this->tamaraNotificationToken, true);
        $actualSig   = $this->base64UrlDecode($sigB64);

        if (! hash_equals($expectedSig, $actualSig)) {
            return false;
        }

        $now = time();
        if (isset($payload['exp']) && is_numeric($payload['exp']) && $now > ((int) $payload['exp'] + 60)) {
            return false;
        }
        if (isset($payload['nbf']) && is_numeric($payload['nbf']) && $now < ((int) $payload['nbf'] - 60)) {
            return false;
        }

        return true;
    }

    private function formatPhoneE164(string $digits): string
    {
        if (str_starts_with($digits, '00966')) return '+966' . substr($digits, 5);
        if (str_starts_with($digits, '966') && strlen($digits) >= 12) return '+' . $digits;
        if (str_starts_with($digits, '05') && strlen($digits) === 10) return '+966' . substr($digits, 1);
        if (str_starts_with($digits, '5') && strlen($digits) === 9) return '+966' . $digits;
        return '+966500000000';
    }

    private function base64UrlDecode(string $input): string
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $input .= str_repeat('=', 4 - $remainder);
        }
        return (string) base64_decode(strtr($input, '-_', '+/'));
    }

    private function transliterateArabic(string $text): string
    {
        $map = [
            'أ' => 'A', 'إ' => 'E', 'آ' => 'A', 'ا' => 'A',
            'ب' => 'B', 'ت' => 'T', 'ث' => 'Th', 'ج' => 'J',
            'ح' => 'H', 'خ' => 'Kh', 'د' => 'D', 'ذ' => 'Dh',
            'ر' => 'R', 'ز' => 'Z', 'س' => 'S', 'ش' => 'Sh',
            'ص' => 'S', 'ض' => 'D', 'ط' => 'T', 'ظ' => 'Z',
            'ع' => 'A', 'غ' => 'Gh', 'ف' => 'F', 'ق' => 'Q',
            'ك' => 'K', 'ل' => 'L', 'م' => 'M', 'ن' => 'N',
            'ه' => 'H', 'و' => 'W', 'ي' => 'Y', 'ى' => 'A',
            'ة' => 'h', 'ء' => '', 'ئ' => 'Y', 'ؤ' => 'W', 'لا' => 'La',
        ];
        return trim((string) preg_replace('/[^a-zA-Z0-9\s]/', '', strtr($text, $map)));
    }
}
