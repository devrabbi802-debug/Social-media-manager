<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facebook_settings', function (Blueprint $table) {
            $table->boolean('ai_auto_reply_enabled')->default(true)->after('page_access_token');
        });
    }

    public function down(): void
    {
        Schema::table('facebook_settings', function (Blueprint $table) {
            $table->dropColumn('ai_auto_reply_enabled');
        });
    }
};
