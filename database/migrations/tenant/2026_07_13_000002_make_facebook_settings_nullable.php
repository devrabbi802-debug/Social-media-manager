<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facebook_settings', function (Blueprint $table) {
            $table->string('app_id')->nullable()->change();
            $table->text('app_secret')->nullable()->change();
            $table->string('verify_token')->nullable()->change();
            $table->string('page_id')->nullable()->change();
            $table->text('page_access_token')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('facebook_settings', function (Blueprint $table) {
            $table->string('app_id')->nullable(false)->change();
            $table->text('app_secret')->nullable(false)->change();
            $table->string('page_id')->nullable(false)->change();
            $table->text('page_access_token')->nullable(false)->change();
        });
    }
};
