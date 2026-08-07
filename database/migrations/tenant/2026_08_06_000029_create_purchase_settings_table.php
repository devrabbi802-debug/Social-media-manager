<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('default_warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->foreignId('purchase_account_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $table->integer('payment_term_days')->default(30);
            $table->decimal('default_tax_rate', 5, 2)->default(0);
            $table->boolean('auto_create_invoice_on_receipt')->default(true);
            $table->boolean('auto_post_purchases')->default(false);
            $table->boolean('update_cost_price_on_receipt')->default(true);
            $table->string('po_prefix')->default('PO');
            $table->string('grn_prefix')->default('GRN');
            $table->string('inv_prefix')->default('INV');
            $table->string('pay_prefix')->default('PAY');
            $table->string('rtn_prefix')->default('RTN');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_settings');
    }
};
