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

        $desc = 'Unit Reservation No ' . ($invoice->suite_number ?: ($invoice->apartment_number ?: $invoice->invoice_number));

        // Determine which panels (payment methods) to show on Telr checkout
        $panels = match ($chosenMethod) {
            'apple_pay' => 'card,applepay',              // Cards + Apple Pay (never isolate applepay)
            'tabby'     => 'tabby',                      // Tabby only
            'tamara'    => 'tamara',                     // Tamara only
            'standard'  => 'card',                       // Cards only (Mada/Visa/MC)
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
            'ivp_framed'   => 0, // Explicitly enforce full-screen redirection (not an iFrame)

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
        // 1. Customer Name (Transliterate to Latin ASCII for Visa/MC/3DS ISO 8583 compliance)
        $rawName = trim((string) ($invoice->customer_name ?? ''));
        $latinName = $this->transliterateArabic($rawName);
        $nameParts = preg_split('/\s+/', $latinName);

        $firstName = !empty($nameParts[0]) ? $nameParts[0] : 'Client';
        $surname   = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : 'Customer';

        // Ensure length constraints (Telr min 2 chars)
        if (strlen($firstName) < 2) {
            $firstName = 'Client';
        }
        if (strlen($surname) < 2) {
            $surname = 'Customer';
        }

        // 2. City, Region & Postal Code mapping (Saudi Arabia in standard English/Latin)
        $location = trim((string) ($invoice->property_location ?? ''));
        $propertyName = trim((string) ($invoice->property_name ?? ''));
        $searchContext = $location . ' ' . $propertyName;

        $city       = 'Riyadh';
        $region     = 'Riyadh';
        $postalCode = '12271';

        $saudiCities = [
            'جدة'         => ['city' => 'Jeddah', 'region' => 'Makkah', 'zip' => '21589'],
            'مكة'         => ['city' => 'Mecca', 'region' => 'Makkah', 'zip' => '24231'],
            'المدينة'     => ['city' => 'Medina', 'region' => 'Medina', 'zip' => '42311'],
            'الدمام'      => ['city' => 'Dammam', 'region' => 'Eastern Province', 'zip' => '31411'],
            'الخبر'       => ['city' => 'Khobar', 'region' => 'Eastern Province', 'zip' => '31952'],
            'الظهران'     => ['city' => 'Dhahran', 'region' => 'Eastern Province', 'zip' => '31261'],
            'الأحساء'     => ['city' => 'Al Ahsa', 'region' => 'Eastern Province', 'zip' => '31982'],
            'الهفوف'      => ['city' => 'Hofuf', 'region' => 'Eastern Province', 'zip' => '31982'],
            'الجبيل'      => ['city' => 'Jubail', 'region' => 'Eastern Province', 'zip' => '31951'],
            'الطائف'      => ['city' => 'Taif', 'region' => 'Makkah', 'zip' => '26513'],
            'القصيم'      => ['city' => 'Buraidah', 'region' => 'Al Qassim', 'zip' => '51411'],
            'بريدة'       => ['city' => 'Buraidah', 'region' => 'Al Qassim', 'zip' => '51411'],
            'عنيزة'       => ['city' => 'Unaizah', 'region' => 'Al Qassim', 'zip' => '51911'],
            'حائل'        => ['city' => 'Hail', 'region' => 'Hail', 'zip' => '55421'],
            'تبوك'        => ['city' => 'Tabuk', 'region' => 'Tabuk', 'zip' => '71411'],
            'أبها'        => ['city' => 'Abha', 'region' => 'Asir', 'zip' => '62521'],
            'خميس مشيط'   => ['city' => 'Khamis Mushait', 'region' => 'Asir', 'zip' => '62431'],
            'جازان'       => ['city' => 'Jazan', 'region' => 'Jazan', 'zip' => '82711'],
            'نجران'       => ['city' => 'Najran', 'region' => 'Najran', 'zip' => '75211'],
            'ينبع'        => ['city' => 'Yanbu', 'region' => 'Medina', 'zip' => '46421'],
            'الرياض'      => ['city' => 'Riyadh', 'region' => 'Riyadh', 'zip' => '12271'],
        ];

        foreach ($saudiCities as $keyword => $data) {
            if (mb_stripos($searchContext, $keyword) !== false) {
                $city       = $data['city'];
                $region     = $data['region'];
                $postalCode = $data['zip'];
                break;
            }
        }

        // 3. Address line 1 & line 2 (pure Latin ASCII)
        $address1 = 'King Fahd Road';
        $address2 = 'District ' . $city;

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

    /**
     * Helper to transliterate Arabic text to standard English Latin characters.
     */
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
            'ة' => 'h', 'ء' => '', 'ئ' => 'Y', 'ؤ' => 'W',
            'لا' => 'La',
        ];

        $transliterated = strtr($text, $map);
        $clean = preg_replace('/[^a-zA-Z0-9\s]/', '', $transliterated);
        return trim($clean);
    }
}
