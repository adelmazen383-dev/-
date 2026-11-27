<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TamaraPaymentFlowTest extends TestCase
{
    use RefreshDatabase;

    private string $notificationSecret = 'feature-test-tamara-notification-secret';

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();

        config([
            'services.tamara.api_url'            => 'https://api-sandbox.tamara.co',
            'services.tamara.api_token'          => 'fake-feature-tamara-token',
            'services.tamara.public_key'         => 'fake-feature-public-key',
            'services.tamara.notification_token' => $this->notificationSecret,
        ]);

        Setting::set('fee_visa_percent', '5.00');
        Setting::set('fee_apple_pay_percent', '5.00');
        Setting::set('fee_tabby_percent', '15.00');
        Setting::set('fee_tamara_percent', '15.00');
    }

    public function test_admin_can_create_invoice_with_tamara_and_view_details(): void
    {
        Http::fake([
            'https://api-sandbox.tamara.co/checkout' => Http::response([
                'order_id'     => 'ord-create-001',
                'checkout_id'  => 'chk-create-001',
                'checkout_url' => 'https://checkout-sandbox.tamara.co/session/create-001',
                'status'       => 'new',
            ], 200),
        ]);

        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('invoices.store'), [
            'customer_name'     => 'خالد العتيبي',
            'phone'             => '0551234567',
            'national_id'       => '1012345678',
            'apartment_number'  => '205',
            'suite_number'      => '205',
            'agreed_amount'     => '2000',
            'amount'            => '1000',
            'deposit_amount'    => '500',
            'payment_method'    => 'tamara',
            'property_location' => 'جدة - المملكة العربية السعودية',
        ]);

        $invoice = Invoice::firstOrFail();

        $response->assertRedirect(route('invoices.show', $invoice));
        $response->assertSessionHas('success');

        $this->assertSame('tamara', $invoice->payment_method);
        $this->assertSame('15.00', (string) $invoice->fee_percentage);
        $this->assertSame('225.00', (string) $invoice->fee_amount);
        $this->assertSame('1725.00', (string) $invoice->total_payable);
        $this->assertSame('https://checkout-sandbox.tamara.co/session/create-001', $invoice->tamara_url);
        $this->assertSame('ord-create-001', $invoice->tamara_order_id);
        $this->assertSame('chk-create-001', $invoice->tamara_checkout_id);
        $this->assertSame('new', $invoice->tamara_status);
        $this->assertStringStartsWith($invoice->invoice_number . '-', $invoice->tamara_order_reference_id);

        $showResponse = $this->actingAs($admin)->get(route('invoices.show', $invoice));
        $showResponse->assertOk();
        $showResponse->assertSee('ord-create-001');
        $showResponse->assertDontSee('تأكد من إعدادات بوابة');
    }

    public function test_customer_can_select_tamara_on_public_booking_card(): void
    {
        Http::fake([
            'https://api-sandbox.tamara.co/checkout' => Http::response([
                'order_id'     => 'ord-public-001',
                'checkout_id'  => 'chk-public-001',
                'checkout_url' => 'https://checkout-sandbox.tamara.co/session/public-001',
                'status'       => 'new',
            ], 200),
        ]);

        $invoice = Invoice::create([
            'invoice_number'   => 'INV-PUB001',
            'customer_name'    => 'سارة القحطاني',
            'phone'            => '0509876543',
            'national_id'      => '1098765432',
            'apartment_number' => '301',
            'amount'           => 1000.00,
            'agreed_amount'    => 1000.00,
            'deposit_amount'   => 0.00,
            'status'           => 'pending',
            'payment_method'   => 'all',
        ]);

        $cardResponse = $this->get(route('booking-card.show', $invoice->booking_card_token));
        $cardResponse->assertOk();
        $cardResponse->assertSee('تمارا');

        $payResponse = $this->get(route('booking-card.pay', [
            'token'  => $invoice->booking_card_token,
            'method' => 'tamara',
        ]));

        $payResponse->assertRedirect('https://checkout-sandbox.tamara.co/session/public-001');

        $invoice->refresh();
        $this->assertSame('tamara', $invoice->payment_method);
        $this->assertSame('1150.00', (string) $invoice->total_payable);
        $this->assertSame('ord-public-001', $invoice->tamara_order_id);
        $this->assertSame('https://checkout-sandbox.tamara.co/session/public-001', $invoice->tamara_url);
    }

    public function test_tamara_callback_success_verifies_authorises_and_marks_invoice_paid(): void
    {
        Http::fake([
            'https://api-sandbox.tamara.co/orders/ord-cb-001' => Http::response([
                'order_id' => 'ord-cb-001',
                'status'   => 'approved',
            ], 200),
            'https://api-sandbox.tamara.co/orders/ord-cb-001/authorise' => Http::response([
                'order_id'      => 'ord-cb-001',
                'status'        => 'authorised',
                'auto_captured' => true,
            ], 200),
        ]);

        $invoice = Invoice::create([
            'invoice_number'   => 'INV-CB001',
            'customer_name'    => 'محمد الحربي',
            'phone'            => '0554443322',
            'national_id'      => '1044332211',
            'apartment_number' => '105',
            'amount'           => 1000.00,
            'agreed_amount'    => 2000.00,
            'deposit_amount'   => 500.00,
            'fee_percentage'   => 15.00,
            'fee_amount'       => 225.00,
            'total_payable'    => 1725.00,
            'status'           => 'pending',
            'payment_method'   => 'tamara',
            'tamara_order_id'  => 'ord-cb-001',
            'tamara_status'    => 'new',
        ]);

        $response = $this->get(route('payment.tamara.callback', [
            'token'         => $invoice->booking_card_token,
            'status'        => 'success',
            'orderId'       => 'ord-cb-001',
            'paymentStatus' => 'approved',
        ]));

        $response->assertRedirect(route('booking-card.show', $invoice->booking_card_token));
        $response->assertSessionHas('success');

        $invoice->refresh();
        $this->assertSame('paid', $invoice->status);
        $this->assertSame('tamara', $invoice->payment_method);
        $this->assertSame('1000.00', (string) $invoice->amount_paid);
        $this->assertSame('1000.00', (string) $invoice->remaining_amount);
        $this->assertSame('authorised', $invoice->tamara_status);
        $this->assertNotNull($invoice->paid_at);

        // Verify flash banner is rendered on public booking card after redirect
        $cardResponse = $this->get(route('booking-card.show', $invoice->booking_card_token));
        $cardResponse->assertSee('تم سداد الفاتورة بنجاح');
    }

    public function test_tamara_callback_does_not_mark_paid_without_tamara_api_verification(): void
    {
        Http::fake([
            'https://api-sandbox.tamara.co/orders/ord-unpaid' => Http::response([
                'order_id' => 'ord-unpaid',
                'status'   => 'new',
            ], 200),
        ]);

        $invoiceNoOrder = Invoice::create([
            'invoice_number'   => 'INV-SEC001',
            'customer_name'    => 'تست امان',
            'phone'            => '0550000111',
            'national_id'      => '1000000111',
            'apartment_number' => '10',
            'amount'           => 500.00,
            'status'           => 'pending',
            'payment_method'   => 'tamara',
        ]);

        $resp1 = $this->get(route('payment.tamara.callback', [
            'token'  => $invoiceNoOrder->booking_card_token,
            'status' => 'success',
        ]));
        $resp1->assertRedirect(route('booking-card.show', $invoiceNoOrder->booking_card_token));
        $this->assertSame('pending', $invoiceNoOrder->fresh()->status);

        $invoiceNewOrder = Invoice::create([
            'invoice_number'   => 'INV-SEC002',
            'customer_name'    => 'تست امان ٢',
            'phone'            => '0550000222',
            'national_id'      => '1000000222',
            'apartment_number' => '11',
            'amount'           => 500.00,
            'status'           => 'pending',
            'payment_method'   => 'tamara',
            'tamara_order_id'  => 'ord-unpaid',
        ]);

        $resp2 = $this->get(route('payment.tamara.callback', [
            'token'   => $invoiceNewOrder->booking_card_token,
            'status'  => 'success',
            'orderId' => 'ord-unpaid',
        ]));
        $resp2->assertRedirect(route('booking-card.show', $invoiceNewOrder->booking_card_token));
        $this->assertSame('pending', $invoiceNewOrder->fresh()->status);
    }

    public function test_tamara_callback_cancel_and_failure_allow_customer_to_retry(): void
    {
        $invoice = Invoice::create([
            'invoice_number'   => 'INV-FAIL001',
            'customer_name'    => 'فيصل الدوسري',
            'phone'            => '0557778899',
            'national_id'      => '1077889900',
            'apartment_number' => '701',
            'amount'           => 1000.00,
            'status'           => 'pending',
            'payment_method'   => 'tamara',
        ]);

        $cancelResp = $this->get(route('payment.tamara.callback', [
            'token'  => $invoice->booking_card_token,
            'status' => 'cancel',
        ]));
        $cancelResp->assertRedirect(route('booking-card.show', $invoice->booking_card_token));
        $cancelResp->assertSessionHas('warning');
        $this->assertSame('pending', $invoice->fresh()->status);
        $this->assertSame('canceled', $invoice->fresh()->tamara_status);

        $failResp = $this->get(route('payment.tamara.callback', [
            'token'  => $invoice->booking_card_token,
            'status' => 'failure',
        ]));
        $failResp->assertRedirect(route('booking-card.show', $invoice->booking_card_token));
        $failResp->assertSessionHas('error');
        $this->assertSame('failed', $invoice->fresh()->status);

        // Public booking card still renders payment portal so customer can retry
        $cardResp = $this->get(route('booking-card.show', $invoice->booking_card_token));
        $cardResp->assertOk();
        $cardResp->assertSee('pay-submit-btn');
    }

    public function test_tamara_webhook_rejects_unauthorized_requests_and_processes_valid_events(): void
    {
        $invoice = Invoice::create([
            'invoice_number'            => 'INV-WH001',
            'customer_name'             => 'عبدالله الشهري',
            'phone'                     => '0552223344',
            'national_id'               => '1022334466',
            'apartment_number'          => '808',
            'amount'                    => 1200.00,
            'agreed_amount'             => 1200.00,
            'deposit_amount'            => 0.00,
            'fee_percentage'            => 15.00,
            'fee_amount'                => 180.00,
            'total_payable'             => 1380.00,
            'status'                    => 'pending',
            'payment_method'            => 'tamara',
            'tamara_order_id'           => 'ord-wh-001',
            'tamara_order_reference_id' => 'INV-WH001-1732650000-ABCD',
            'tamara_status'             => 'new',
        ]);

        // 1. Missing token => 401
        $this->postJson(route('payment.tamara.webhook'), [
            'event_type'         => 'order_approved',
            'order_id'           => 'ord-wh-001',
            'order_reference_id' => 'INV-WH001-1732650000-ABCD',
        ])->assertStatus(401);

        // 2. Wrong token => 401
        $badJwt = $this->makeJwt(['alg' => 'HS256', 'typ' => 'JWT'], ['iss' => 'Tamara', 'exp' => time() + 300], 'bad-secret');
        $this->postJson(route('payment.tamara.webhook') . '?tamaraToken=' . $badJwt, [
            'event_type'         => 'order_approved',
            'order_id'           => 'ord-wh-001',
            'order_reference_id' => 'INV-WH001-1732650000-ABCD',
        ])->assertStatus(401);

        $this->assertSame('pending', $invoice->fresh()->status);

        // 3. Valid token => verifies via Tamara API, authorises, captures, marks paid
        Http::fake([
            'https://api-sandbox.tamara.co/orders/ord-wh-001' => Http::response([
                'order_id' => 'ord-wh-001',
                'status'   => 'approved',
            ], 200),
            'https://api-sandbox.tamara.co/orders/ord-wh-001/authorise' => Http::response([
                'order_id'      => 'ord-wh-001',
                'status'        => 'authorised',
                'auto_captured' => false,
            ], 200),
            'https://api-sandbox.tamara.co/payments/capture' => Http::response([
                'capture_id' => 'cap-wh-001',
                'status'     => 'fully_captured',
            ], 200),
        ]);

        $validJwt = $this->makeJwt(['alg' => 'HS256', 'typ' => 'JWT'], ['iss' => 'Tamara', 'exp' => time() + 300], $this->notificationSecret);

        $this->postJson(route('payment.tamara.webhook') . '?tamaraToken=' . $validJwt, [
            'event_type'         => 'order_approved',
            'order_id'           => 'ord-wh-001',
            'order_reference_id' => 'INV-WH001-1732650000-ABCD',
            'order_number'       => 'INV-WH001',
        ])->assertOk()->assertJson(['status' => 'ok']);

        $invoice->refresh();
        $this->assertSame('paid', $invoice->status);
        $this->assertSame('fully_captured', $invoice->tamara_status);
        $this->assertSame('1200.00', (string) $invoice->amount_paid);
        $this->assertSame('0.00', (string) $invoice->remaining_amount);

        // 4. Declined webhook on another pending invoice via Bearer token
        $declinedInvoice = Invoice::create([
            'invoice_number'            => 'INV-WH002',
            'customer_name'             => 'بدر العنزي',
            'phone'                     => '0556667788',
            'national_id'               => '1066778899',
            'apartment_number'          => '809',
            'amount'                    => 900.00,
            'status'                    => 'pending',
            'payment_method'            => 'tamara',
            'tamara_order_id'           => 'ord-wh-002',
            'tamara_order_reference_id' => 'INV-WH002-1732650001-EFGH',
        ]);

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $validJwt,
        ])->postJson(route('payment.tamara.webhook'), [
            'event_type'         => 'order_declined',
            'order_id'           => 'ord-wh-002',
            'order_reference_id' => 'INV-WH002-1732650001-EFGH',
        ])->assertOk();

        $declinedInvoice->refresh();
        $this->assertSame('failed', $declinedInvoice->status);
        $this->assertSame('declined', $declinedInvoice->tamara_status);
    }

    public function test_admin_check_status_and_retry_tamara_routes_work(): void
    {
        $admin = User::factory()->create();

        $invoice = Invoice::create([
            'invoice_number'   => 'INV-ADM001',
            'customer_name'    => 'تركي المطيري',
            'phone'            => '0558889900',
            'national_id'      => '1088990011',
            'apartment_number' => '902',
            'amount'           => 1000.00,
            'agreed_amount'    => 1000.00,
            'deposit_amount'   => 0.00,
            'status'           => 'failed',
            'payment_method'   => 'tamara',
        ]);

        Http::fake([
            'https://api-sandbox.tamara.co/checkout' => Http::response([
                'order_id'     => 'ord-retry-002',
                'checkout_id'  => 'chk-retry-002',
                'checkout_url' => 'https://checkout-sandbox.tamara.co/session/retry-002',
                'status'       => 'new',
            ], 200),
            'https://api-sandbox.tamara.co/orders/ord-retry-002' => Http::response([
                'order_id' => 'ord-retry-002',
                'status'   => 'fully_captured',
            ], 200),
        ]);

        $retryResp = $this->actingAs($admin)->post(route('invoices.retry-tamara', $invoice));
        $retryResp->assertRedirect();
        $retryResp->assertSessionHas('success');

        $invoice->refresh();
        $this->assertSame('pending', $invoice->status);
        $this->assertSame('ord-retry-002', $invoice->tamara_order_id);
        $this->assertSame('https://checkout-sandbox.tamara.co/session/retry-002', $invoice->tamara_url);

        $checkResp = $this->actingAs($admin)->post(route('invoices.check-status', $invoice));
        $checkResp->assertRedirect(route('contracts.create', ['invoice_id' => $invoice->id]));
        $checkResp->assertSessionHas('success');

        $invoice->refresh();
        $this->assertSame('paid', $invoice->status);
        $this->assertSame('fully_captured', $invoice->tamara_status);
    }

    public function test_json_select_payment_method_and_dynamic_tamara_views_render_cleanly(): void
    {
        Http::fake([
            'https://api-sandbox.tamara.co/checkout' => Http::response([
                'order_id'     => 'ord-json-001',
                'checkout_id'  => 'chk-json-001',
                'checkout_url' => 'https://checkout-sandbox.tamara.co/session/json-001',
                'status'       => 'new',
            ], 200),
        ]);

        Setting::set('fee_tamara_percent', '12.50');

        $admin = User::factory()->create();
        $admin->givePermissionTo([]); // ensure user model methods work cleanly

        $invoice = Invoice::create([
            'user_id'          => $admin->id,
            'invoice_number'   => 'INV-VIEW001',
            'customer_name'    => 'ماجد الزهراني',
            'phone'            => '0551122334',
            'national_id'      => '1011223344',
            'apartment_number' => '401',
            'suite_number'     => '401',
            'amount'           => 1000.00,
            'agreed_amount'    => 1000.00,
            'deposit_amount'   => 0.00,
            'status'           => 'pending',
            'payment_method'   => 'all',
        ]);

        $this->postJson(route('booking-card.pay', $invoice->booking_card_token), [
            'method' => 'tamara',
        ])->assertOk()->assertJson([
            'success'       => true,
            'method'        => 'tamara',
            'url'           => 'https://checkout-sandbox.tamara.co/session/json-001',
            'base_amount'   => 1000,
            'fee_amount'    => 125,
            'total_payable' => 1125,
        ]);

        $this->actingAs($admin)->get(route('invoices.create'))
            ->assertOk()
            ->assertSee('+12.5%');

        $this->get(route('booking-card.show', $invoice->booking_card_token))
            ->assertOk()
            ->assertSee('1,125.00')
            ->assertDontSee('+12.5%')
            ->assertDontSee('+5%');

        $this->get(route('invoices.print', $invoice))
            ->assertOk()
            ->assertSee('1,125.00')
            ->assertDontSee('+12.5%');

        $this->get(route('invoices.print', ['invoice' => $invoice, 'design' => 'sindi']))
            ->assertOk()
            ->assertSee('1,125.00')
            ->assertDontSee('+12.5%');

        $this->get(route('invoices.booking-card', $invoice))
            ->assertOk();
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
