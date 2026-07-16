<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE attribute_templates MODIFY COLUMN type ENUM('text','textarea','number','select','boolean','date') DEFAULT 'text'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE attribute_templates MODIFY COLUMN type ENUM('text','number','select','boolean','date') DEFAULT 'text'");
    }
};
