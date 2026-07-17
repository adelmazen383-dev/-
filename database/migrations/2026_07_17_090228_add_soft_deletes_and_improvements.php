<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Soft Deletes for contracts
        Schema::table('contracts', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Soft Deletes for customers
        Schema::table('customers', function (Blueprint $table) {
            $table->softDeletes();
            $table->string('email')->nullable()->after('phone');
            $table->text('address')->nullable()->after('email');
        });

        // Add role field to users for quick access
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('customers', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['email', 'address']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
