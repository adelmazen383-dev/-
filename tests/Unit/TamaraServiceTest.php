<?php

namespace Tests\Unit;

use App\Models\Invoice;
use App\Models\Setting;
use App\Services\TamaraService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TamaraServiceTest extends TestCase
{
    use RefreshDatabase;

    private string $notificationSecret = 'test-tamara-notification-secret-key';

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();

        config([
            'services.tamara.api_url'            => 'https://api-sandbox.tamara.co',
            'services.tamara.api_token'          => 'fake-tamara-api-token',
            'services.tamara.public_key'         => 'fake-tamara-public-key',
            'services.tamara.notification_token' => $this->notificationSecret,
        ]);

        Setting::set('fee_tamara_percent', '15.00');
    }

    public function test_create_checkout_session_sends_valid_payload_and_returns_ids(): void
    {
        Http::fake([
            'https://api-sandbox.tamara.co/checkout' => Http::response([
                'order_id'     => 'ord-uuid-1234',
                'checkout_id'  => 'chk-uuid-5678',
                'checkout_url' => 'https://checkout-sandbox.tamara.co/checkout?token=abc',
                'status'       => 'new',
            ], 200),
        ]);

        $invoice = Invoice::create([
            'invoice_number'    => 'INV-UNIT0001',
            'customer_name'     => 'أحمد محمد',
            'phone'             => '0551234567',
            'national_id'       => '1012345678',
            'apartment_number'  => '201',
            'suite_number'      => '201',
            'property_location' => 'جدة - المملكة العربية السعودية',
            'amount'            => 1000.00,
            'deposit_amount'    => 500.00,
            'status'            => 'pending',
            'payment_method'    => 'tamara',
        ]);

        $service = new TamaraService();
        $result  = $service->createCheckoutSession($invoice);

        $this->assertTrue($result['success']);
        $this->assertSame('https://checkout-sandbox.tamara.co/checkout?token=abc', $result['web_url']);
        $this->assertSame('ord-uuid-1234', $result['order_id']);
        $this->assertSame('chk-uuid-5678', $result['checkout_id']);
        $this->assertSame('new', $result['status']);
        $this->assertStringStartsWith('INV-UNIT0001-', $result['order_reference_id']);

        Http::assertSent(function ($request) {
            $data = $request->data();
            return $request->url() === 'https://api-sandbox.tamara.co/checkout'
                && $data['order_number'] === 'INV-UNIT0001'
                && (float) $data['total_amount']['amount'] === 1725.0
                && $data['total_amount']['currency'] === 'SAR'
                && $data['payment_type'] === 'PAY_BY_INSTALMENTS'
                && $data['instalments'] === 4
                && $data['consumer']['phone_number'] === '+966551234567'
                && $data['billing_address']['city'] === 'Jeddah'
                && str_contains($data['merchant_url']['success'], 'status=success')
                && str_contains($data['merchant_url']['notification'], '/payment/tamara/webhook');
        });
    }

    public function test_create_checkout_session_handles_api_error_gracefully(): void
    {
        Http::fake([
            'https://api-sandbox.tamara.co/checkout' => Http::response([
                'message' => 'Amount exceeds merchant limit',
            ], 422),
        ]);

        $invoice = Invoice::create([
            'invoice_number'   => 'INV-UNIT0002',
            'customer_name'    => 'سعد خالد',
            'phone'            => '0501112233',
            'national_id'      => '1022334455',
            'apartment_number' => '101',
            'amount'           => 1000.00,
            'deposit_amount'   => 0.00,
            'status'           => 'pending',
            'payment_method'   => 'tamara',
        ]);

        $service = new TamaraService();
        $result  = $service->createCheckoutSession($invoice);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Amount exceeds merchant limit', $result['error']);
    }

    public function test_create_checkout_session_fails_safely_when_unconfigured(): void
    {
        config(['services.tamara.api_token' => '']);

        $invoice = Invoice::create([
            'invoice_number'   => 'INV-UNIT0003',
            'customer_name'    => 'فهد علي',
            'phone'            => '0550000001',
            'national_id'      => '1099887766',
            'apartment_number' => '303',
            'amount'           => 500.00,
            'deposit_amount'   => 0.00,
            'status'           => 'pending',
            'payment_method'   => 'tamara',
        ]);

        $service = new TamaraService();
        $result  = $service->createCheckoutSession($invoice);

        $this->assertFalse($result['success']);
        Http::assertNothingSent();
    }

    public function test_verify_order_payment_authorises_and_captures_when_not_auto_captured(): void
    {
        Http::fake([
            'https://api-sandbox.tamara.co/orders/ord-111' => Http::response([
                'order_id' => 'ord-111',
                'status'   => 'approved',
            ], 200),
            'https://api-sandbox.tamara.co/orders/ord-111/authorise' => Http::response([
                'order_id'      => 'ord-111',
                'status'        => 'authorised',
                'auto_captured' => false,
            ], 200),
            'https://api-sandbox.tamara.co/payments/capture' => Http::response([
                'capture_id' => 'cap-222',
                'status'     => 'fully_captured',
            ], 200),
        ]);

        $invoice = Invoice::create([
            'invoice_number'   => 'INV-UNIT0004',
            'customer_name'    => 'نورة سالم',
            'phone'            => '0551112222',
            'national_id'      => '1011223344',
            'apartment_number' => '404',
            'amount'           => 1000.00,
            'deposit_amount'   => 0.00,
            'total_payable'    => 1150.00,
            'status'           => 'pending',
            'payment_method'   => 'tamara',
            'tamara_order_id'  => 'ord-111',
        ]);

        $service = new TamaraService();
        $result  = $service->verifyOrderPayment($invoice);

        $this->assertTrue($result['paid']);
        $this->assertFalse($result['failed']);
        $this->assertSame('fully_captured', $result['status']);
        $this->assertSame('ord-111', $result['order_id']);

        Http::assertSentCount(3);
    }

    public function test_verify_order_payment_skips_capture_when_auto_captured(): void
    {
        Http::fake([
            'https://api-sandbox.tamara.co/orders/ord-auto' => Http::response([
                'order_id' => 'ord-auto',
                'status'   => 'approved',
            ], 200),
            'https://api-sandbox.tamara.co/orders/ord-auto/authorise' => Http::response([
                'order_id'      => 'ord-auto',
                'status'        => 'authorised',
                'auto_captured' => true,
            ], 200),
        ]);

        $invoice = Invoice::create([
            'invoice_number'   => 'INV-UNIT0005',
            'customer_name'    => 'ريم ناصر',
            'phone'            => '0553334444',
            'national_id'      => '1033445566',
            'apartment_number' => '505',
            'amount'           => 800.00,
            'deposit_amount'   => 0.00,
            'total_payable'    => 920.00,
            'status'           => 'pending',
            'payment_method'   => 'tamara',
            'tamara_order_id'  => 'ord-auto',
        ]);

        $service = new TamaraService();
        $result  = $service->verifyOrderPayment($invoice);

        $this->assertTrue($result['paid']);
        $this->assertSame('authorised', $result['status']);
        Http::assertNotSent(fn ($req) => str_contains($req->url(), '/payments/capture'));
    }

    public function test_verify_order_payment_falls_back_to_reference_id_lookup(): void
    {
        Http::fake([
            'https://api-sandbox.tamara.co/merchants/orders/reference-id/INV-UNIT0006-REF' => Http::response([
                'order_id' => 'ord-ref-999',
                'status'   => 'approved',
            ], 200),
            'https://api-sandbox.tamara.co/orders/ord-ref-999/authorise' => Http::response([
                'order_id'      => 'ord-ref-999',
                'status'        => 'authorised',
                'auto_captured' => true,
            ], 200),
        ]);

        $invoice = Invoice::create([
            'invoice_number'            => 'INV-UNIT0006',
            'customer_name'             => 'وليد الحازمي',
            'phone'                     => '0551230000',
            'national_id'               => '1012300000',
            'apartment_number'          => '606',
            'amount'                    => 1000.00,
            'deposit_amount'            => 0.00,
            'total_payable'             => 1150.00,
            'status'                    => 'pending',
            'payment_method'            => 'tamara',
            'tamara_order_reference_id' => 'INV-UNIT0006-REF',
        ]);

        $service = new TamaraService();
        $result  = $service->verifyOrderPayment($invoice);

        $this->assertTrue($result['paid']);
        $this->assertSame('ord-ref-999', $result['order_id']);
        $this->assertSame('authorised', $result['status']);
    }

    public function test_verify_webhook_token_validates_hs256_jwt_correctly(): void
    {
        $service = new TamaraService();

        $validJwt = $this->makeJwt([
            'alg' => 'HS256',
            'typ' => 'JWT',
        ], [
            'iss' => 'Tamara',
            'exp' => time() + 300,
        ], $this->notificationSecret);

        $expiredJwt = $this->makeJwt([
            'alg' => 'HS256',
            'typ' => 'JWT',
        ], [
            'iss' => 'Tamara',
            'exp' => time() - 300,
        ], $this->notificationSecret);

        $wrongSecretJwt = $this->makeJwt([
            'alg' => 'HS256',
            'typ' => 'JWT',
        ], [
            'iss' => 'Tamara',
            'exp' => time() + 300,
        ], 'wrong-secret');

        $wrongIssuerJwt = $this->makeJwt([
            'alg' => 'HS256',
            'typ' => 'JWT',
        ], [
            'iss' => 'Attacker',
            'exp' => time() + 300,
        ], $this->notificationSecret);

        $algNoneJwt = $this->makeJwt([
            'alg' => 'none',
            'typ' => 'JWT',
        ], [
            'iss' => 'Tamara',
            'exp' => time() + 300,
        ], $this->notificationSecret);

        $this->assertTrue($service->verifyWebhookToken($validJwt));
        $this->assertFalse($service->verifyWebhookToken($expiredJwt));
        $this->assertFalse($service->verifyWebhookToken($wrongSecretJwt));
        $this->assertFalse($service->verifyWebhookToken($wrongIssuerJwt));
        $this->assertFalse($service->verifyWebhookToken($algNoneJwt));
        $this->assertFalse($service->verifyWebhookToken('not.a.jwt'));
        $this->assertFalse($service->verifyWebhookToken(''));
    }

    private function makeJwt(array $header, array $payload, string $secret): string
    {
        $encode = fn (array $data): string => rtrim(strtr(base64_encode(json_encode($data)), '+/', '-_'), '=');
        $h      = $encode($header);
        $p      = $encode($payload);
        $sig    = rtrim(strtr(base64_encode(hash_hmac('sha256', "{$h}.{$p}", $secret, true)), '+/', '-_'), '=');

        return "{$h}.{$p}.{$sig}";
    }
}
