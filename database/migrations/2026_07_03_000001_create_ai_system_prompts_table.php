<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_system_prompts', function (Blueprint $table) {
            $table->id();
            $table->longText('prompt_text');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_system_prompts');
    }
};
