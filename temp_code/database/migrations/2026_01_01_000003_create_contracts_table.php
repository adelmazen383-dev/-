<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->string('contract_number')->unique();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->unsignedBigInteger('template_id'); // We'll assume contract_templates table exists
            
            $table->text('property_details');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('rent_amount', 10, 2);
            $table->string('payment_method');
            $table->text('additional_terms')->nullable();

            $table->enum('status', ['draft', 'sent', 'viewed', 'signed', 'rejected', 'cancelled'])->default('draft');
            $table->uuid('verification_token')->unique();
            $table->string('pdf_path')->nullable();
            $table->string('signed_pdf_path')->nullable();
            $table->string('qr_path')->nullable();
            
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('viewed_at')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
