<?php

namespace Tests\Feature;

use App\Mail\ProviderPasswordResetOtpMail;
use App\Models\ProviderInvoice;
use App\Models\ProviderPasswordOtp;
use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ServiceProviderPortalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
        Storage::fake('local');

        config([
            'services.telr.store_id'            => '36090',
            'services.telr.auth_key'            => 'test-telr-auth-key',
            'services.telr.test_mode'           => '1',
            'services.tamara.api_url'           => 'https://api-sandbox.tamara.co',
            'services.tamara.api_token'         => 'test-tamara-token',
            'services.tamara.notification_token'=> 'test-tamara-notif-secret',
        ]);
    }

    private function createAdmin(): User
    {
        $role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole($role);
        return $admin;
    }

    private function createProvider(array $overrides = []): ServiceProvider
    {
        return ServiceProvider::create(array_merge([
            'name'                => 'سارة العتيبي',
            'business_name'       => 'لمسة فن للتنسيق',
            'email'               => 'provider@example.com',
            'phone'               => '0551234567',
            'password'            => 'Password123!',
            'service_type'        => 'coordination',
            'service_description' => 'تنسيق حفلات ومناسبات خاصة وتجهيز الغرف بالورد والبالونات',
            'bank_name'           => 'مصرف الراجحي',
            'bank_account_name'   => 'سارة العتيبي',
            'iban'                => 'SA0380000000608010167519',
            'is_active'           => true,
            'subscription_status' => 'inactive',
            'wallet_balance'      => 0.00,
        ], $overrides));
    }

    public function test_serviced_suites_registration_enforces_id_front_back_tourism_license_and_bank_confirmation(): void
    {
        // Missing back ID, tourism license, and bank confirmation -> validation fails
        $this->post(route('provider.register.submit'), [
            'name'                  => 'خالد الحربي',
            'business_name'         => 'أجنحة النخبة',
            'email'                 => 'suites@example.com',
            'phone'                 => '0559998881',
            'service_type'          => 'serviced_suites',
            'property_kind'         => 'شقة',
            'service_description'   => 'أجنحة وشقق فندقية مخدومة فاخرة بالرياض',
            'bank_name'             => 'الأهلي',
            'bank_account_name'     => 'خالد الحربي',
            'iban'                  => 'SA0380000000608010167519',
            'password'              => 'Secret123!',
            'password_confirmation' => 'Secret123!',
            'national_id_front'     => UploadedFile::fake()->image('id_front.jpg'),
        ])->assertSessionHasErrors(['national_id_back', 'tourism_license', 'bank_owner_confirmed']);

        // Complete valid submission succeeds and stores private files
        $this->post(route('provider.register.submit'), [
            'name'                  => 'خالد الحربي',
            'business_name'         => 'أجنحة النخبة',
            'email'                 => 'suites@example.com',
            'phone'                 => '0559998881',
            'service_type'          => 'serviced_suites',
            'property_kind'         => 'فيلا',
            'service_description'   => 'فلل وأجنحة فندقية مخدومة فاخرة بالرياض',
            'bank_name'             => 'الأهلي',
            'bank_account_name'     => 'خالد الحربي',
            'iban'                  => 'SA0380000000608010167519',
            'bank_owner_confirmed'  => '1',
            'password'              => 'Secret123!',
            'password_confirmation' => 'Secret123!',
            'national_id_front'     => UploadedFile::fake()->image('id_front.jpg'),
            'national_id_back'      => UploadedFile::fake()->image('id_back.jpg'),
            'tourism_license'       => UploadedFile::fake()->create('tourism.pdf', 100, 'application/pdf'),
        ])->assertRedirect(route('provider.dashboard'));

        $provider = ServiceProvider::where('email', 'suites@example.com')->firstOrFail();
        $this->assertTrue($provider->bank_owner_confirmed);
        $this->assertSame('فيلا', $provider->property_kind);
        $this->assertNotNull($provider->national_id_front_path);
        $this->assertNotNull($provider->national_id_back_path);
        $this->assertNotNull($provider->tourism_license_path);
        Storage::disk('local')->assertExists($provider->national_id_front_path);
        Storage::disk('local')->assertExists($provider->national_id_back_path);
        Storage::disk('local')->assertExists($provider->tourism_license_path);
    }

    public function test_coordination_and_photography_registration_enforce_their_specific_documents(): void
    {
        // Coordination requires commercial/freelance doc
        $this->post(route('provider.register.submit'), [
            'name'                  => 'ريم الشمري',
            'business_name'         => 'تنظيم ريم',
            'email'                 => 'coord@example.com',
            'phone'                 => '0551112223',
            'service_type'          => 'coordination',
            'service_description'   => 'تنسيق حفلات ومناسبات راقية بجميع التفاصيل',
            'bank_name'             => 'الراجحي',
            'bank_account_name'     => 'ريم الشمري',
            'iban'                  => 'SA0380000000608010167519',
            'password'              => 'Secret123!',
            'password_confirmation' => 'Secret123!',
            'national_id_front'     => UploadedFile::fake()->image('id.jpg'),
        ])->assertSessionHasErrors(['commercial_doc']);

        // Photography requires BOTH photography_license and commercial_doc
        $this->post(route('provider.register.submit'), [
            'name'                  => 'عمر القحطاني',
            'business_name'         => 'عدسة عمر',
            'email'                 => 'photo@example.com',
            'phone'                 => '0551112224',
            'service_type'          => 'photography',
            'service_description'   => 'تصوير فوتوغرافي وفيديو للمناسبات والمنتجات',
            'bank_name'             => 'الإنماء',
            'bank_account_name'     => 'عمر القحطاني',
            'iban'                  => 'SA0380000000608010167519',
            'password'              => 'Secret123!',
            'password_confirmation' => 'Secret123!',
            'national_id_front'     => UploadedFile::fake()->image('id.jpg'),
            'commercial_doc'        => UploadedFile::fake()->create('freelance.pdf', 80, 'application/pdf'),
        ])->assertSessionHasErrors(['photography_license']);

        // Valid photography registration succeeds
        $this->post(route('provider.register.submit'), [
            'name'                  => 'عمر القحطاني',
            'business_name'         => 'عدسة عمر',
            'email'                 => 'photo@example.com',
            'phone'                 => '0551112224',
            'service_type'          => 'photography',
            'service_description'   => 'تصوير فوتوغرافي وفيديو للمناسبات والمنتجات',
            'bank_name'             => 'الإنماء',
            'bank_account_name'     => 'عمر القحطاني',
            'iban'                  => 'SA0380000000608010167519',
            'password'              => 'Secret123!',
            'password_confirmation' => 'Secret123!',
            'national_id_front'     => UploadedFile::fake()->image('id.jpg'),
            'commercial_doc'        => UploadedFile::fake()->create('freelance.pdf', 80, 'application/pdf'),
            'photography_license'   => UploadedFile::fake()->create('photo_lic.pdf', 80, 'application/pdf'),
        ])->assertRedirect(route('provider.dashboard'));

        $photo = ServiceProvider::where('email', 'photo@example.com')->firstOrFail();
        Storage::disk('local')->assertExists($photo->photography_license_path);
        Storage::disk('local')->assertExists($photo->commercial_doc_path);
    }

    public function test_subscription_gate_blocks_invoice_creation_until_activated_and_blocks_again_when_expired(): void
    {
        $admin    = $this->createAdmin();
        $provider = $this->createProvider(['subscription_status' => 'inactive']);

        // 1. Unsubscribed provider cannot access create form or store invoice
        $this->actingAs($provider, 'provider')
            ->get(route('provider.invoices.create'))
            ->assertRedirect(route('provider.dashboard'));

        $this->actingAs($provider, 'provider')
            ->post(route('provider.invoices.store'), [
                'customer_name'  => 'فهد الدوسري',
                'customer_phone' => '0501234567',
                'title'          => 'باقة تنسيق',
                'amount'         => 500,
            ])->assertRedirect(route('provider.dashboard'));

        $this->assertDatabaseCount('provider_invoices', 0);

        // 2. Admin activates monthly subscription
        $this->actingAs($admin, 'web')
            ->post(route('admin.providers.subscription.activate', $provider), [
                'duration_months' => 1,
                'amount'          => 199,
            ])->assertRedirect();

        $provider->refresh();
        $this->assertTrue($provider->hasActiveSubscription());

        // 3. Now provider CAN create an invoice
        $this->actingAs($provider, 'provider')
            ->post(route('provider.invoices.store'), [
                'customer_name'   => 'فهد الدوسري',
                'customer_phone'  => '0501234567',
                'title'           => 'باقة تنسيق VIP',
                'amount'          => 850.00,
                'service_details' => ['event_type' => 'تخرج', 'venue' => 'الرياض'],
            ])->assertRedirect();

        $this->assertDatabaseCount('provider_invoices', 1);

        // 4. Simulate subscription expiry -> immediately blocked from creating new invoices
        $provider->update(['subscription_ends_at' => now()->subMinute()]);
        $this->assertFalse($provider->fresh()->hasActiveSubscription());

        $this->actingAs($provider->fresh(), 'provider')
            ->get(route('provider.invoices.create'))
            ->assertRedirect(route('provider.dashboard'));
    }

    public function test_verified_payment_credits_provider_wallet_once_and_admin_can_zero_wallet(): void
    {
        $admin    = $this->createAdmin();
        $provider = $this->createProvider([
            'subscription_status'    => 'active',
            'subscription_starts_at' => now(),
            'subscription_ends_at'   => now()->addMonth(),
        ]);

        $invoice = ProviderInvoice::create([
            'service_provider_id' => $provider->id,
            'service_type'        => 'coordination',
            'customer_name'       => 'عبدالله السالم',
            'customer_phone'      => '0557778889',
            'title'               => 'تنسيق حفلة خاصة',
            'amount'              => 1200.00,
            'total_payable'       => 1260.00,
            'payment_method'      => 'standard',
            'status'              => 'pending',
            'telr_order_ref'      => 'TELR-PRV-001',
        ]);

        // Public page renders cleanly WITHOUT fee percentages
        $this->get(route('provider-pay.show', $invoice->public_token))
            ->assertOk()
            ->assertSee('تنسيق حفلة خاصة')
            ->assertDontSee('+5%')
            ->assertDontSee('+15%');

        // Mock Telr check returning code 3 (Authorised / Paid)
        Http::fake([
            'https://secure.telr.com/gateway/order.json' => Http::response([
                'order' => ['status' => ['code' => 3, 'text' => 'Paid']],
            ], 200),
        ]);

        // Callback verifies with Telr and credits provider wallet
        $this->get(route('provider-pay.telr.callback', [
            'invoice' => $invoice->invoice_number,
            'status'  => 'success',
        ]))->assertRedirect(route('provider-pay.show', $invoice->public_token));

        $invoice->refresh();
        $provider->refresh();

        $this->assertTrue($invoice->isPaid());
        $this->assertTrue($invoice->isWalletCredited());
        $this->assertEquals(1200.00, (float) $provider->wallet_balance);
        $this->assertEquals(1200.00, (float) $provider->total_earned);

        // Replaying callback MUST NOT double-credit wallet
        $this->get(route('provider-pay.telr.callback', [
            'invoice' => $invoice->invoice_number,
            'status'  => 'success',
        ]))->assertRedirect();

        $this->assertEquals(1200.00, (float) $provider->fresh()->wallet_balance);

        // Admin settles & zeroes wallet at end of day
        $this->actingAs($admin, 'web')
            ->post(route('admin.providers.wallet.settle', $provider), [
                'settle_mode' => 'full',
                'reference'   => 'TRF-EOD-001',
                'notes'       => 'تحويل نهاية اليوم',
            ])->assertRedirect();

        $provider->refresh();
        $this->assertEquals(0.00, (float) $provider->wallet_balance);
        $this->assertEquals(1200.00, (float) $provider->total_settled);
        $this->assertDatabaseHas('provider_wallet_transactions', [
            'service_provider_id' => $provider->id,
            'type'                => 'payout',
            'reference'           => 'TRF-EOD-001',
            'balance_after'       => 0.00,
        ]);
    }

    public function test_forgot_password_otp_flow_sends_email_enforces_security_and_resets_password(): void
    {
        Mail::fake();

        $provider = $this->createProvider([
            'email'    => 'otp-user@example.com',
            'phone'    => '0554443332',
            'password' => 'OldPassword123!',
        ]);

        // 1. Request OTP using phone number
        $this->post(route('provider.password.otp.send'), [
            'identifier' => '0554443332',
        ])->assertRedirect(route('provider.password.verify.form'));

        $capturedCode = null;
        Mail::assertSent(ProviderPasswordResetOtpMail::class, function (ProviderPasswordResetOtpMail $mail) use ($provider, &$capturedCode) {
            $capturedCode = $mail->otpCode;
            return $mail->hasTo($provider->email) && strlen($mail->otpCode) === 6;
        });

        $this->assertNotNull($capturedCode);

        // 2. Wrong OTP is rejected and increments attempt counter
        $wrongCode = $capturedCode === '000000' ? '111111' : '000000';
        $this->post(route('provider.password.otp.reset'), [
            'otp_code'              => $wrongCode,
            'password'              => 'NewSecret999!',
            'password_confirmation' => 'NewSecret999!',
        ])->assertSessionHasErrors('otp_code');

        $otpRow = ProviderPasswordOtp::where('service_provider_id', $provider->id)->latest('id')->firstOrFail();
        $this->assertSame(1, $otpRow->attempts);

        // 3. Valid 6-digit OTP resets password cleanly
        $this->post(route('provider.password.otp.reset'), [
            'otp_code'              => $capturedCode,
            'password'              => 'NewSecret999!',
            'password_confirmation' => 'NewSecret999!',
        ])->assertRedirect(route('provider.login'));

        $provider->refresh();
        $this->assertTrue(Hash::check('NewSecret999!', $provider->password));
        $this->assertFalse(Hash::check('OldPassword123!', $provider->password));

        // 4. Provider can now log in with the new password
        $this->post(route('provider.login.submit'), [
            'login'    => 'otp-user@example.com',
            'password' => 'NewSecret999!',
        ])->assertRedirect(route('provider.dashboard'));
    }

    public function test_admin_pages_and_private_documents_are_isolated_from_providers(): void
    {
        $admin    = $this->createAdmin();
        $docPath  = UploadedFile::fake()->image('secret_id.jpg')->store('provider-documents/1', 'local');
        $provider = $this->createProvider(['national_id_front_path' => $docPath]);

        // Provider CANNOT access admin service-provider routes
        $this->actingAs($provider, 'provider')
            ->get(route('admin.providers.dashboard'))
            ->assertRedirect(route('login'));

        // Admin CAN access isolated dashboard, list, invoices, wallets, mail settings, and private KYC document
        $this->actingAs($admin, 'web')->get(route('admin.providers.dashboard'))->assertOk();
        $this->actingAs($admin, 'web')->get(route('admin.providers.index'))->assertOk();
        $this->actingAs($admin, 'web')->get(route('admin.providers.show', $provider))->assertOk();
        $this->actingAs($admin, 'web')->get(route('admin.providers.invoices.index'))->assertOk();
        $this->actingAs($admin, 'web')->get(route('admin.providers.wallets.index'))->assertOk();
        $this->actingAs($admin, 'web')->get(route('admin.providers.mail.index'))->assertOk();
        $this->actingAs($admin, 'web')->get(route('admin.providers.document', [$provider, 'national_id_front']))->assertOk();
    }

    public function test_all_four_service_invoice_templates_and_tamara_provider_payment_credit_wallet(): void
    {
        foreach (['coordination', 'photography', 'serviced_suites', 'other'] as $idx => $type) {
            $prov = $this->createProvider([
                'email'                  => "type{$idx}@example.com",
                'phone'                  => "055000010{$idx}",
                'service_type'           => $type,
                'custom_service_name'    => $type === 'other' ? 'ضيافة قهوة عربية' : null,
                'property_kind'          => $type === 'serviced_suites' ? 'شاليه' : null,
                'subscription_status'    => 'active',
                'subscription_starts_at' => now(),
                'subscription_ends_at'   => now()->addMonth(),
            ]);

            $inv = ProviderInvoice::create([
                'service_provider_id' => $prov->id,
                'service_type'        => $type,
                'customer_name'       => 'عميل تجريبي',
                'customer_phone'      => '0551230000',
                'title'               => "خدمة {$type}",
                'amount'              => 400.00,
                'total_payable'       => 400.00,
                'status'              => 'pending',
            ]);

            $this->get(route('provider-pay.show', $inv->public_token))
                ->assertOk()
                ->assertSee("خدمة {$type}");

            $this->actingAs($prov, 'provider')
                ->get(route('provider.invoices.print', $inv))
                ->assertOk()
                ->assertSee("خدمة {$type}");
        }

        // Verify Tamara checkout + callback credits wallet atomically
        $tamaraProvider = ServiceProvider::where('email', 'type0@example.com')->firstOrFail();
        $tamaraInv      = $tamaraProvider->invoices()->firstOrFail();

        Http::fake([
            'https://api-sandbox.tamara.co/checkout' => Http::response([
                'order_id'     => 'tmr-prv-999',
                'checkout_id'  => 'chk-prv-999',
                'checkout_url' => 'https://checkout-sandbox.tamara.co/prv-999',
                'status'       => 'new',
            ], 200),
            'https://api-sandbox.tamara.co/orders/tmr-prv-999' => Http::response([
                'order_id' => 'tmr-prv-999',
                'status'   => 'approved',
            ], 200),
            'https://api-sandbox.tamara.co/orders/tmr-prv-999/authorise' => Http::response([
                'order_id'      => 'tmr-prv-999',
                'status'        => 'authorised',
                'auto_captured' => true,
            ], 200),
        ]);

        $this->post(route('provider-pay.select', $tamaraInv->public_token), [
            'method' => 'tamara',
        ])->assertRedirect('https://checkout-sandbox.tamara.co/prv-999');

        $this->get(route('provider-pay.tamara.callback', [
            'token'   => $tamaraInv->public_token,
            'status'  => 'success',
            'orderId' => 'tmr-prv-999',
        ]))->assertRedirect(route('provider-pay.show', $tamaraInv->public_token));

        $this->assertTrue($tamaraInv->fresh()->isPaid());
        $this->assertEquals(400.00, (float) $tamaraProvider->fresh()->wallet_balance);
    }

    public function test_detailed_forms_for_photography_coordination_with_attachment_and_apartments_render_luxury_invoice(): void
    {
        $admin = $this->createAdmin();

        // 1. Coordination invoice with quote attachment + financial breakdown
        $coord = $this->createProvider([
            'email'                  => 'coord-detail@example.com',
            'phone'                  => '0558800001',
            'service_type'           => 'coordination',
            'subscription_status'    => 'active',
            'subscription_starts_at' => now(),
            'subscription_ends_at'   => now()->addMonth(),
        ]);

        $this->actingAs($coord, 'provider')->post(route('provider.invoices.store'), [
            'customer_name'        => 'أحمد المنصور',
            'customer_phone'       => '0551231111',
            'customer_national_id' => '1099887766',
            'title'                => 'تنسيق مناسبة',
            'agreed_amount'        => 2000,
            'paid_previously'      => 500,
            'amount'               => 1000,
            'payment_method'       => 'tabby',
            'notes'                => 'تجهيز قبل الساعة 5 مساءً',
            'quote_attachment'     => UploadedFile::fake()->image('quote.jpg'),
            'service_details'      => [
                'region'           => 'الرياض',
                'venue'            => 'قاعة النرجس',
                'event_date'       => '2026-12-10',
                'event_time'       => '19:30',
                'responsible_name' => 'المنسق ناصر',
            ],
        ])->assertRedirect();

        $coordInv = $coord->invoices()->firstOrFail();
        $this->assertEquals(2000.00, (float) $coordInv->agreed_amount);
        $this->assertEquals(500.00, (float) $coordInv->paid_previously);
        $this->assertEquals(1500.00, (float) $coordInv->remaining_amount);
        $this->assertEquals(1000.00, (float) $coordInv->amount);
        $this->assertNotNull($coordInv->quote_attachment_path);
        Storage::disk('local')->assertExists($coordInv->quote_attachment_path);

        $this->get(route('provider-pay.show', $coordInv->public_token))
            ->assertOk()
            ->assertSee('فاتورة إلكترونية')
            ->assertSee('thiqa-gold-logo.png')
            ->assertSee('2,000.00')
            ->assertSee('500.00')
            ->assertSee('1,500.00')
            ->assertSee('1,000.00')
            ->assertSee('قاعة النرجس')
            ->assertSee('المنسق ناصر');

        // Attachment accessible by provider, customer token, and admin
        $this->actingAs($coord, 'provider')->get(route('provider.invoices.attachment', $coordInv))->assertOk();
        $this->get(route('provider-pay.attachment', $coordInv->public_token))->assertOk();
        $this->actingAs($admin, 'web')->get(route('admin.providers.invoices.attachment', $coordInv))->assertOk();

        // 2. Serviced Suites / Apartment invoice with floor, suite, nights, deposit, full/partial, cash
        $apt = $this->createProvider([
            'email'                  => 'apt-detail@example.com',
            'phone'                  => '0558800002',
            'service_type'           => 'serviced_suites',
            'property_kind'          => 'شقة',
            'subscription_status'    => 'active',
            'subscription_starts_at' => now(),
            'subscription_ends_at'   => now()->addMonth(),
        ]);

        $this->actingAs($apt, 'provider')->post(route('provider.invoices.store'), [
            'customer_name'        => 'سلطان العنزي',
            'customer_phone'       => '0552223334',
            'customer_national_id' => '1088776655',
            'title'                => 'حجز شقة فندقية',
            'agreed_amount'        => 1800,
            'deposit_amount'       => 300,
            'paid_previously'      => 600,
            'amount'               => 1500,
            'payment_method'       => 'cash',
            'payment_type'         => 'partial',
            'service_details'      => [
                'floor_number'   => 'الدور الرابع',
                'suite_number'   => 'شقة 402',
                'check_in_date'  => '2026-12-01',
                'check_out_date' => '2026-12-04',
                'nights'         => '3',
                'guests_count'   => '4',
            ],
        ])->assertRedirect();

        $aptInv = $apt->invoices()->firstOrFail();
        $this->assertEquals(1500.00, (float) $aptInv->remaining_amount);
        $this->assertSame('partial', $aptInv->payment_type);
        $this->assertSame('cash', $aptInv->payment_method);

        $this->get(route('provider-pay.show', $aptInv->public_token))
            ->assertOk()
            ->assertSee('الدور الرابع')
            ->assertSee('شقة 402')
            ->assertSee('3 ليلة')
            ->assertSee('دفع جزئي')
            ->assertSee('300.00');
    }
}
