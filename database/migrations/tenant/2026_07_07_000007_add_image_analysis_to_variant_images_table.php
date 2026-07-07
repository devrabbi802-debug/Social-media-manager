<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('variant_images', function (Blueprint $table) {
            $table->json('image_analysis')->nullable()->after('sort_order');
        });
    }

    public function down(): void
    {
        Schema::table('variant_images', function (Blueprint $table) {
            $table->dropColumn('image_analysis');
        });
    }
};
