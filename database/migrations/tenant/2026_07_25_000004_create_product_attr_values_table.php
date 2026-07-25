<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_attr_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->cascadeOnDelete();
            $table->foreignId('attribute_id')->constrained('attributes')->cascadeOnDelete();
            $table->foreignId('value_id')->nullable()->constrained('attribute_values')->nullOnDelete();
            $table->string('value', 500)->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'variant_id', 'attribute_id', 'value_id'], 'prod_attr_unique');
            $table->index('attribute_id');
            $table->index('value_id');
            $table->index('variant_id');
        });

        // Migrate data from product_attribute_values
        DB::statement('
            INSERT INTO product_attr_values (product_id, variant_id, attribute_id, value_id, value, created_at, updated_at)
            SELECT pav.product_id, NULL, pav.attribute_template_id, NULL, pav.value, NOW(), NOW()
            FROM product_attribute_values pav
        ');

        // Migrate data from variant_attribute_values
        DB::statement('
            INSERT INTO product_attr_values (product_id, variant_id, attribute_id, value_id, value, created_at, updated_at)
            SELECT pv.product_id, vav.variant_id, vav.attribute_template_id, NULL, vav.value, NOW(), NOW()
            FROM variant_attribute_values vav
            JOIN product_variants pv ON pv.id = vav.variant_id
        ');

        Schema::dropIfExists('variant_attribute_values');
        Schema::dropIfExists('product_attribute_values');
    }

    public function down(): void
    {
        Schema::create('product_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attribute_template_id')->constrained('attributes')->cascadeOnDelete();
            $table->text('value');
            $table->timestamps();
            $table->unique(['product_id', 'attribute_template_id'], 'pav_unique');
        });

        Schema::create('variant_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->foreignId('attribute_template_id')->constrained('attributes')->cascadeOnDelete();
            $table->string('value');
            $table->timestamps();
            $table->unique(['variant_id', 'attribute_template_id'], 'vav_unique');
        });

        // Restore data
        DB::statement('
            INSERT INTO product_attribute_values (product_id, attribute_template_id, value, created_at, updated_at)
            SELECT DISTINCT pav.product_id, pav.attribute_id, pav.value, NOW(), NOW()
            FROM product_attr_values pav
            WHERE pav.variant_id IS NULL
        ');

        DB::statement('
            INSERT INTO variant_attribute_values (variant_id, attribute_template_id, value, created_at, updated_at)
            SELECT DISTINCT pav.variant_id, pav.attribute_id, pav.value, NOW(), NOW()
            FROM product_attr_values pav
            WHERE pav.variant_id IS NOT NULL
        ');

        Schema::dropIfExists('product_attr_values');
    }
};
