<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_receipts', function (Blueprint $table) {
            if (! Schema::hasColumn('purchase_receipts', 'discount_type')) {
                $table->string('discount_type')->nullable()->after('discount_amount');
            }
            if (! Schema::hasColumn('purchase_receipts', 'discount_value')) {
                $table->decimal('discount_value', 14, 2)->default(0)->after('discount_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_receipts', function (Blueprint $table) {
            $table->dropColumn(['discount_type', 'discount_value']);
        });
    }
};