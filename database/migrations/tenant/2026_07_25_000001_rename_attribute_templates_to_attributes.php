<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('attribute_templates', 'attributes');

        DB::statement("ALTER TABLE attributes CHANGE COLUMN type data_type ENUM('text','textarea','number','select','multiselect','boolean','date') NOT NULL DEFAULT 'text'");

        DB::statement('ALTER TABLE attributes CHANGE COLUMN is_variant_option is_variant TINYINT(1) NOT NULL DEFAULT false');

        Schema::table('attributes', function (Blueprint $table) {
            $table->boolean('is_filterable')->default(false)->after('is_variant');
            $table->dropColumn(['options', 'is_color']);
        });

        Schema::table('attributes', function (Blueprint $table) {
            $table->index('is_filterable');
            $table->index('is_variant');
            $table->index('data_type');
        });
    }

    public function down(): void
    {
        Schema::table('attributes', function (Blueprint $table) {
            $table->dropIndex(['is_filterable']);
            $table->dropIndex(['is_variant']);
            $table->dropIndex(['data_type']);
        });

        Schema::table('attributes', function (Blueprint $table) {
            $table->json('options')->nullable()->after('is_required');
            $table->boolean('is_color')->default(false)->after('is_active');
        });

        DB::statement("ALTER TABLE attributes CHANGE COLUMN data_type type ENUM('text','textarea','number','select','boolean','date') NOT NULL DEFAULT 'text'");

        DB::statement('ALTER TABLE attributes CHANGE COLUMN is_variant is_variant_option TINYINT(1) NOT NULL DEFAULT false');

        $table->dropColumn('is_filterable');

        Schema::rename('attributes', 'attribute_templates');
    }
};
