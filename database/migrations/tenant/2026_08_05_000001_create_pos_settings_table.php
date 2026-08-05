<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_settings', function (Blueprint $table) {
            $table->id();
            $table->string('store_name')->nullable();
            $table->string('store_phone')->nullable();
            $table->string('store_email')->nullable();
            $table->text('store_address')->nullable();
            $table->text('receipt_footer')->nullable();
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->string('tax_type')->default('inclusive');
            $table->string('currency')->default('BDT');
            $table->string('currency_symbol')->default('৳');
            $table->json('payment_methods')->nullable();
            $table->string('default_payment_method')->default('cash');
            $table->foreignId('default_warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->boolean('auto_print_receipt')->default(false);
            $table->string('receipt_size')->default('80mm');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_settings');
    }
};
