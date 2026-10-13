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
    public function createOrder(Invoice $invoice): array
    {
        if (empty($this->storeId) || empty($this->authKey)) {
            Log::error('Telr credentials missing — check TELR_STORE_ID and TELR_AUTH_KEY in .env');
            return ['success' => false, 'error' => 'Telr credentials not configured.'];
        }

        $payload = [
            'ivp_method'   => 'create',
            'ivp_store'    => $this->storeId,
            'ivp_authkey'  => $this->authKey,
            'ivp_cart'     => $invoice->invoice_number,
            'ivp_test'     => $this->testMode,
            'ivp_amount'   => number_format($invoice->amount, 2, '.', ''),
            'ivp_currency' => 'SAR',
            'ivp_desc'     => 'حجز وحدة سكنية — رقم ' . ($invoice->suite_number ?: $invoice->apartment_number) . ' — توثيق العقود',

            // Return URLs — Telr redirects the customer here after payment
            'return_auth'  => route('telr.callback', ['invoice' => $invoice->invoice_number, 'status' => 'success']),
            'return_decl'  => route('telr.callback', ['invoice' => $invoice->invoice_number, 'status' => 'declined']),
            'return_can'   => route('telr.callback', ['invoice' => $invoice->invoice_number, 'status' => 'cancelled']),

            // Billing details
            'bill_fname'   => $invoice->customer_name,
            'bill_sname'   => '.',
            'bill_country' => 'SA',
            'bill_phone'   => $invoice->phone,
        ];

        try {
            $response = Http::asForm()->timeout(15)->post($this->apiUrl, $payload);

            if ($response->successful() && isset($response['order']['url'])) {
                return [
                    'success' => true,
                    'url'     => $response['order']['url'],
                    'ref'     => $response['order']['ref'],
                ];
            }

            $errorMsg = $response['error']['message'] ?? ($response['error']['note'] ?? 'Unexpected Telr response.');

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
}
