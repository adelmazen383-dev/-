<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->string('customer_name');
            $table->string('phone', 20);
            $table->string('national_id', 20);
            $table->string('apartment_number', 50);
            $table->decimal('amount', 10, 2);
            $table->string('status', 20)->default('pending')->index(); // pending, paid, failed, cancelled
            $table->string('telr_order_ref')->nullable()->index();
            $table->text('telr_transaction_url')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
