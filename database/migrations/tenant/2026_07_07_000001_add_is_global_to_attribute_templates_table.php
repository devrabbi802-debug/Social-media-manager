<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Drop foreign key first
        Schema::table('attribute_templates', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
        });

        // 2. Drop old unique index
        Schema::table('attribute_templates', function (Blueprint $table) {
            $table->dropUnique('attribute_templates_category_id_slug_unique');
        });

        // 3. Add is_global column
        Schema::table('attribute_templates', function (Blueprint $table) {
            $table->boolean('is_global')->default(false)->after('category_id');
        });

        // 4. Make category_id nullable (for global attributes)
        Schema::table('attribute_templates', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable()->change();
        });

        // 5. Recreate unique constraint (nullable category_id works with MySQL)
        Schema::table('attribute_templates', function (Blueprint $table) {
            $table->unique(['category_id', 'slug', 'is_global']);
        });

        // 6. Recreate foreign key
        Schema::table('attribute_templates', function (Blueprint $table) {
            $table->foreign('category_id')->references('id')->on('categories')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('attribute_templates', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropUnique('attribute_templates_category_id_slug_is_global_unique');
            $table->dropColumn('is_global');
            $table->unsignedBigInteger('category_id')->nullable(false)->change();
            $table->unique(['category_id', 'slug']);
            $table->foreign('category_id')->references('id')->on('categories')->cascadeOnDelete();
        });
    }
};
