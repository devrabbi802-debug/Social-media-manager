<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('attribute_options', 'attribute_values');

        Schema::table('attribute_values', function (Blueprint $table) {
            $table->string('swatch_hex', 7)->nullable()->after('value');
            $table->dropColumn(['slug', 'is_active']);
        });

        Schema::table('attribute_values', function (Blueprint $table) {
            $table->index('attribute_template_id');
        });

        DB::statement('ALTER TABLE attribute_values CHANGE COLUMN attribute_template_id attribute_id BIGINT UNSIGNED NOT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE attribute_values CHANGE COLUMN attribute_id attribute_template_id BIGINT UNSIGNED NOT NULL');

        Schema::table('attribute_values', function (Blueprint $table) {
            $table->dropIndex(['attribute_id']);
            $table->string('slug')->after('value');
            $table->boolean('is_active')->default(true)->after('sort_order');
            $table->dropColumn('swatch_hex');
        });

        Schema::rename('attribute_values', 'attribute_options');
    }
};
