<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->enum('type', ['lessee', 'lessor'])->default('lessee')->after('address');
        });

        Schema::table('contracts', function (Blueprint $table) {
            $table->foreignId('lessor_id')->nullable()->after('customer_id')->constrained('customers')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropForeign(['lessor_id']);
            $table->dropColumn('lessor_id');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
