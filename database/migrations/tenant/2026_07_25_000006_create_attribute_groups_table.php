<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attribute_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('attributes', function (Blueprint $table) {
            $table->foreignId('attribute_group_id')->nullable()->after('is_filterable')
                ->constrained('attribute_groups')->nullOnDelete();
        });

        Schema::create('attribute_group_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['attribute_group_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attribute_group_product');

        Schema::table('attributes', function (Blueprint $table) {
            $table->dropForeign(['attribute_group_id']);
            $table->dropColumn('attribute_group_id');
        });

        Schema::dropIfExists('attribute_groups');
    }
};
