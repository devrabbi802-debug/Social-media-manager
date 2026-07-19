<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('storefront_settings', function (Blueprint $table) {
            $table->id();

            // Theme Selection
            $table->string('theme_slug', 50)->default('clothing-fashion');
            $table->json('theme_overrides')->nullable();

            // Brand Identity
            $table->string('store_name')->nullable();
            $table->string('store_logo')->nullable();
            $table->string('store_favicon')->nullable();

            // Layout Options
            $table->enum('layout_style', ['grid', 'list', 'masonry'])->default('grid');
            $table->integer('products_per_row')->default(4);
            $table->integer('products_per_row_mobile')->default(2);
            $table->boolean('show_header_slider')->default(true);
            $table->boolean('show_brands_section')->default(true);
            $table->boolean('show_newsletter')->default(true);

            // Footer Content
            $table->text('footer_about_text')->nullable();
            $table->string('footer_logo')->nullable();
            $table->string('facebook_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('youtube_url')->nullable();
            $table->string('whatsapp_number', 20)->nullable();
            $table->string('footer_copyright_text')->nullable();

            // Contact Info
            $table->string('contact_phone', 20)->nullable();
            $table->string('contact_email')->nullable();
            $table->text('contact_address')->nullable();

            // Custom Code
            $table->text('custom_css')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('storefront_settings');
    }
};