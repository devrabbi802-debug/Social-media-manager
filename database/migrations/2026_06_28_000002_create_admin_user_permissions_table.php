<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_user_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained()->onDelete('cascade');
            $table->string('menu_slug');
            $table->string('permission'); // list, create, edit, delete, view
            $table->timestamps();

            $table->unique(['admin_id', 'menu_slug', 'permission']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_user_permissions');
    }
};
