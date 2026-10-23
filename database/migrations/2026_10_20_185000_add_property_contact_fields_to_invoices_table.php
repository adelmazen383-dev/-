<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'property_name')) {
                $table->string('property_name')->nullable()->after('apartment_number');
            }
            if (!Schema::hasColumn('invoices', 'property_location')) {
                $table->string('property_location')->nullable()->after('property_name');
            }
            if (!Schema::hasColumn('invoices', 'property_phone')) {
                $table->string('property_phone', 50)->nullable()->after('property_location');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'property_name')) {
                $table->dropColumn(['property_name', 'property_location', 'property_phone']);
            }
        });
    }
};
