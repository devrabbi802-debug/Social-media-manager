<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE messages MODIFY COLUMN audio_path TEXT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE messages MODIFY COLUMN audio_path VARCHAR(255) NULL");
    }
};
