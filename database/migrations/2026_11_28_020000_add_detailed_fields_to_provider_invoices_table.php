<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('provider_invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('provider_invoices', 'customer_national_id')) {
                $table->string('customer_national_id', 30)->nullable()->after('customer_email');
            }
            if (!Schema::hasColumn('provider_invoices', 'agreed_amount')) {
                $table->decimal('agreed_amount', 12, 2)->default(0.00)->after('amount');
            }
            if (!Schema::hasColumn('provider_invoices', 'deposit_amount')) {
                $table->decimal('deposit_amount', 12, 2)->default(0.00)->after('agreed_amount');
            }
            if (!Schema::hasColumn('provider_invoices', 'paid_previously')) {
                $table->decimal('paid_previously', 12, 2)->default(0.00)->after('deposit_amount');
            }
            if (!Schema::hasColumn('provider_invoices', 'remaining_amount')) {
                $table->decimal('remaining_amount', 12, 2)->default(0.00)->after('paid_previously');
            }
            if (!Schema::hasColumn('provider_invoices', 'payment_type')) {
                $table->string('payment_type', 20)->nullable()->after('payment_method'); // full, partial
            }
            if (!Schema::hasColumn('provider_invoices', 'quote_attachment_path')) {
                $table->string('quote_attachment_path')->nullable()->after('notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('provider_invoices', function (Blueprint $table) {
            $cols = [
                'customer_national_id',
                'agreed_amount',
                'deposit_amount',
                'paid_previously',
                'remaining_amount',
                'payment_type',
                'quote_attachment_path',
            ];
            $existing = array_filter($cols, fn ($c) => Schema::hasColumn('provider_invoices', $c));
            if (!empty($existing)) {
                $table->dropColumn($existing);
            }
        });
    }
};
