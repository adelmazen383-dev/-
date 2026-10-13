<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('suite_number', 50)->nullable()->after('apartment_number');
            $table->date('check_in_date')->nullable()->after('suite_number');
            $table->date('check_out_date')->nullable()->after('check_in_date');
            $table->decimal('agreed_amount', 10, 2)->nullable()->after('amount');
            $table->decimal('amount_paid', 10, 2)->default(0)->after('agreed_amount');
            $table->decimal('remaining_amount', 10, 2)->default(0)->after('amount_paid');
            $table->decimal('deposit_amount', 10, 2)->default(500)->after('remaining_amount');
            $table->string('booking_card_token', 64)->nullable()->unique()->after('telr_transaction_url');
            $table->text('notes')->nullable()->after('booking_card_token');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'suite_number',
                'check_in_date',
                'check_out_date',
                'agreed_amount',
                'amount_paid',
                'remaining_amount',
                'deposit_amount',
                'booking_card_token',
                'notes',
            ]);
        });
    }
};
