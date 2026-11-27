<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TamaraService
{
    private string $apiToken;
    private string $publicKey;
    private string $notificationToken;
    private string $apiUrl;

    public function __construct()
    {
        $this->apiToken          = (string) config('services.tamara.api_token', '');
        $this->publicKey         = (string) config('services.tamara.public_key', '');
        $this->notificationToken = (string) config('services.tamara.notification_token', '');
        $this->apiUrl            = rtrim((string) config('services.tamara.api_url', 'https://api.tamara.co'), '/');
    }

    public function isConfigured(): bool
    {
        return $this->apiToken !== '';
    }

    public function hasNotificationToken(): bool
    {
        return $this->notificationToken !== '';
    }

    /**
     * Create a Tamara checkout session (Pay in 4).
     */
    public function createCheckoutSession(Invoice $invoice): array
    {
        if (!$this->isConfigured()) {
            Log::warning('Tamara API token is not configured.');
            return [
                'success' => false,
                'error'   => 'بيانات الربط مع تمارا غير مهيأة في الإعدادات.',
            ];
        }

        $totalAmount = $this->calculateTamaraTotal($invoice);
        if ($totalAmount <= 0) {
            return [
                'success' => false,
                'error'   => 'إجمالي مبلغ الفاتورة غير صالح لإنشاء جلسة تمارا.',
            ];
        }

        $formattedAmt = number_format($totalAmount, 2, '.', '');
        $amountFloat  = (float) $formattedAmt;

        $cleanPhone = preg_replace('/[^0-9]/', '', (string) $invoice->phone);
        $e164Phone  = $this->formatPhoneE164($cleanPhone);

        [$firstName, $lastName] = $this->splitBuyerName((string) $invoice->customer_name);

        $token            = $invoice->booking_card_token;
        $orderReferenceId = $invoice->invoice_number . '-' . now()->timestamp . '-' . strtoupper(Str::random(4));
        $description      = 'توثيق عقد وحدة رقم ' . ($invoice->suite_number ?: ($invoice->apartment_number ?: $invoice->invoice_number));
        $buyerEmail       = $this->resolveBuyerEmail($invoice, $cleanPhone);
        $city             = $this->resolveCity((string) $invoice->property_location);

        $payload = [
            'order_reference_id' => $orderReferenceId,
            'order_number'       => $invoice->invoice_number,
            'total_amount'       => [
                'amount'   => $amountFloat,
                'currency' => 'SAR',
            ],
            'description'        => $description,
            'country_code'       => 'SA',
            'payment_type'       => 'PAY_BY_INSTALMENTS',
            'instalments'        => 4,
            'locale'             => 'ar_SA',
            'items'              => [
                [
                    'reference_id'    => $invoice->invoice_number,
                    'type'            => 'Digital',
                    'name'            => $description,
                    'sku'             => $invoice->invoice_number,
                    'quantity'        => 1,
                    'unit_price'      => [
                        'amount'   => $amountFloat,
                        'currency' => 'SAR',
                    ],
                    'discount_amount' => [
                        'amount'   => 0.0,
                        'currency' => 'SAR',
                    ],
                    'tax_amount'      => [
                        'amount'   => 0.0,
                        'currency' => 'SAR',
                    ],
                    'total_amount'    => [
                        'amount'   => $amountFloat,
                        'currency' => 'SAR',
                    ],
                ],
            ],
            'consumer'           => [
                'first_name'   => $firstName,
                'last_name'    => $lastName,
                'phone_number' => $e164Phone,
                'email'        => $buyerEmail,
            ],
            'billing_address'    => [
                'first_name'   => $firstName,
                'last_name'    => $lastName,
                'line1'        => 'King Fahd Road',
                'city'         => $city,
                'country_code' => 'SA',
                'phone_number' => $e164Phone,
            ],
            'shipping_address'   => [
                'first_name'   => $firstName,
                'last_name'    => $lastName,
                'line1'        => 'King Fahd Road',
                'city'         => $city,
                'country_code' => 'SA',
                'phone_number' => $e164Phone,
            ],
            'discount_amount'    => [
                'amount'   => 0.0,
                'currency' => 'SAR',
            ],
            'shipping_amount'    => [
                'amount'   => 0.0,
                'currency' => 'SAR',
            ],
            'tax_amount'         => [
                'amount'   => 0.0,
                'currency' => 'SAR',
            ],
            'merchant_url'       => [
                'success'      => route('payment.tamara.callback', ['token' => $token, 'status' => 'success']),
                'failure'      => route('payment.tamara.callback', ['token' => $token, 'status' => 'failure']),
                'cancel'       => route('payment.tamara.callback', ['token' => $token, 'status' => 'cancel']),
                'notification' => route('payment.tamara.webhook'),
            ],
            'platform'           => 'Laravel',
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ])->withOptions([
                'curl' => [
                    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                ],
            ])->timeout(30)->post("{$this->apiUrl}/checkout", $payload);

            $data = $response->json() ?? [];

            Log::info('Tamara Checkout Session Response', [
                'invoice'            => $invoice->invoice_number,
                'order_reference_id' => $orderReferenceId,
                'http_status'        => $response->status(),
                'order_id'           => $data['order_id'] ?? null,
                'checkout_id'        => $data['checkout_id'] ?? null,
                'tamara_status'      => $data['status'] ?? null,
            ]);

            if ($response->successful() && !empty($data['checkout_url'])) {
                return [
                    'success'            => true,
                    'web_url'            => $data['checkout_url'],
                    'order_id'           => $data['order_id'] ?? null,
                    'checkout_id'        => $data['checkout_id'] ?? null,
                    'order_reference_id' => $orderReferenceId,
                    'status'             => $data['status'] ?? 'new',
                ];
            }

            $errorMessage = $this->extractApiError($data, 'تعذر إنشاء جلسة تمارا');

            return [
                'success' => false,
                'error'   => 'تمارا: ' . $errorMessage,
            ];
        } catch (\Throwable $e) {
            Log::error('Tamara Checkout Exception', [
                'invoice' => $invoice->invoice_number,
                'error'   => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error'   => 'تعذر الاتصال بخدمة تمارا: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get order details from Tamara by Tamara order ID (GET /orders/{order_id}).
     */
    public function getOrder(string $orderId): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'بيانات الربط مع تمارا غير مهيأة.'];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Accept'        => 'application/json',
            ])->withOptions([
                'curl' => [
                    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                ],
            ])->timeout(20)->get("{$this->apiUrl}/orders/{$orderId}");

            $data = $response->json() ?? [];

            return [
                'success' => $response->successful(),
                'status'  => $data['status'] ?? null,
                'data'    => $data,
                'error'   => $response->successful() ? null : $this->extractApiError($data, 'تعذر جلب تفاصيل الطلب من تمارا'),
            ];
        } catch (\Throwable $e) {
            Log::error('Tamara Get Order Exception', ['order_id' => $orderId, 'error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get order details from Tamara by merchant order_reference_id.
     */
    public function getOrderByReference(string $orderReferenceId): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'بيانات الربط مع تمارا غير مهيأة.'];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Accept'        => 'application/json',
            ])->withOptions([
                'curl' => [
                    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                ],
            ])->timeout(20)->get("{$this->apiUrl}/merchants/orders/reference-id/{$orderReferenceId}");

            $data = $response->json() ?? [];

            return [
                'success'  => $response->successful(),
                'order_id' => $data['order_id'] ?? null,
                'status'   => $data['status'] ?? null,
                'data'     => $data,
                'error'    => $response->successful() ? null : $this->extractApiError($data, 'تعذر جلب الطلب بالرقم المرجعي من تمارا'),
            ];
        } catch (\Throwable $e) {
            Log::error('Tamara Get Order By Reference Exception', ['order_reference_id' => $orderReferenceId, 'error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Authorise an approved Tamara order (POST /orders/{order_id}/authorise).
     */
    public function authoriseOrder(string $orderId): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'بيانات الربط مع تمارا غير مهيأة.'];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ])->withOptions([
                'curl' => [
                    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                ],
            ])->timeout(20)->post("{$this->apiUrl}/orders/{$orderId}/authorise");

            $data = $response->json() ?? [];

            Log::info('Tamara Authorise Order Response', [
                'order_id'      => $orderId,
                'http_status'   => $response->status(),
                'tamara_status' => $data['status'] ?? null,
                'auto_captured' => $data['auto_captured'] ?? null,
            ]);

            return [
                'success'       => $response->successful(),
                'status'        => $data['status'] ?? null,
                'auto_captured' => (bool) ($data['auto_captured'] ?? false),
                'data'          => $data,
                'error'         => $response->successful() ? null : $this->extractApiError($data, 'تعذر اعتماد طلب تمارا'),
            ];
        } catch (\Throwable $e) {
            Log::error('Tamara Authorise Exception', ['order_id' => $orderId, 'error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Capture an authorised Tamara order (POST /payments/capture).
     */
    public function captureOrder(string $orderId, Invoice $invoice): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'بيانات الربط مع تمارا غير مهيأة.'];
        }

        $totalAmount = round((float) ($invoice->total_payable > 0 ? $invoice->total_payable : $this->calculateTamaraTotal($invoice)), 2);
        $description = 'توثيق عقد وحدة رقم ' . ($invoice->suite_number ?: ($invoice->apartment_number ?: $invoice->invoice_number));

        $payload = [
            'order_id'      => $orderId,
            'total_amount'  => [
                'amount'   => $totalAmount,
                'currency' => 'SAR',
            ],
            'shipping_info' => [
                'shipped_at'       => now()->toIso8601String(),
                'shipping_company' => 'منصة توثيق العقود',
                'tracking_number'  => $invoice->invoice_number,
            ],
            'items'         => [
                [
                    'name'         => $description,
                    'type'         => 'Digital',
                    'quantity'     => 1,
                    'reference_id' => $invoice->invoice_number,
                    'sku'          => $invoice->invoice_number,
                    'unit_price'   => [
                        'amount'   => $totalAmount,
                        'currency' => 'SAR',
                    ],
                    'total_amount' => [
                        'amount'   => $totalAmount,
                        'currency' => 'SAR',
                    ],
                ],
            ],
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ])->withOptions([
                'curl' => [
                    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                ],
            ])->timeout(20)->post("{$this->apiUrl}/payments/capture", $payload);

            $data = $response->json() ?? [];

            Log::info('Tamara Capture Order Response', [
                'order_id'      => $orderId,
                'http_status'   => $response->status(),
                'tamara_status' => $data['status'] ?? null,
                'capture_id'    => $data['capture_id'] ?? null,
            ]);

            return [
                'success'    => $response->successful(),
                'status'     => $data['status'] ?? null,
                'capture_id' => $data['capture_id'] ?? null,
                'data'       => $data,
                'error'      => $response->successful() ? null : $this->extractApiError($data, 'تعذر تحصيل مبلغ طلب تمارا'),
            ];
        } catch (\Throwable $e) {
            Log::error('Tamara Capture Exception', ['order_id' => $orderId, 'error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Verify, authorise, and capture (if needed) an invoice's Tamara order via Tamara API.
     */
    public function verifyOrderPayment(Invoice $invoice, ?string $orderId = null): array
    {
        $targetOrderId = $orderId ?: $invoice->tamara_order_id;
        $orderInfo     = null;

        if (!$targetOrderId && !empty($invoice->tamara_order_reference_id)) {
            $refLookup = $this->getOrderByReference($invoice->tamara_order_reference_id);
            if ($refLookup['success']) {
                $targetOrderId = $refLookup['order_id'] ?? ($refLookup['data']['order_id'] ?? null);
                $orderInfo     = $refLookup;
            }
        }

        if (!$targetOrderId) {
            return [
                'paid'     => false,
                'failed'   => false,
                'status'   => null,
                'order_id' => null,
                'error'    => 'لم يتم العثور على معرف طلب تمارا لهذه الفاتورة.',
            ];
        }

        if ($orderInfo === null) {
            $orderInfo = $this->getOrder($targetOrderId);
        }

        if (!$orderInfo['success']) {
            return [
                'paid'     => false,
                'failed'   => false,
                'status'   => null,
                'order_id' => $targetOrderId,
                'error'    => $orderInfo['error'] ?? 'تعذر التحقق من حالة الطلب لدى تمارا.',
            ];
        }

        $orderStatus = strtolower((string) ($orderInfo['status'] ?? $orderInfo['data']['status'] ?? ''));

        if ($orderStatus === 'approved') {
            $authResult = $this->authoriseOrder($targetOrderId);
            if ($authResult['success']) {
                $orderStatus = strtolower((string) ($authResult['status'] ?? 'authorised'));

                if (!$authResult['auto_captured'] && $orderStatus === 'authorised') {
                    $captureResult = $this->captureOrder($targetOrderId, $invoice);
                    if ($captureResult['success'] && !empty($captureResult['status'])) {
                        $orderStatus = strtolower((string) $captureResult['status']);
                    }
                }
            }
        }

        $paidStatuses   = ['authorised', 'fully_captured', 'partially_captured'];
        $failedStatuses = ['declined', 'expired', 'canceled', 'cancelled'];

        return [
            'paid'     => in_array($orderStatus, $paidStatuses, true),
            'failed'   => in_array($orderStatus, $failedStatuses, true),
            'status'   => $orderStatus ?: null,
            'order_id' => $targetOrderId,
            'data'     => $orderInfo['data'] ?? [],
            'error'    => null,
        ];
    }

    /**
     * Extract Tamara JWT token from webhook request (?tamaraToken=... or Bearer header).
     */
    public function extractWebhookToken(Request $request): ?string
    {
        $queryToken = $request->query('tamaraToken');
        if (is_string($queryToken) && trim($queryToken) !== '') {
            return trim($queryToken);
        }

        $bearer = $request->bearerToken();
        if (is_string($bearer) && trim($bearer) !== '') {
            return trim($bearer);
        }

        return null;
    }

    /**
     * Verify incoming webhook request using Tamara HS256 JWT token.
     */
    public function verifyWebhookRequest(Request $request): bool
    {
        $token = $this->extractWebhookToken($request);
        if ($token === null) {
            return false;
        }

        return $this->verifyWebhookToken($token);
    }

    /**
     * Verify Tamara notification HS256 JWT signature and standard claims.
     */
    public function verifyWebhookToken(string $token): bool
    {
        if (!$this->hasNotificationToken() || trim($token) === '') {
            return false;
        }

        $parts = explode('.', trim($token));
        if (count($parts) !== 3) {
            return false;
        }

        [$headerB64, $payloadB64, $signatureB64] = $parts;

        $headerJson    = $this->base64UrlDecode($headerB64);
        $payloadJson   = $this->base64UrlDecode($payloadB64);
        $actualSig     = $this->base64UrlDecode($signatureB64);

        if ($headerJson === false || $payloadJson === false || $actualSig === false) {
            return false;
        }

        $header  = json_decode($headerJson, true);
        $payload = json_decode($payloadJson, true);

        if (!is_array($header) || !is_array($payload)) {
            return false;
        }

        if (($header['alg'] ?? null) !== 'HS256') {
            return false;
        }

        $expectedSig = hash_hmac('sha256', "{$headerB64}.{$payloadB64}", $this->notificationToken, true);
        if (!hash_equals($expectedSig, $actualSig)) {
            return false;
        }

        $leeway = 60;
        $now    = time();

        if (isset($payload['exp']) && ((int) $payload['exp'] + $leeway) < $now) {
            return false;
        }

        if (isset($payload['nbf']) && ((int) $payload['nbf'] - $leeway) > $now) {
            return false;
        }

        if (isset($payload['iss']) && $payload['iss'] !== 'Tamara') {
            return false;
        }

        return true;
    }

    public function calculateTamaraTotal(Invoice $invoice): float
    {
        $baseAmount    = (float) $invoice->base_amount;
        $feePercentage = Setting::getFeePercentage('tamara');
        $feeAmount     = $feePercentage > 0 ? round(($baseAmount * $feePercentage) / 100, 2) : 0.00;

        return round($baseAmount + $feeAmount, 2);
    }

    private function formatPhoneE164(string $cleanPhone): string
    {
        if (str_starts_with($cleanPhone, '00966') && strlen($cleanPhone) >= 14) {
            return '+' . substr($cleanPhone, 2);
        }
        if (str_starts_with($cleanPhone, '05') && strlen($cleanPhone) === 10) {
            return '+966' . substr($cleanPhone, 1);
        }
        if (str_starts_with($cleanPhone, '5') && strlen($cleanPhone) === 9) {
            return '+966' . $cleanPhone;
        }
        if (str_starts_with($cleanPhone, '966') && strlen($cleanPhone) >= 12) {
            return '+' . $cleanPhone;
        }

        return '+966500000000';
    }

    private function splitBuyerName(string $rawName): array
    {
        $buyerName = trim($rawName) ?: 'عميل';
        $nameParts = preg_split('/\s+/', $buyerName, 2) ?: [$buyerName];
        $firstName = trim((string) ($nameParts[0] ?? 'عميل')) ?: 'عميل';
        $lastName  = trim((string) ($nameParts[1] ?? $firstName)) ?: $firstName;

        return [$firstName, $lastName];
    }

    private function resolveCity(string $location): string
    {
        $cities = [
            'جدة'     => 'Jeddah',
            'مكة'     => 'Mecca',
            'المدينة' => 'Medina',
            'الدمام'  => 'Dammam',
            'الخبر'   => 'Khobar',
            'الرياض'  => 'Riyadh',
        ];

        foreach ($cities as $ar => $en) {
            if (mb_stripos($location, $ar) !== false) {
                return $en;
            }
        }

        return 'Riyadh';
    }

    private function extractApiError(array $data, string $fallback): string
    {
        if (!empty($data['message']) && is_string($data['message'])) {
            return $data['message'];
        }
        if (!empty($data['error']) && is_string($data['error'])) {
            return $data['error'];
        }
        if (!empty($data['errors']) && is_array($data['errors'])) {
            $first = reset($data['errors']);
            if (is_array($first) && !empty($first['error_code'])) {
                return (string) $first['error_code'];
            }
            if (is_string($first)) {
                return $first;
            }
        }

        return $fallback;
    }

    private function base64UrlDecode(string $input): string|false
    {
        $remainder = strlen($input) % 4;
        if ($remainder > 0) {
            $input .= str_repeat('=', 4 - $remainder);
        }

        return base64_decode(strtr($input, '-_', '+/'), true);
    }

    /**
     * Helper to resolve unique customer email.
     */
    private function resolveBuyerEmail(Invoice $invoice, string $cleanPhone): string
    {
        $badDomains = ['example.com', 'test.com'];
        $candidates = [
            $invoice->user?->email,
            $invoice->email ?? null,
        ];

        foreach ($candidates as $candidate) {
            if (!empty($candidate)
                && filter_var($candidate, FILTER_VALIDATE_EMAIL)
                && !collect($badDomains)->contains(fn ($d) => str_contains(strtolower($candidate), $d))
            ) {
                return $candidate;
            }
        }

        $phonePart = !empty($cleanPhone) ? substr($cleanPhone, -8) : substr(md5($invoice->invoice_number), 0, 8);
        return "customer.{$phonePart}@mail.thiqa.me";
    }
}
