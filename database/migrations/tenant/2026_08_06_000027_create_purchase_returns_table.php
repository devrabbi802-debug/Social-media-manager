<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_number')->unique();
            $table->foreignId('purchase_receipt_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('purchase_invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('supplier_id')->constrained()->restrictOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained()->nullOnDelete();
            $table->date('return_date');
            $table->string('status')->default('pending'); // pending | completed | cancelled
            $table->decimal('total', 14, 2)->default(0);
            $table->text('reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['supplier_id']);
            $table->index(['status']);
            $table->index(['return_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_returns');
    }
};
