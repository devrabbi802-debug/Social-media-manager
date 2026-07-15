<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE messages MODIFY COLUMN type ENUM('text', 'image', 'ai_reply', 'audio') NOT NULL DEFAULT 'text'");

        Schema::table('messages', function (Blueprint $table) {
            $table->text('audio_path')->nullable()->after('image_path');
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn('audio_path');
        });

        DB::statement("ALTER TABLE messages MODIFY COLUMN type ENUM('text', 'image', 'ai_reply') NOT NULL DEFAULT 'text'");
    }
};
