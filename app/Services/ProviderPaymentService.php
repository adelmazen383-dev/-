<?php

namespace App\Services;

use App\Models\ProviderInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Isolated payment gateway adapter for Service Provider invoices.
 * Does NOT touch or modify core site TelrService, TamaraService, or InvoiceController.
 */
class ProviderPaymentService
{
    private string $telrStoreId;
    private string $telrAuthKey;
    private string $telrTestMode;
    private string $telrApiUrl = 'https://secure.telr.com/gateway/order.json';

    private string $tamaraApiToken;
    private string $tamaraNotificationToken;
    private string $tamaraApiUrl;

    public function __construct(private ProviderWalletService $walletService)
    {
        $this->telrStoreId  = (string) config('services.telr.store_id', '');
        $this->telrAuthKey  = (string) config('services.telr.auth_key', '');
        $this->telrTestMode = (string) config('services.telr.test_mode', '1');

        $this->tamaraApiToken          = (string) config('services.tamara.api_token', '');
        $this->tamaraNotificationToken = (string) config('services.tamara.notification_token', '');
        $this->tamaraApiUrl            = rtrim((string) config('services.tamara.api_url', 'https://api.tamara.co'), '/');
    }

    /**
     * Create a Telr session (Apple Pay / Card / Tabby) for a ProviderInvoice.
     */
    public function createTelrOrder(ProviderInvoice $invoice, string $method = 'standard'): array
    {
        if ($this->telrStoreId === '' || $this->telrAuthKey === '') {
            return ['success' => false, 'error' => 'بيانات بوابة الدفع غير مهيأة حالياً.'];
        }

        $calc  = $invoice->calculateTotalForMethod($method);
        $total = $calc['total'];
        if ($total <= 0) {
            return ['success' => false, 'error' => 'مبلغ الفاتورة غير صالح.'];
        }

        $latinName = $this->transliterateArabic((string) $invoice->customer_name);
        $parts     = preg_split('/\s+/', trim($latinName));
        $firstName = !empty($parts[0]) && strlen($parts[0]) >= 2 ? $parts[0] : 'Client';
        $surname   = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : 'Customer';
        if (strlen($surname) < 2) {
            $surname = 'Customer';
        }

        $cleanPhone = preg_replace('/[^0-9+]/', '', (string) $invoice->customer_phone);
        if (strlen($cleanPhone) < 7) {
            $cleanPhone = '0500000000';
        }

        $email = $this->resolveCustomerEmail($invoice, $cleanPhone);

        $payload = [
            'ivp_method'   => 'create',
            'ivp_store'    => $this->telrStoreId,
            'ivp_authkey'  => $this->telrAuthKey,
            'ivp_cart'     => $invoice->invoice_number . '-' . strtoupper($method) . '-' . time(),
            'ivp_test'     => $this->telrTestMode,
            'ivp_amount'   => number_format($total, 2, '.', ''),
            'ivp_currency' => 'SAR',
            'ivp_desc'     => 'Service Invoice ' . $invoice->invoice_number,
            'ivp_framed'   => 0,
            'return_auth'  => route('provider-pay.telr.callback', ['invoice' => $invoice->invoice_number, 'status' => 'success']),
            'return_decl'  => route('provider-pay.telr.callback', ['invoice' => $invoice->invoice_number, 'status' => 'declined']),
            'return_can'   => route('provider-pay.telr.callback', ['invoice' => $invoice->invoice_number, 'status' => 'cancelled']),
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

        $panelsMap = [
            'standard'  => 'card',
            'apple_pay' => 'applepay',
            'tabby'     => 'tabby',
        ];
        if (isset($panelsMap[$method])) {
            $payload['ivp_panels'] = $panelsMap[$method];
        }

        try {
            $response = Http::asForm()->timeout(15)->post($this->telrApiUrl, $payload);

            if ($response->successful() && isset($response['order']['url'])) {
                $invoice->update([
                    'payment_method'         => $method,
                    'gateway_fee_percentage' => $calc['fee_percentage'],
                    'gateway_fee_amount'     => $calc['fee_amount'],
                    'total_payable'          => $calc['total'],
                    'telr_order_ref'         => $response['order']['ref'],
                    'telr_transaction_url'   => $response['order']['url'],
                ]);

                return [
                    'success' => true,
                    'url'     => $response['order']['url'],
                    'ref'     => $response['order']['ref'],
                ];
            }

            $errMsg = $response['error']['message'] ?? ($response['error']['note'] ?? 'تعذر إنشاء رابط الدفع.');
            Log::error('Provider Telr Create Order failed', [
                'invoice' => $invoice->invoice_number,
                'status'  => $response->status(),
            ]);

            return ['success' => false, 'error' => $errMsg];
        } catch (\Throwable $e) {
            Log::error('Provider Telr Exception', ['invoice' => $invoice->invoice_number, 'error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'حدث خطأ أثناء الاتصال ببوابة الدفع.'];
        }
    }

    /**
     * Check an existing Telr order status for a ProviderInvoice.
     * Code 3 = Authorised (Paid), 2 = Pending, -1 = Declined, -2 = Cancelled.
     */
    public function checkTelrOrder(string $orderRef): array
    {
        try {
            $response = Http::asForm()->timeout(15)->post($this->telrApiUrl, [
                'ivp_method'  => 'check',
                'ivp_store'   => $this->telrStoreId,
                'ivp_authkey' => $this->telrAuthKey,
                'order_ref'   => $orderRef,
            ]);

            if ($response->successful() && isset($response['order']['status'])) {
                return [
                    'success' => true,
                    'code'    => (int) $response['order']['status']['code'],
                    'message' => (string) ($response['order']['status']['text'] ?? ''),
                ];
            }

            return ['success' => false, 'error' => 'استجابة غير متوقعة من بوابة الدفع.'];
        } catch (\Throwable $e) {
            Log::error('Provider Telr Check Exception', ['ref' => $orderRef, 'error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'تعذر التحقق من حالة الدفع حالياً.'];
        }
    }

    /**
     * Create a Tamara BNPL (Pay in 4) checkout session for a ProviderInvoice.
     */
    public function createTamaraCheckout(ProviderInvoice $invoice): array
    {
        if ($this->tamaraApiToken === '') {
            return ['success' => false, 'error' => 'بيانات بوابة تمارا غير مهيأة حالياً.'];
        }

        $calc  = $invoice->calculateTotalForMethod('tamara');
        $total = $calc['total'];
        if ($total <= 0) {
            return ['success' => false, 'error' => 'مبلغ الفاتورة غير صالح.'];
        }

        $amountFloat = (float) number_format($total, 2, '.', '');
        $cleanPhone  = preg_replace('/[^0-9]/', '', (string) $invoice->customer_phone);
        $e164Phone   = $this->formatPhoneE164($cleanPhone);
        $email       = $this->resolveCustomerEmail($invoice, $cleanPhone);

        $nameParts = preg_split('/\s+/', trim((string) $invoice->customer_name));
        $firstName = $nameParts[0] ?? 'عميل';
        $lastName  = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : $firstName;

        $orderRefId = $invoice->invoice_number . '-' . now()->timestamp . '-' . strtoupper(Str::random(4));

        $payload = [
            'order_reference_id' => $orderRefId,
            'order_number'       => $invoice->invoice_number,
            'total_amount'       => ['amount' => $amountFloat, 'currency' => 'SAR'],
            'description'        => mb_substr((string) $invoice->title, 0, 200),
            'country_code'       => 'SA',
            'payment_type'       => 'PAY_BY_INSTALMENTS',
            'instalments'        => 4,
            'locale'             => 'ar_SA',
            'items'              => [[
                'reference_id'    => $invoice->invoice_number,
                'type'            => 'Digital',
                'name'            => mb_substr((string) $invoice->title, 0, 120),
                'sku'             => $invoice->invoice_number,
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
                'city'         => 'Riyadh',
                'country_code' => 'SA',
                'phone_number' => $e164Phone,
            ],
            'shipping_address'   => [
                'first_name'   => $firstName,
                'last_name'    => $lastName,
                'line1'        => 'Saudi Arabia',
                'city'         => 'Riyadh',
                'country_code' => 'SA',
                'phone_number' => $e164Phone,
            ],
            'shipping_amount'    => ['amount' => 0.0, 'currency' => 'SAR'],
            'tax_amount'         => ['amount' => 0.0, 'currency' => 'SAR'],
            'discount'           => [
                'name'   => 'None',
                'amount' => ['amount' => 0.0, 'currency' => 'SAR'],
            ],
            'merchant_url'       => [
                'success'      => route('provider-pay.tamara.callback', ['token' => $invoice->public_token, 'status' => 'success']),
                'failure'      => route('provider-pay.tamara.callback', ['token' => $invoice->public_token, 'status' => 'failure']),
                'cancel'       => route('provider-pay.tamara.callback', ['token' => $invoice->public_token, 'status' => 'cancel']),
                'notification' => route('provider-pay.tamara.webhook'),
            ],
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->tamaraApiToken,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ])->timeout(20)->post("{$this->tamaraApiUrl}/checkout", $payload);

            $data = $response->json() ?? [];

            if ($response->successful() && !empty($data['checkout_url'])) {
                $invoice->update([
                    'payment_method'            => 'tamara',
                    'gateway_fee_percentage'    => $calc['fee_percentage'],
                    'gateway_fee_amount'        => $calc['fee_amount'],
                    'total_payable'             => $calc['total'],
                    'tamara_url'                => $data['checkout_url'],
                    'tamara_order_id'           => $data['order_id'] ?? null,
                    'tamara_checkout_id'        => $data['checkout_id'] ?? null,
                    'tamara_order_reference_id' => $orderRefId,
                    'tamara_status'             => $data['status'] ?? 'new',
                ]);

                return [
                    'success'  => true,
                    'url'      => $data['checkout_url'],
                    'order_id' => $data['order_id'] ?? null,
                ];
            }

            $errMsg = $data['message'] ?? 'تعذر إنشاء جلسة تمارا.';
            return ['success' => false, 'error' => $errMsg];
        } catch (\Throwable $e) {
            Log::error('Provider Tamara Checkout Exception', ['invoice' => $invoice->invoice_number, 'error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'حدث خطأ أثناء الاتصال ببوابة تمارا.'];
        }
    }

    /**
     * Verify, authorise, and capture (if needed) a ProviderInvoice Tamara order.
     */
    public function verifyTamaraOrder(ProviderInvoice $invoice, ?string $orderId = null): array
    {
        if ($this->tamaraApiToken === '') {
            return ['success' => false, 'paid' => false, 'status' => 'error', 'error' => 'Tamara not configured.'];
        }

        $targetOrderId = $orderId ?: $invoice->tamara_order_id;
        if (!$targetOrderId) {
            return ['success' => false, 'paid' => false, 'status' => 'unknown', 'error' => 'لا يوجد رقم طلب تمارا.'];
        }

        try {
            $resp = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->tamaraApiToken,
                'Accept'        => 'application/json',
            ])->timeout(15)->get("{$this->tamaraApiUrl}/orders/{$targetOrderId}");

            $data = $resp->json() ?? [];
            if (!$resp->successful()) {
                return ['success' => false, 'paid' => false, 'status' => 'error', 'error' => $data['message'] ?? 'تعذر الاستعلام من تمارا.'];
            }

            $status = strtolower((string) ($data['status'] ?? 'unknown'));

            if ($status === 'approved') {
                $authResp = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->tamaraApiToken,
                    'Accept'        => 'application/json',
                ])->timeout(15)->post("{$this->tamaraApiUrl}/orders/{$targetOrderId}/authorise");

                $authData = $authResp->json() ?? [];
                if (!$authResp->successful()) {
                    return ['success' => false, 'paid' => false, 'order_id' => $targetOrderId, 'status' => 'approved', 'error' => 'فشل اعتماد الطلب لدى تمارا.'];
                }

                $status       = strtolower((string) ($authData['status'] ?? 'authorised'));
                $autoCaptured = !empty($authData['auto_captured']);

                if (!$autoCaptured && $status === 'authorised') {
                    $capAmount = (float) number_format((float) $invoice->total_payable, 2, '.', '');
                    $capResp = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $this->tamaraApiToken,
                        'Content-Type'  => 'application/json',
                        'Accept'        => 'application/json',
                    ])->timeout(15)->post("{$this->tamaraApiUrl}/payments/capture", [
                        'order_id'     => $targetOrderId,
                        'total_amount' => ['amount' => $capAmount, 'currency' => 'SAR'],
                        'shipping_info' => [
                            'shipped_at'       => now()->toIso8601String(),
                            'shipping_company' => 'Digital Service',
                        ],
                    ]);
                    if ($capResp->successful()) {
                        $status = strtolower((string) ($capResp->json('status') ?? 'fully_captured'));
                    }
                }
            }

            $paidStatuses   = ['authorised', 'fully_captured', 'partially_captured', 'captured'];
            $failedStatuses = ['declined', 'canceled', 'cancelled', 'expired', 'failed'];

            return [
                'success'  => true,
                'paid'     => in_array($status, $paidStatuses, true),
                'failed'   => in_array($status, $failedStatuses, true),
                'order_id' => $targetOrderId,
                'status'   => $status,
            ];
        } catch (\Throwable $e) {
            Log::error('Provider Tamara Verify Exception', ['order_id' => $targetOrderId, 'error' => $e->getMessage()]);
            return ['success' => false, 'paid' => false, 'status' => 'error', 'error' => $e->getMessage()];
        }
    }

    /**
     * Verify Tamara webhook HS256 JWT token.
     */
    public function verifyTamaraWebhookRequest(Request $request): bool
    {
        if ($this->tamaraNotificationToken === '') {
            return false;
        }

        $token = $request->query('tamaraToken');
        if (!is_string($token) || trim($token) === '') {
            $token = $request->bearerToken();
        }
        if (!is_string($token) || trim($token) === '') {
            return false;
        }

        $parts = explode('.', trim($token));
        if (count($parts) !== 3) {
            return false;
        }

        [$headerB64, $payloadB64, $sigB64] = $parts;
        $header  = json_decode($this->base64UrlDecode($headerB64), true);
        $payload = json_decode($this->base64UrlDecode($payloadB64), true);

        if (!is_array($header) || !is_array($payload) || (($header['alg'] ?? '') !== 'HS256')) {
            return false;
        }

        $expectedSig = hash_hmac('sha256', "{$headerB64}.{$payloadB64}", $this->tamaraNotificationToken, true);
        $actualSig   = $this->base64UrlDecode($sigB64);

        if (!hash_equals($expectedSig, $actualSig)) {
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

    /**
     * Mark a ProviderInvoice as verified & paid AND atomically credit the provider's wallet.
     */
    public function markPaidAndCreditWallet(
        ProviderInvoice $invoice,
        ?string $method = null,
        ?int $adminId = null,
        array $extraFields = []
    ): ProviderInvoice {
        DB::transaction(function () use ($invoice, $method, $adminId, $extraFields) {
            $locked = ProviderInvoice::whereKey($invoice->id)->lockForUpdate()->firstOrFail();

            $updates = array_merge($extraFields, [
                'status'      => 'paid',
                'paid_at'     => $locked->paid_at ?: now(),
                'verified_at' => $locked->verified_at ?: now(),
            ]);

            if ($method) {
                $updates['payment_method'] = $method;
            }
            if ($adminId && !$locked->verified_by) {
                $updates['verified_by'] = $adminId;
            }

            $locked->update($updates);
            $this->walletService->creditFromInvoice($locked, $adminId);
        });

        return $invoice->fresh();
    }

    private function formatPhoneE164(string $digits): string
    {
        if (str_starts_with($digits, '00966')) {
            return '+966' . substr($digits, 5);
        }
        if (str_starts_with($digits, '966') && strlen($digits) >= 12) {
            return '+' . $digits;
        }
        if (str_starts_with($digits, '05') && strlen($digits) === 10) {
            return '+966' . substr($digits, 1);
        }
        if (str_starts_with($digits, '5') && strlen($digits) === 9) {
            return '+966' . $digits;
        }
        return '+966500000000';
    }

    private function resolveCustomerEmail(ProviderInvoice $invoice, string $cleanPhone): string
    {
        if (!empty($invoice->customer_email) && filter_var($invoice->customer_email, FILTER_VALIDATE_EMAIL)) {
            return $invoice->customer_email;
        }
        $digits = preg_replace('/[^0-9]/', '', $cleanPhone);
        return !empty($digits)
            ? 'pclient.' . substr($digits, -8) . '@mail.thiqa.me'
            : 'pclient.' . substr(md5($invoice->invoice_number), 0, 10) . '@mail.thiqa.me';
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
