<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('company')->nullable();
            $table->string('tax_number')->nullable();
            $table->string('address')->nullable();
            $table->string('payment_terms')->nullable();
            $table->integer('payment_term_days')->default(0);
            $table->decimal('opening_balance', 14, 2)->default(0);
            $table->text('notes')->nullable();
            $table->string('status')->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
