<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attribute_templates', function (Blueprint $table) {
            $table->string('placeholder')->nullable()->after('is_required');
            $table->string('default')->nullable()->after('placeholder');
        });
    }

    public function down(): void
    {
        Schema::table('attribute_templates', function (Blueprint $table) {
            $table->dropColumn(['placeholder', 'default']);
        });
    }
};
