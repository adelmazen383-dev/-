<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('business_name');
            $table->string('email')->unique();
            $table->string('phone', 30)->unique();
            $table->string('password');

            // Service classification (4 types: coordination, photography, serviced_suites, other)
            $table->string('service_type', 40)->index();
            $table->string('property_kind', 60)->nullable(); // شقة، فيلا، شاليه، جناح فندقي
            $table->string('custom_service_name')->nullable();
            $table->text('service_description')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('commercial_register', 100)->nullable();
            $table->string('national_id_number', 30)->nullable();

            // KYC uploaded documents (stored on private local disk)
            $table->string('national_id_front_path')->nullable();
            $table->string('national_id_back_path')->nullable();
            $table->string('tourism_license_path')->nullable();
            $table->string('commercial_doc_path')->nullable();
            $table->string('photography_license_path')->nullable();

            // Payout bank details
            $table->string('bank_name', 120)->nullable();
            $table->string('bank_account_name', 150)->nullable();
            $table->string('iban', 50)->nullable();
            $table->boolean('bank_owner_confirmed')->default(false);

            // Account & monthly subscription state
            $table->boolean('is_active')->default(true)->index();
            $table->string('subscription_status', 30)->default('inactive')->index(); // inactive, active, expired, suspended
            $table->timestamp('subscription_starts_at')->nullable();
            $table->timestamp('subscription_ends_at')->nullable()->index();

            // Wallet accounting
            $table->decimal('wallet_balance', 12, 2)->default(0.00);
            $table->decimal('total_earned', 12, 2)->default(0.00);
            $table->decimal('total_settled', 12, 2)->default(0.00);

            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('provider_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_provider_id')->constrained('service_providers')->cascadeOnDelete();
            $table->string('plan_name', 120)->default('اشتراك شهري');
            $table->unsignedTinyInteger('duration_months')->default(1);
            $table->decimal('amount', 10, 2)->default(0.00);
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('status', 30)->default('active')->index(); // active, expired, cancelled
            $table->string('payment_reference', 120)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('activated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('provider_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_provider_id')->constrained('service_providers')->cascadeOnDelete();
            $table->string('invoice_number', 60)->unique();
            $table->string('public_token', 64)->unique();
            $table->string('service_type', 40)->index();

            // Customer details
            $table->string('customer_name');
            $table->string('customer_phone', 30);
            $table->string('customer_email')->nullable();

            // Invoice details (customizable per service type)
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('service_details')->nullable();

            // Financial breakdown
            $table->decimal('amount', 12, 2); // Net amount credited to provider wallet
            $table->decimal('gateway_fee_percentage', 5, 2)->default(0.00);
            $table->decimal('gateway_fee_amount', 12, 2)->default(0.00);
            $table->decimal('total_payable', 12, 2); // Total paid by customer

            // Payment & verification state
            $table->string('payment_method', 30)->default('all'); // all, standard, apple_pay, tabby, tamara
            $table->string('status', 30)->default('pending')->index(); // pending, paid, failed, cancelled
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('wallet_credited_at')->nullable()->index();

            // Gateway references (isolated from core site invoices)
            $table->string('telr_order_ref', 120)->nullable()->index();
            $table->text('telr_transaction_url')->nullable();
            $table->string('tamara_order_id', 120)->nullable()->index();
            $table->string('tamara_checkout_id', 120)->nullable();
            $table->string('tamara_order_reference_id', 140)->nullable()->index();
            $table->string('tamara_status', 40)->nullable();
            $table->text('tamara_url')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('provider_wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_provider_id')->constrained('service_providers')->cascadeOnDelete();
            $table->foreignId('provider_invoice_id')->nullable()->constrained('provider_invoices')->nullOnDelete();
            $table->string('type', 30)->index(); // credit, payout, adjustment
            $table->decimal('amount', 12, 2);
            $table->decimal('balance_before', 12, 2);
            $table->decimal('balance_after', 12, 2);
            $table->string('reference', 120)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('provider_password_otps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_provider_id')->constrained('service_providers')->cascadeOnDelete();
            $table->string('email')->index();
            $table->string('phone', 30)->nullable();
            $table->string('code_hash');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->dateTime('expires_at')->index();
            $table->dateTime('used_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_password_otps');
        Schema::dropIfExists('provider_wallet_transactions');
        Schema::dropIfExists('provider_invoices');
        Schema::dropIfExists('provider_subscriptions');
        Schema::dropIfExists('service_providers');
    }
};
