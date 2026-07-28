<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Fix status ENUM to include 'signed_by_lessee'
        DB::statement("ALTER TABLE contracts MODIFY COLUMN status ENUM('draft', 'sent', 'viewed', 'signed_by_lessee', 'signed', 'rejected', 'cancelled') DEFAULT 'draft'");

        // 2. Add tourism_license_number column (was missing from migrations)
        if (!Schema::hasColumn('contracts', 'tourism_license_number')) {
            Schema::table('contracts', function (Blueprint $table) {
                $table->string('tourism_license_number')->nullable()->after('additional_terms');
            });
        }

        // 3. Add deposit_amount column if not exists
        if (!Schema::hasColumn('contracts', 'deposit_amount')) {
            Schema::table('contracts', function (Blueprint $table) {
                $table->decimal('deposit_amount', 10, 2)->default(500)->after('rent_amount');
            });
        }

        // 4. Change date columns to datetime if needed
        Schema::table('contracts', function (Blueprint $table) {
            $table->dateTime('start_date')->change();
            $table->dateTime('end_date')->change();
        });
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE contracts MODIFY COLUMN status ENUM('draft', 'sent', 'viewed', 'signed', 'rejected', 'cancelled') DEFAULT 'draft'");

        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn('tourism_license_number');
            $table->dropColumn('deposit_amount');
            $table->date('start_date')->change();
            $table->date('end_date')->change();
        });
    }
};
