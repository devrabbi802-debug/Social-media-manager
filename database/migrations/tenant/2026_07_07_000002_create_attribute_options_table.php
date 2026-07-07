<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attribute_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_template_id')->constrained()->cascadeOnDelete();
            $table->string('value');
            $table->string('slug');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['attribute_template_id', 'slug']);
            $table->index('attribute_template_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attribute_options');
    }
};
