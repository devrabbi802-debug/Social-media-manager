<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove exact duplicates first (same facebook_mid + type) keeping earliest row,
        // so the unique index can be created.
        DB::statement('DELETE m1 FROM messages m1
            INNER JOIN messages m2
                ON m1.facebook_mid = m2.facebook_mid
                AND m1.type = m2.type
                AND m1.id > m2.id
            WHERE m1.facebook_mid IS NOT NULL');

        Schema::table('messages', function (Blueprint $table) {
            $table->unique(['facebook_mid', 'type'], 'messages_facebook_mid_type_unique');
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropUnique('messages_facebook_mid_type_unique');
        });
    }
};