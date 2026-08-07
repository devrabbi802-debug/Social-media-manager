<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number')->unique();
            $table->foreignId('supplier_id')->constrained()->restrictOnDelete();
            $table->foreignId('purchase_invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 14, 2)->default(0);
            $table->string('method')->default('cash');
            $table->string('reference')->nullable();
            $table->date('payment_date');
            $table->text('notes')->nullable();
            $table->string('status')->default('completed'); // completed | cancelled
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['supplier_id']);
            $table->index(['purchase_invoice_id']);
            $table->index(['payment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_payments');
    }
};
