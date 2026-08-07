<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_settings', function (Blueprint $table) {
            $table->json('payment_methods')->nullable()->after('purchase_account_id');
            $table->string('default_payment_method')->nullable()->after('payment_methods');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_settings', function (Blueprint $table) {
            $table->dropColumn(['payment_methods', 'default_payment_method']);
        });
    }
};
