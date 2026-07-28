<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->json('text_embedding')->nullable()->after('status');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->json('text_embedding')->nullable()->after('price');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('text_embedding');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn('text_embedding');
        });
    }
};
