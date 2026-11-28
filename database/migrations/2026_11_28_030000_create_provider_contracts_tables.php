<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_provider_id')->constrained('service_providers')->cascadeOnDelete();
            $table->foreignId('provider_invoice_id')->nullable()->constrained('provider_invoices')->nullOnDelete();
            $table->unsignedBigInteger('template_id')->nullable();

            $table->string('contract_number', 50)->unique();

            // Lessor (First Party - defaults to the Serviced Suites provider)
            $table->string('lessor_name', 150);
            $table->string('lessor_national_id', 40);
            $table->string('lessor_phone', 30);

            // Lessee (Second Party - tenant)
            $table->string('lessee_name', 150);
            $table->string('lessee_national_id', 40);
            $table->string('lessee_phone', 30);

            // Property & Rental Details (exact parity with core contracts)
            $table->text('property_details');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->decimal('rent_amount', 10, 2);
            $table->decimal('deposit_amount', 10, 2)->default(0);
            $table->string('tourism_license_number', 120)->nullable();
            $table->text('additional_terms')->nullable();

            // Lifecycle & Verification
            $table->string('status', 30)->default('draft')->index();
            $table->uuid('verification_token')->unique();
            $table->string('pdf_path')->nullable();
            $table->string('signed_pdf_path')->nullable();

            $table->dateTime('sent_at')->nullable();
            $table->dateTime('viewed_at')->nullable();
            $table->dateTime('signed_at')->nullable();
            $table->dateTime('rejected_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['service_provider_id', 'status']);
            $table->index(['service_provider_id', 'created_at']);
        });

        Schema::create('provider_contract_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_contract_id')->constrained('provider_contracts')->cascadeOnDelete();
            $table->string('role', 20); // lessee | lessor
            $table->string('signature_path');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->dateTime('signed_at');
            $table->timestamps();

            $table->index(['provider_contract_id', 'role']);
        });

        Schema::create('provider_contract_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_contract_id')->constrained('provider_contracts')->cascadeOnDelete();
            $table->string('event');
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_contract_logs');
        Schema::dropIfExists('provider_contract_signatures');
        Schema::dropIfExists('provider_contracts');
    }
};
