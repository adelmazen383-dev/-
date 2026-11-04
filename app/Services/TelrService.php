<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Invoice;

class TelrService
{
    private string $storeId;
    private string $authKey;
    private string $testMode;
    private string $apiUrl = 'https://secure.telr.com/gateway/order.json';

    public function __construct()
    {
        $this->storeId  = (string) (config('services.telr.store_id') ?? '');
        $this->authKey  = (string) (config('services.telr.auth_key') ?? '');
        $this->testMode = (string) (config('services.telr.test_mode') ?? '1');
    }

    /**
     * Create a payment order on Telr and return the transaction URL.
     */
    public function createOrder(Invoice $invoice, ?string $method = null): array
    {
        if (empty($this->storeId) || empty($this->authKey)) {
            Log::error('Telr credentials missing — check TELR_STORE_ID and TELR_AUTH_KEY in .env');
            return ['success' => false, 'error' => 'Telr credentials not configured.'];
        }

        $chosenMethod  = $method ?? $invoice->payment_method ?? 'all';
        $rentAmount    = (float) ($invoice->amount ?? 0);
        $depositAmount = (float) ($invoice->deposit_amount ?? 0);
        $baseAmount    = $rentAmount + $depositAmount;

        $feeAmount = 0.00;
        $feeDesc   = '';
        if (in_array($chosenMethod, ['tabby', 'tamara'])) {
            $feeAmount = round($baseAmount * 0.15, 2);
            $methodName = $chosenMethod === 'tabby' ? 'تابي Tabby' : 'تمارا Tamara';
            $feeDesc   = " (شامل رسوم تقسيط {$methodName} 15%: " . number_format($feeAmount, 2) . " ر.س)";
        } elseif ($chosenMethod === 'apple_pay') {
            $feeDesc = ' (Apple Pay)';
        }

        $totalAmount = $baseAmount + $feeAmount;

        $desc = 'حجز وحدة سكنية — رقم ' . ($invoice->suite_number ?: $invoice->apartment_number);
        if ($depositAmount > 0) {
            $desc .= ' (إيجار: ' . number_format($rentAmount, 2) . ' + تأمين: ' . number_format($depositAmount, 2) . ')';
        }
        $desc .= $feeDesc . ' — توثيق العقود';

        // Determine which panels (payment methods) to show on Telr checkout
        $panels = match ($chosenMethod) {
            'apple_pay' => 'applepay',                // Apple Pay only
            'tabby'     => 'tabby',                  // Tabby only
            'tamara'    => 'tamara',                 // Tamara only
            'standard'  => 'card',                  // Cards only (Mada/Visa/MC)
            default     => 'card,applepay,tabby,tamara', // ALL available methods
        };

        $billing = $this->buildBillingDetails($invoice);

        $payload = array_merge([
            'ivp_method'   => 'create',
            'ivp_store'    => $this->storeId,
            'ivp_authkey'  => $this->authKey,
            'ivp_cart'     => $invoice->invoice_number . ($chosenMethod !== 'all' ? '-' . strtoupper($chosenMethod) : '') . '-' . time(),
            'ivp_test'     => $this->testMode,
            'ivp_amount'   => number_format($totalAmount, 2, '.', ''),
            'ivp_currency' => 'SAR',
            'ivp_desc'     => $desc,

            // Show only the selected payment panel(s) on Telr's checkout page
            'ivp_panels'   => $panels,

            // Return URLs — Telr redirects the customer here after payment
            'return_auth'  => route('telr.callback', ['invoice' => $invoice->invoice_number, 'status' => 'success']),
            'return_decl'  => route('telr.callback', ['invoice' => $invoice->invoice_number, 'status' => 'declined']),
            'return_can'   => route('telr.callback', ['invoice' => $invoice->invoice_number, 'status' => 'cancelled']),
        ], $billing);

        try {
            $response = Http::asForm()->timeout(15)->post($this->apiUrl, $payload);

            if ($response->successful() && isset($response['order']['url'])) {
                return [
                    'success' => true,
                    'url'     => $response['order']['url'],
                    'ref'     => $response['order']['ref'],
                ];
            }

            $errArr = $response['error'] ?? [];
            $errorMsg = $errArr['message'] ?? ($errArr['note'] ?? 'استجابة غير متوقعة من Telr.');
            if (!empty($errArr['note']) && !empty($errArr['message'])) {
                $errorMsg .= ' - ' . $errArr['note'];
            }
            if (!empty($errArr['details'])) {
                $errorMsg .= ' (' . $errArr['details'] . ')';
            }

            Log::error('Telr Create Order — unexpected response', [
                'status'   => $response->status(),
                'body'     => $response->json(),
                'invoice'  => $invoice->invoice_number,
            ]);

            return ['success' => false, 'error' => $errorMsg];
        } catch (\Exception $e) {
            Log::error('Telr Create Order — exception', [
                'message' => $e->getMessage(),
                'invoice' => $invoice->invoice_number,
            ]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Check the status of an existing order on Telr.
     *
     * Status codes:
     *   2 => Pending / Not yet processed
     *   3 => Authorised (Paid)
     *  -1 => Declined / Error
     *  -2 => Cancelled
     */
    public function checkOrder(string $orderRef): array
    {
        try {
            $response = Http::asForm()->timeout(15)->post($this->apiUrl, [
                'ivp_method'  => 'check',
                'ivp_store'   => $this->storeId,
                'ivp_authkey' => $this->authKey,
                'order_ref'   => $orderRef,
            ]);

            if ($response->successful() && isset($response['order']['status'])) {
                return [
                    'success' => true,
                    'code'    => (int) $response['order']['status']['code'],
                    'message' => $response['order']['status']['text'] ?? '',
                ];
            }

            Log::error('Telr Check Order — unexpected response', [
                'body' => $response->json(),
                'ref'  => $orderRef,
            ]);

            return ['success' => false, 'error' => 'Unexpected Telr response.'];
        } catch (\Exception $e) {
            Log::error('Telr Check Order — exception', [
                'message' => $e->getMessage(),
                'ref'     => $orderRef,
            ]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Build formatted and validated billing details for Telr API.
     * Ensures Address, City, Country, Postal Code, Name, Email, and Phone
     * strictly conform to Telr gateway & Apple Pay verification requirements.
     */
    private function buildBillingDetails(Invoice $invoice): array
    {
        // 1. Customer Name (Split into Forename and Surname)
        $rawName = trim((string) ($invoice->customer_name ?? ''));
        $nameParts = preg_split('/\s+/', $rawName);
        $firstName = !empty($nameParts[0]) ? $nameParts[0] : 'العميل';
        $surname   = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : $firstName;

        // Ensure length constraints (Telr min 2 chars)
        if (mb_strlen($firstName) < 2) {
            $firstName = 'العميل';
        }
        if (mb_strlen($surname) < 2) {
            $surname = $firstName;
        }

        // 2. City, Region & Postal Code mapping (Saudi Arabia)
        $location = trim((string) ($invoice->property_location ?? ''));
        $propertyName = trim((string) ($invoice->property_name ?? ''));
        $searchContext = $location . ' ' . $propertyName;

        $city       = 'الرياض';
        $region     = 'منطقة الرياض';
        $postalCode = '12271';

        $saudiCities = [
            'جدة'         => ['city' => 'جدة', 'region' => 'منطقة مكة المكرمة', 'zip' => '21589'],
            'مكة'         => ['city' => 'مكة المكرمة', 'region' => 'منطقة مكة المكرمة', 'zip' => '24231'],
            'المدينة'     => ['city' => 'المدينة المنورة', 'region' => 'منطقة المدينة المنورة', 'zip' => '42311'],
            'الدمام'      => ['city' => 'الدمام', 'region' => 'المنطقة الشرقية', 'zip' => '31411'],
            'الخبر'       => ['city' => 'الخبر', 'region' => 'المنطقة الشرقية', 'zip' => '31952'],
            'الظهران'     => ['city' => 'الظهران', 'region' => 'المنطقة الشرقية', 'zip' => '31261'],
            'الأحساء'     => ['city' => 'الهفوف', 'region' => 'المنطقة الشرقية', 'zip' => '31982'],
            'الهفوف'      => ['city' => 'الهفوف', 'region' => 'المنطقة الشرقية', 'zip' => '31982'],
            'الجبيل'      => ['city' => 'الجبيل', 'region' => 'المنطقة الشرقية', 'zip' => '31951'],
            'الطائف'      => ['city' => 'الطائف', 'region' => 'منطقة مكة المكرمة', 'zip' => '26513'],
            'القصيم'      => ['city' => 'بريدة', 'region' => 'منطقة القصيم', 'zip' => '51411'],
            'بريدة'       => ['city' => 'بريدة', 'region' => 'منطقة القصيم', 'zip' => '51411'],
            'عنيزة'       => ['city' => 'عنيزة', 'region' => 'منطقة القصيم', 'zip' => '51911'],
            'حائل'        => ['city' => 'حائل', 'region' => 'منطقة حائل', 'zip' => '55421'],
            'تبوك'        => ['city' => 'تبوك', 'region' => 'منطقة تبوك', 'zip' => '71411'],
            'أبها'        => ['city' => 'أبها', 'region' => 'منطقة عسير', 'zip' => '62521'],
            'خميس مشيط'   => ['city' => 'خميس مشيط', 'region' => 'منطقة عسير', 'zip' => '62431'],
            'جازان'       => ['city' => 'جازان', 'region' => 'منطقة جازان', 'zip' => '82711'],
            'نجران'       => ['city' => 'نجران', 'region' => 'منطقة نجران', 'zip' => '75211'],
            'ينبع'        => ['city' => 'ينبع', 'region' => 'منطقة المدينة المنورة', 'zip' => '46421'],
            'الرياض'      => ['city' => 'الرياض', 'region' => 'منطقة الرياض', 'zip' => '12271'],
        ];

        foreach ($saudiCities as $keyword => $data) {
            if (mb_stripos($searchContext, $keyword) !== false) {
                $city       = $data['city'];
                $region     = $data['region'];
                $postalCode = $data['zip'];
                break;
            }
        }

        // 3. Address line 1 & line 2 (min 2, max 110 chars)
        $address1 = !empty($location) ? $location : 'طريق الملك فهد';
        if (mb_strlen($address1) < 4) {
            $address1 = 'طريق الملك فهد - ' . $city;
        }
        if (mb_strlen($address1) > 100) {
            $address1 = mb_substr($address1, 0, 100);
        }

        $unitNumber = trim((string) ($invoice->suite_number ?: $invoice->apartment_number ?: ''));
        $address2 = '';
        if (!empty($propertyName) || !empty($unitNumber)) {
            $address2 = trim(($propertyName ? $propertyName . ' ' : '') . ($unitNumber ? 'وحدة ' . $unitNumber : ''));
            if (mb_strlen($address2) > 100) {
                $address2 = mb_substr($address2, 0, 100);
            }
        }

        // 4. Email address
        $userEmail = $invoice->user?->email;
        $email = (!empty($userEmail) && filter_var($userEmail, FILTER_VALIDATE_EMAIL))
            ? $userEmail
            : 'billing@thiqa.me';

        // 5. Phone number (clean non-digits, keep leading + if present, ensure 7-15 digits)
        $cleanPhone = preg_replace('/[^0-9+]/', '', (string) $invoice->phone);
        if (empty($cleanPhone) || strlen($cleanPhone) < 7) {
            $cleanPhone = '0500000000';
        }

        return [
            'bill_fname'   => $firstName,
            'bill_sname'   => $surname,
            'bill_addr1'   => $address1,
            'bill_addr2'   => $address2,
            'bill_city'    => $city,
            'bill_region'  => $region,
            'bill_country' => 'SA',
            'bill_zip'     => $postalCode,
            'bill_email'   => $email,
            'bill_phone'   => $cleanPhone,
        ];
    }
}
