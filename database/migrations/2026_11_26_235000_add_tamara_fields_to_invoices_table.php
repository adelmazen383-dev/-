<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'tamara_order_id')) {
                $table->string('tamara_order_id', 100)->nullable()->index()->after('tamara_url');
            }
            if (!Schema::hasColumn('invoices', 'tamara_checkout_id')) {
                $table->string('tamara_checkout_id', 100)->nullable()->after('tamara_order_id');
            }
            if (!Schema::hasColumn('invoices', 'tamara_order_reference_id')) {
                $table->string('tamara_order_reference_id', 120)->nullable()->index()->after('tamara_checkout_id');
            }
            if (!Schema::hasColumn('invoices', 'tamara_status')) {
                $table->string('tamara_status', 40)->nullable()->after('tamara_order_reference_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'tamara_order_id')) {
                $table->dropIndex(['tamara_order_id']);
            }
            if (Schema::hasColumn('invoices', 'tamara_order_reference_id')) {
                $table->dropIndex(['tamara_order_reference_id']);
            }

            $columns = [];
            foreach (['tamara_order_id', 'tamara_checkout_id', 'tamara_order_reference_id', 'tamara_status'] as $col) {
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
