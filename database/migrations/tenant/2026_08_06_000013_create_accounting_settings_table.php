<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_settings', function (Blueprint $table) {
            $table->id();
            $table->string('currency_symbol', 10)->default('৳');
            $table->string('currency', 10)->default('BDT');
            $table->unsignedTinyInteger('fiscal_year_start_month')->default(7);
            $table->boolean('post_pos_sales')->default(true);
            $table->boolean('post_pos_refunds')->default(true);
            $table->boolean('post_storefront_orders')->default(true);
            $table->foreignId('default_cash_account_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $table->foreignId('default_bank_account_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $table->foreignId('default_receivable_account_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $table->foreignId('default_inventory_account_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $table->foreignId('default_cogs_account_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $table->foreignId('default_sales_account_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $table->foreignId('default_discount_account_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $table->foreignId('default_tax_payable_account_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $table->foreignId('default_expense_account_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $table->json('payment_account_map')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_settings');
    }
};
