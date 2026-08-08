<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->string('business_name')->nullable();
            $table->string('store_name')->nullable();
            $table->string('store_phone')->nullable();
            $table->string('store_email')->nullable();
            $table->string('store_address')->nullable();
            $table->string('logo_path')->nullable();
            $table->foreignId('default_warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->string('currency', 10)->default('BDT');
            $table->string('currency_symbol', 10)->default('৳');
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->string('tax_type', 20)->default('inclusive');
            $table->timestamps();
        });

        // Seed from existing POS settings if available
        if (Schema::hasTable('pos_settings')) {
            $pos = DB::table('pos_settings')->where('id', 1)->first();
            if ($pos) {
                DB::table('company_settings')->insert([
                    'business_name' => $pos->store_name ?? null,
                    'store_name' => $pos->store_name ?? null,
                    'store_phone' => $pos->store_phone ?? null,
                    'store_email' => $pos->store_email ?? null,
                    'store_address' => $pos->store_address ?? null,
                    'default_warehouse_id' => $pos->default_warehouse_id ?? null,
                    'currency' => $pos->currency ?? 'BDT',
                    'currency_symbol' => $pos->currency_symbol ?? '৳',
                    'tax_rate' => $pos->tax_rate ?? 0,
                    'tax_type' => $pos->tax_type ?? 'inclusive',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};
