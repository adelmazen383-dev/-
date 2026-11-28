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

        // 3. Valid 6-digit OTP resets password cleanly and redirects to unified login
        $this->post(route('provider.password.otp.reset'), [
            'otp_code'              => $capturedCode,
            'password'              => 'NewSecret999!',
            'password_confirmation' => 'NewSecret999!',
        ])->assertRedirect(route('login'));

        $provider->refresh();
        $this->assertTrue(Hash::check('NewSecret999!', $provider->password));
        $this->assertFalse(Hash::check('OldPassword123!', $provider->password));

        // 4. Provider can now log in with the new password via unified /login (email only)
        $this->post(route('login'), [
            'email'    => 'otp-user@example.com',
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

    public function test_uploaded_images_and_pdfs_stream_inline_with_exact_mime_headers_and_download_mode(): void
    {
        $admin = $this->createAdmin();

        // Register provider with BOTH a real PNG image AND a valid PDF file
        $pdfBytes = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF\n";
        $pdfFile  = UploadedFile::fake()->createWithContent('tourism_license.pdf', $pdfBytes);
        $imgFile  = UploadedFile::fake()->image('national_id.png', 300, 200);

        $this->post(route('provider.register.submit'), [
            'name'                  => 'نواف العتيبي',
            'business_name'         => 'أجنحة نواف',
            'email'                 => 'pdf-img@example.com',
            'phone'                 => '0557770001',
            'service_type'          => 'serviced_suites',
            'property_kind'         => 'جناح فندقي',
            'service_description'   => 'أجنحة فندقية مخدومة بالكامل في جدة',
            'bank_name'             => 'الراجحي',
            'bank_account_name'     => 'نواف العتيبي',
            'iban'                  => 'SA0380000000608010167519',
            'bank_owner_confirmed'  => '1',
            'password'              => 'Secret123!',
            'password_confirmation' => 'Secret123!',
            'national_id_front'     => $imgFile,
            'national_id_back'      => UploadedFile::fake()->image('id_back.jpg', 300, 200),
            'tourism_license'       => $pdfFile,
        ])->assertRedirect(route('provider.dashboard'));

        $provider = ServiceProvider::where('email', 'pdf-img@example.com')->firstOrFail();

        // Admin views PNG image inline -> exact image/png + inline disposition
        $imgRes = $this->actingAs($admin, 'web')
            ->get(route('admin.providers.document', [$provider, 'national_id_front']));
        $imgRes->assertOk();
        $this->assertStringContainsString('image/png', (string) $imgRes->headers->get('Content-Type'));
        $this->assertStringContainsString('inline', (string) $imgRes->headers->get('Content-Disposition'));

        // Admin views PDF inline -> exact application/pdf + inline disposition + Accept-Ranges
        $pdfRes = $this->actingAs($admin, 'web')
            ->get(route('admin.providers.document', [$provider, 'tourism_license']));
        $pdfRes->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $pdfRes->headers->get('Content-Type'));
        $this->assertStringContainsString('inline', (string) $pdfRes->headers->get('Content-Disposition'));
        $this->assertSame('bytes', $pdfRes->headers->get('Accept-Ranges'));

        // Download mode (?download=1) -> attachment disposition
        $dlRes = $this->actingAs($admin, 'web')
            ->get(route('admin.providers.document', [$provider, 'tourism_license']) . '?download=1');
        $dlRes->assertOk();
        $this->assertStringContainsString('attachment', (string) $dlRes->headers->get('Content-Disposition'));

        // Also test PDF quote attachment on an invoice
        $provider->update([
            'subscription_status'    => 'active',
            'subscription_starts_at' => now(),
            'subscription_ends_at'   => now()->addMonth(),
        ]);

        $this->actingAs($provider, 'provider')->post(route('provider.invoices.store'), [
            'customer_name'    => 'ماجد السبيعي',
            'customer_phone'   => '0553334445',
            'title'            => 'حجز جناح VIP',
            'agreed_amount'    => 1000,
            'amount'           => 1000,
            'quote_attachment' => UploadedFile::fake()->createWithContent('quote.pdf', $pdfBytes),
        ])->assertRedirect();

        $inv = $provider->invoices()->firstOrFail();

        $quotePdfRes = $this->get(route('provider-pay.attachment', $inv->public_token));
        $quotePdfRes->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $quotePdfRes->headers->get('Content-Type'));
        $this->assertStringContainsString('inline', (string) $quotePdfRes->headers->get('Content-Disposition'));
    }

    public function test_seo_landing_page_sitemap_xml_structured_data_and_private_noindex(): void
    {
        // Public SEO landing page (/providers)
        $this->get(route('provider.landing'))
            ->assertOk()
            ->assertSee('rel="canonical"', false)
            ->assertSee('hreflang="ar-SA"', false)
            ->assertSee('og-thiqa.png', false)
            ->assertSee('application/ld+json', false)
            ->assertSee('BreadcrumbList', false)
            ->assertSee('FAQPage', false)
            ->assertSee('og:title', false)
            ->assertSee('بوابة مقدمي الخدمات');

        // Homepage SEO + JSON-LD + Internal Links to /providers & /provider/register
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('rel="canonical"', false)
            ->assertSee('hreflang="ar-SA"', false)
            ->assertSee('og-thiqa.png', false)
            ->assertSee('application/ld+json', false)
            ->assertSee('FAQPage', false)
            ->assertSee('ProfessionalService', false)
            ->assertSee('og:site_name', false)
            ->assertSee(route('provider.landing'), false)
            ->assertSee(route('provider.register'), false);

        // Dynamic XML Sitemap
        $sitemap = $this->get(route('seo.sitemap'));
        $sitemap->assertOk();
        $this->assertStringContainsString('application/xml', (string) $sitemap->headers->get('Content-Type'));
        $sitemap->assertSee('/providers', false)
            ->assertSee('/provider/register', false)
            ->assertSee('/login', false)
            ->assertSee('/verify', false)
            ->assertDontSee('/provider/login', false)
            ->assertSee('<urlset', false);

        // Private OTP & customer payment pages MUST be noindex, nofollow
        $this->get(route('provider.password.request'))
            ->assertOk()
            ->assertSee('noindex, nofollow', false);

        $provider = $this->createProvider();
        $inv = ProviderInvoice::create([
            'service_provider_id' => $provider->id,
            'service_type'        => 'coordination',
            'customer_name'       => 'عميل خاص',
            'customer_phone'      => '0550009999',
            'title'               => 'فاتورة خاصة',
            'amount'              => 300,
            'total_payable'       => 300,
            'status'              => 'pending',
        ]);

        $this->get(route('provider-pay.show', $inv->public_token))
            ->assertOk()
            ->assertSee('noindex, nofollow', false);
    }

    public function test_provider_login_accepts_email_only_rejects_phone_and_logout_works(): void
    {
        $admin = $this->createAdmin();
        $admin->update(['password' => bcrypt('AdminSecret123!')]);

        $provider = $this->createProvider([
            'email'    => 'emailonly@example.com',
            'phone'    => '0556667778',
            'password' => 'SecretPass123!',
        ]);

        // 0. Legacy /provider/login cleanly 301-redirects to unified /login
        $this->get(route('provider.login'))
            ->assertStatus(301)
            ->assertRedirect(route('login'));

        // Unified /login page renders with Thiqa identity & registration CTA
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('بوابة الدخول الموحدة')
            ->assertSee(route('provider.register'), false);

        // 1. Attempting to log in with phone number MUST fail validation on email
        $this->post(route('login'), [
            'email'    => '0556667778',
            'password' => 'SecretPass123!',
        ])->assertSessionHasErrors('email');

        $this->assertGuest('provider');
        $this->assertGuest('web');

        // 2. Logging in as Service Provider via unified /login succeeds & routes to provider.dashboard
        $this->post(route('login'), [
            'email'    => 'EmailOnly@Example.com',
            'password' => 'SecretPass123!',
        ])->assertRedirect(route('provider.dashboard'));

        $this->assertAuthenticatedAs($provider, 'provider');
        $this->assertGuest('web');

        // 3. Dashboard and Profile show logout action cleanly
        $this->actingAs($provider, 'provider')
            ->get(route('provider.dashboard'))
            ->assertOk()
            ->assertSee(route('provider.logout'), false)
            ->assertSee('تسجيل الخروج');

        $this->actingAs($provider, 'provider')
            ->get(route('provider.profile'))
            ->assertOk()
            ->assertSee('تسجيل الخروج الآن');

        // 4. Logging out signs provider out cleanly and redirects to unified /login
        $this->actingAs($provider, 'provider')
            ->post(route('provider.logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest('provider');

        // 5. Logging in as Admin via the EXACT SAME unified /login succeeds & routes to Admin dashboard
        $this->post(route('login'), [
            'email'    => $admin->email,
            'password' => 'AdminSecret123!',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($admin, 'web');
        $this->assertGuest('provider');
    }

    public function test_admin_subscription_pricing_control_panel_updates_provider_subscription_and_contact_page(): void
    {
        $admin    = $this->createAdmin();
        $provider = $this->createProvider([
            'email'               => 'sub-check@example.com',
            'phone'               => '0559990011',
            'subscription_status' => 'inactive',
        ]);

        // 1. Admin can view and save subscription pricing & contact channels
        $this->actingAs($admin, 'web')
            ->get(route('admin.providers.subscription-settings.index'))
            ->assertOk();

        $this->actingAs($admin, 'web')
            ->post(route('admin.providers.subscription-settings.save'), [
                'plan_1m_enabled'  => '1',
                'plan_1m_price'    => '249',
                'plan_1m_label'    => 'الباقة الشهرية المميزة',
                'plan_3m_enabled'  => '1',
                'plan_3m_price'    => '650',
                'plan_3m_label'    => 'باقة 3 أشهر الذهبية',
                'plan_6m_enabled'  => '1',
                'plan_6m_price'    => '1199',
                'plan_6m_label'    => 'باقة 6 أشهر',
                'plan_12m_enabled' => '0',
                'plan_12m_price'   => '1999',
                'plan_12m_label'   => 'الباقة السنوية',
                'features_text'    => "فواتير غير محدودة\nدعم فني فوري عبر الواتساب",
                'whatsapp'         => '0559991122',
                'phone'            => '0559991122',
                'email'            => 'sub@thiqa.me',
                'bank_name'        => 'مصرف الراجحي',
                'bank_account'     => 'مؤسسة موقع أمر التجارية',
                'bank_iban'        => 'sa0380000000608010167519',
                'contact_note'     => 'راسلنا الآن لتفعيل باقتك خلال دقائق.',
            ])->assertRedirect();

        // 2. Newly registered provider sees the configured plans, prices & direct WhatsApp link
        $this->actingAs($provider, 'provider')
            ->get(route('provider.subscription'))
            ->assertOk()
            ->assertSee('الباقة الشهرية المميزة')
            ->assertSee('249.00')
            ->assertSee('650.00')
            ->assertSee('1,199.00')
            ->assertDontSee('1,999.00')
            ->assertSee('https://wa.me/966559991122', false)
            ->assertSee('SA0380000000608010167519')
            ->assertSee('sub@thiqa.me')
            ->assertSee('راسلنا الآن لتفعيل باقتك خلال دقائق.');

        $this->actingAs($provider, 'provider')
            ->get(route('provider.dashboard'))
            ->assertOk()
            ->assertSee('https://wa.me/966559991122', false)
            ->assertSee(route('provider.subscription'), false);
    }

    public function test_provider_profile_is_strictly_read_only_while_password_and_admin_edits_work(): void
    {
        $admin    = $this->createAdmin();
        $provider = $this->createProvider([
            'name'                => 'فهد الأصلي',
            'business_name'       => 'استوديو فهد',
            'email'               => 'locked@example.com',
            'phone'               => '0551119990',
            'password'            => 'OldPass123!',
            'service_type'        => 'photography',
            'service_description' => 'تصوير مناسبات احترافي',
            'iban'                => 'SA0380000000608010167519',
        ]);

        // 1. Profile view shows Read-Only badge and does NOT expose update form
        $this->actingAs($provider, 'provider')
            ->get(route('provider.profile'))
            ->assertOk()
            ->assertSee('للعرض فقط')
            ->assertDontSee('حفظ التعديلات');

        // 2. Direct PUT /provider/profile is blocked with 403 Forbidden and DB stays unchanged
        $this->actingAs($provider, 'provider')
            ->put(route('provider.profile.update'), [
                'name'          => 'اسم مختلف',
                'business_name' => 'نشاط مختلف',
                'service_type'  => 'coordination',
                'iban'          => 'SA9999999999999999999999',
            ])->assertForbidden();

        $provider->refresh();
        $this->assertSame('فهد الأصلي', $provider->name);
        $this->assertSame('استوديو فهد', $provider->business_name);
        $this->assertSame('photography', $provider->service_type);
        $this->assertSame('SA0380000000608010167519', $provider->iban);

        // 3. Provider CAN still change their own password
        $this->actingAs($provider, 'provider')
            ->put(route('provider.profile.password'), [
                'current_password'      => 'OldPass123!',
                'password'              => 'NewStrongPass99!',
                'password_confirmation' => 'NewStrongPass99!',
            ])->assertRedirect();

        $this->assertTrue(Hash::check('NewStrongPass99!', $provider->fresh()->password));

        // 4. Admin CAN update provider's locked details
        $this->actingAs($admin, 'web')
            ->put(route('admin.providers.update', $provider), [
                'name'                => 'فهد المعدل إدارياً',
                'business_name'       => 'استوديو فهد المعتمد',
                'service_type'        => 'coordination',
                'service_description' => 'تنسيق مناسبات وحفلات معتمد من الإدارة',
                'city'                => 'الرياض',
                'bank_name'           => 'مصرف الإنماء',
                'bank_account_name'   => 'فهد المعدل إدارياً',
                'iban'                => 'SA1122334455667788990011',
            ])->assertRedirect();

        $provider->refresh();
        $this->assertSame('فهد المعدل إدارياً', $provider->name);
        $this->assertSame('استوديو فهد المعتمد', $provider->business_name);
        $this->assertSame('coordination', $provider->service_type);
        $this->assertSame('SA1122334455667788990011', $provider->iban);
    }
}
