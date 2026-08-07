<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('purchase_receipt_items', 'discount')) {
            Schema::table('purchase_receipt_items', function (Blueprint $table) {
                $table->decimal('discount', 10, 2)->default(0)->after('unit_cost');
            });
        }
    }

    public function down(): void
    {
        Schema::table('purchase_receipt_items', function (Blueprint $table) {
            $table->dropColumn('discount');
        });
    }
};