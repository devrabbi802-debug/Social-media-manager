<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('business_name')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('sub_category')->nullable();
            $table->string('persona_name')->nullable();
            $table->string('business_hours')->nullable();
            $table->text('off_hours_message')->nullable();
            $table->text('business_description')->nullable();

            // Tone & Communication
            $table->string('formality_level')->default('casual');
            $table->string('emoji_usage')->default('sometimes');
            $table->string('language_style')->default('banglish');
            $table->string('greeting_style')->default('হ্যালো');

            // Pricing
            $table->boolean('price_negotiation')->default(false);
            $table->integer('negotiation_limit')->default(0);
            $table->text('bulk_discount_rule')->nullable();
            $table->text('current_promo')->nullable();

            // Delivery
            $table->text('delivery_areas')->nullable();
            $table->string('delivery_time')->nullable();
            $table->string('delivery_partner')->nullable();
            $table->boolean('cod_available')->default(true);

            // Payment
            $table->json('accepted_payment_methods')->nullable();
            $table->boolean('advance_payment_required')->default(false);
            $table->integer('advance_payment_percent')->default(0);
            $table->boolean('advance_for_outside_dhaka')->default(false);

            // Policies
            $table->text('refund_policy')->nullable();
            $table->text('exchange_policy')->nullable();

            // Order Process
            $table->text('order_process_message')->nullable();

            // Escalation
            $table->text('custom_escalation_keywords')->nullable();
            $table->string('escalation_contact')->nullable();

            // Category-specific extra data
            $table->json('extra_fields_data')->nullable();

            // FAQ
            $table->json('faq')->nullable();

            // Logo
            $table->string('logo_path')->nullable();

            $table->timestamps();

            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_settings');
    }
};
