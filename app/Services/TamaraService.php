<?php

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TamaraService
{
    private string $apiToken;
    private string $publicKey;
    private string $notificationToken;
    private string $apiUrl;

    public function __construct()
    {
        $this->apiToken          = (string) config('services.tamara.api_token', env('TAMARA_API_TOKEN', ''));
        $this->publicKey         = (string) config('services.tamara.public_key', env('TAMARA_PUBLIC_KEY', ''));
        $this->notificationToken = (string) config('services.tamara.notification_token', env('TAMARA_NOTIFICATION_TOKEN', ''));
        $this->apiUrl            = rtrim((string) config('services.tamara.api_url', env('TAMARA_API_URL', 'https://api.tamara.co')), '/');
    }

    /**
     * Create a Tamara checkout session (Pay in 4).
     */
    public function createCheckoutSession(Invoice $invoice): array
    {
        $baseAmount    = (float) $invoice->base_amount;
        $feePercentage = \App\Models\Setting::getFeePercentage('tamara');
        $feeAmount     = $feePercentage > 0 ? round(($baseAmount * $feePercentage) / 100, 2) : 0.00;
        $totalAmount   = $baseAmount + $feeAmount;
        $formattedAmt  = number_format($totalAmount, 2, '.', '');

        // Format Phone to E.164 (+9665XXXXXXXX)
        $cleanPhone = preg_replace('/[^0-9]/', '', (string) $invoice->phone);
        if (str_starts_with($cleanPhone, '05') && strlen($cleanPhone) === 10) {
            $e164Phone = '+966' . substr($cleanPhone, 1);
        } elseif (str_starts_with($cleanPhone, '5') && strlen($cleanPhone) === 9) {
            $e164Phone = '+966' . $cleanPhone;
        } elseif (str_starts_with($cleanPhone, '966') && strlen($cleanPhone) >= 12) {
            $e164Phone = '+' . $cleanPhone;
        } else {
            $e164Phone = '+966500000000';
        }

        $buyerName  = trim((string) ($invoice->customer_name ?: 'عميل'));
        $nameParts  = explode(' ', $buyerName, 2);
        $firstName  = $nameParts[0] ?? $buyerName;
        $lastName   = $nameParts[1] ?? $firstName;

        $token      = $invoice->booking_card_token;
        $successUrl = url("/payment/tamara/callback?token={$token}&status=success");
        $cancelUrl  = url("/payment/tamara/callback?token={$token}&status=cancel");
        $failureUrl = url("/payment/tamara/callback?token={$token}&status=failure");
        $notifyUrl  = url("/payment/tamara/webhook");

        $description = 'توثيق عقد وحدة رقم ' . ($invoice->suite_number ?: ($invoice->apartment_number ?: $invoice->invoice_number));

        // Resolve email
        $buyerEmail = $this->resolveBuyerEmail($invoice, $cleanPhone);

        $payload = [
            'order_reference_id' => $invoice->invoice_number,
            'order_number'       => $invoice->invoice_number,
            'total_amount'       => [
                'amount'   => $formattedAmt,
                'currency' => 'SAR',
            ],
            'description'        => $description,
            'country_code'       => 'SA',
            'payment_type'       => 'PAY_BY_INSTALMENTS',
            'instalments'        => 4,
            'locale'             => 'ar_SA',
            'items'              => [
                [
                    'reference_id' => $invoice->invoice_number,
                    'type'         => 'Digital',
                    'name'         => $description,
                    'sku'          => $invoice->invoice_number,
                    'quantity'     => 1,
                    'unit_price'   => [
                        'amount'   => $formattedAmt,
                        'currency' => 'SAR',
                    ],
                    'discount_amount' => [
                        'amount'   => '0.00',
                        'currency' => 'SAR',
                    ],
                    'tax_amount' => [
                        'amount'   => '0.00',
                        'currency' => 'SAR',
                    ],
                    'total_amount' => [
                        'amount'   => $formattedAmt,
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
                'city'         => 'Riyadh',
                'country_code' => 'SA',
                'phone_number' => $e164Phone,
            ],
            'shipping_address'   => [
                'first_name'   => $firstName,
                'last_name'    => $lastName,
                'line1'        => 'King Fahd Road',
                'city'         => 'Riyadh',
                'country_code' => 'SA',
                'phone_number' => $e164Phone,
            ],
            'discount'           => [
                'amount'   => '0.00',
                'currency' => 'SAR',
            ],
            'shipping_amount'    => [
                'amount'   => '0.00',
                'currency' => 'SAR',
            ],
            'tax_amount'         => [
                'amount'   => '0.00',
                'currency' => 'SAR',
            ],
            'merchant_url'       => [
                'success'      => $successUrl,
                'failure'      => $failureUrl,
                'cancel'       => $cancelUrl,
                'notification' => $notifyUrl,
            ],
            'platform'           => 'Laravel',
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Content-Type'  => 'application/json',
            ])->timeout(30)->post("{$this->apiUrl}/checkout", $payload);

            $data = $response->json();

            Log::info('Tamara Checkout Session Created', [
                'invoice' => $invoice->invoice_number,
                'status'  => $response->status(),
                'body'    => $data,
            ]);

            if ($response->successful() && !empty($data['checkout_url'])) {
                return [
                    'success'     => true,
                    'web_url'     => $data['checkout_url'],
                    'checkout_id' => $data['checkout_id'] ?? null,
                ];
            }

            $errorMessage = $data['message'] ?? ($data['error'] ?? 'تعذر إنشاء جلسة تمارا');
            return [
                'success' => false,
                'error'   => 'تمارا: ' . $errorMessage,
            ];
        } catch (\Exception $e) {
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
     * Get order details from Tamara by order ID.
     */
    public function getOrder(string $orderId): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
            ])->timeout(20)->get("{$this->apiUrl}/merchants/orders/{$orderId}");

            return [
                'success' => $response->successful(),
                'status'  => $response->json('status'),
                'data'    => $response->json(),
            ];
        } catch (\Exception $e) {
            Log::error('Tamara Get Order Exception', ['order_id' => $orderId, 'error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Authorise / Capture an order.
     */
    public function authoriseOrder(string $orderId): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Content-Type'  => 'application/json',
            ])->timeout(20)->post("{$this->apiUrl}/merchants/orders/{$orderId}/authorise");

            return [
                'success' => $response->successful(),
                'status'  => $response->json('status'),
                'data'    => $response->json(),
            ];
        } catch (\Exception $e) {
            Log::error('Tamara Authorise Exception', ['order_id' => $orderId, 'error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Verify webhook notification token.
     */
    public function verifyWebhookToken(string $token): bool
    {
        return $token === $this->notificationToken;
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
                && !collect($badDomains)->contains(fn($d) => str_contains(strtolower($candidate), $d))
            ) {
                return $candidate;
            }
        }

        $phonePart = !empty($cleanPhone) ? substr($cleanPhone, -8) : substr(md5($invoice->invoice_number), 0, 8);
        return "customer.{$phonePart}@mail.thiqa.me";
    }
}
