<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attribute_templates', function (Blueprint $table) {
            $table->boolean('is_color')->default(false)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('attribute_templates', function (Blueprint $table) {
            $table->dropColumn('is_color');
        });
    }
};
