<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add embedding column to store CLIP vector embeddings for image matching.
     * The embedding is a JSON array of 512 floating point numbers.
     */
    public function up(): void
    {
        Schema::connection('tenant')->table('product_images', function (Blueprint $table) {
            $table->json('embedding')->nullable()->after('image_analysis');
        });

        Schema::connection('tenant')->table('variant_images', function (Blueprint $table) {
            $table->json('embedding')->nullable()->after('image_analysis');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('tenant')->table('product_images', function (Blueprint $table) {
            $table->dropColumn('embedding');
        });

        Schema::connection('tenant')->table('variant_images', function (Blueprint $table) {
            $table->dropColumn('embedding');
        });
    }
};
