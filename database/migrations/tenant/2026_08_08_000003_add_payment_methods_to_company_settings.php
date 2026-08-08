<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->json('payment_methods')->nullable()->after('tax_type');
            $table->string('default_payment_method', 20)->nullable()->after('payment_methods');
        });

        // Seed from POS settings
        if (Schema::hasTable('pos_settings')) {
            $pos = DB::table('pos_settings')->where('id', 1)->first();
            if ($pos && ! empty($pos->payment_methods)) {
                DB::table('company_settings')->where('id', 1)->update([
                    'payment_methods' => $pos->payment_methods,
                    'default_payment_method' => $pos->default_payment_method ?? '1010',
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn(['payment_methods', 'default_payment_method']);
        });
    }
};
