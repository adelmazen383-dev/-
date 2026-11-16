<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });

            // Insert default fee settings
            $defaults = [
                ['key' => 'fee_visa_percent', 'value' => '5.00'],
                ['key' => 'fee_apple_pay_percent', 'value' => '5.00'],
                ['key' => 'fee_tabby_percent', 'value' => '15.00'],
                ['key' => 'fee_tamara_percent', 'value' => '15.00'],
            ];

            foreach ($defaults as $item) {
                DB::table('settings')->insert(array_merge($item, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
