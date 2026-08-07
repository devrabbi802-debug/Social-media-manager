<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique();
            $table->foreignId('supplier_id')->constrained()->restrictOnDelete();
            $table->date('order_date');
            $table->date('expected_date')->nullable();
            $table->string('status')->default('draft'); // draft | ordered | partially_received | received | cancelled
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->string('discount_type')->nullable(); // fixed | percent
            $table->decimal('discount_value', 14, 2)->default(0);
            $table->decimal('discount_amount', 14, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('ordered_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['supplier_id']);
            $table->index(['status']);
            $table->index(['order_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
