<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('business_settings', function (Blueprint $table) {
            $table->text('refund_policy')->nullable()->after('advance_payment_percent');
            $table->text('exchange_policy')->nullable()->after('refund_policy');
        });
    }

    public function down(): void
    {
        Schema::table('business_settings', function (Blueprint $table) {
            $table->dropColumn(['refund_policy', 'exchange_policy']);
        });
    }
};
