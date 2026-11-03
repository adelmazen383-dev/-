<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'payment_method')) {
                $table->string('payment_method', 30)->default('all')->after('status');
            }
            if (!Schema::hasColumn('invoices', 'fee_percentage')) {
                $table->decimal('fee_percentage', 5, 2)->default(0.00)->after('payment_method');
            }
            if (!Schema::hasColumn('invoices', 'fee_amount')) {
                $table->decimal('fee_amount', 10, 2)->default(0.00)->after('fee_percentage');
            }
            if (!Schema::hasColumn('invoices', 'total_payable')) {
                $table->decimal('total_payable', 10, 2)->nullable()->after('fee_amount');
            }
            if (!Schema::hasColumn('invoices', 'tabby_url')) {
                $table->text('tabby_url')->nullable()->after('telr_transaction_url');
            }
            if (!Schema::hasColumn('invoices', 'tamara_url')) {
                $table->text('tamara_url')->nullable()->after('tabby_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $columns = [];
            foreach (['payment_method', 'fee_percentage', 'fee_amount', 'total_payable', 'tabby_url', 'tamara_url'] as $col) {
                if (Schema::hasColumn('invoices', $col)) {
                    $columns[] = $col;
                }
            }
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
