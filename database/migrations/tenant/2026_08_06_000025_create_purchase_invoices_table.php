<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('purchase_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('purchase_receipt_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('supplier_id')->constrained()->restrictOnDelete();
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->string('status')->default('awaiting_payment'); // draft | awaiting_payment | partially_paid | paid | cancelled
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('discount_amount', 14, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->decimal('paid_amount', 14, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['supplier_id']);
            $table->index(['status']);
            $table->index(['invoice_date']);
            $table->index(['due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_invoices');
    }
};
