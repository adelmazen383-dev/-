<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            // Fix #8: Add proper foreign key for template_id
            $table->foreign('template_id')
                  ->references('id')
                  ->on('contract_templates')
                  ->onDelete('restrict');

            // Fix #9: Add performance indexes
            $table->index('status');
            $table->index('created_at');

            // Fix #13: Track which admin created the contract
            $table->foreignId('created_by')
                  ->nullable()
                  ->after('additional_terms')
                  ->constrained('users')
                  ->onDelete('set null');
        });

        // Fix #9: Add phone index to customers
        Schema::table('customers', function (Blueprint $table) {
            $table->index('phone');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropForeign(['template_id']);
            $table->dropForeign(['created_by']);
            $table->dropIndex(['status']);
            $table->dropIndex(['created_at']);
            $table->dropColumn('created_by');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['phone']);
        });
    }
};
