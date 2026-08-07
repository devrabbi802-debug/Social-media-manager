<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_invoices', function (Blueprint $table) {
            $table->decimal('advance_applied', 14, 2)->default(0)->after('paid_amount');
        });

        Schema::table('supplier_payments', function (Blueprint $table) {
            $table->foreignId('purchase_order_id')->nullable()->after('purchase_invoice_id')->constrained('purchase_orders')->nullOnDelete();
            $table->string('type')->default('payment')->after('status')->index(); // payment | advance
        });

        Schema::create('supplier_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_payment_id')->constrained()->cascadeOnDelete();
            $table->string('method');
            $table->decimal('amount', 14, 2)->default(0);
            $table->string('reference')->nullable();
            $table->timestamps();

            $table->index(['supplier_payment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_payment_methods');

        Schema::table('supplier_payments', function (Blueprint $table) {
            $table->dropForeign(['purchase_order_id']);
            $table->dropColumn(['purchase_order_id', 'type']);
        });

        Schema::table('purchase_invoices', function (Blueprint $table) {
            $table->dropColumn(['advance_applied']);
        });
    }
};
