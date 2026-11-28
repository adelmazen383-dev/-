<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_subscription_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 50)->unique();
            $table->uuid('public_token')->unique();
            $table->foreignId('service_provider_id')->constrained('service_providers')->cascadeOnDelete();
            $table->foreignId('provider_subscription_id')->nullable()->constrained('provider_subscriptions')->nullOnDelete();

            // Locked snapshot of the Admin-configured plan & price at checkout time
            $table->unsignedSmallInteger('duration_months');
            $table->string('plan_name', 150);
            $table->decimal('amount', 10, 2);

            // Payment gateway tracking
            $table->string('payment_method', 30)->default('telr'); // telr | tamara
            $table->string('status', 35)->default('pending_payment')->index();
            // pending_payment | paid_pending_approval | approved | rejected | failed

            $table->string('telr_order_ref', 120)->nullable();
            $table->text('telr_transaction_url')->nullable();
            $table->string('tamara_order_id', 120)->nullable();
            $table->string('tamara_checkout_id', 120)->nullable();
            $table->string('tamara_order_reference_id', 120)->nullable();
            $table->string('tamara_status', 40)->nullable();
            $table->text('tamara_url')->nullable();

            $table->dateTime('paid_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->index(['service_provider_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_subscription_orders');
    }
};
