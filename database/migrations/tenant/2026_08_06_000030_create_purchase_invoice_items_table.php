<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('purchase_order_item_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('purchase_receipt_item_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->string('name');
            $table->string('sku')->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_cost', 14, 2)->default(0);
            $table->decimal('discount', 14, 2)->default(0);
            $table->decimal('line_total', 14, 2)->default(0);
            $table->timestamps();

            $table->index(['purchase_invoice_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_invoice_items');
    }
};
