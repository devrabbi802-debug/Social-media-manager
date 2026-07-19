<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_setups', function (Blueprint $table) {
            $table->id();
            $table->string('business_name')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('support_number')->nullable();
            $table->string('support_email')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_setups');
    }
};
