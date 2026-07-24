<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── orders ───────────────────────────────────────────
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('user_id', 'customer_id');
            $table->renameColumn('guest_email', 'customer_phone');
            $table->renameColumn('guest_name', 'customer_name');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->change();
            $table->string('customer_phone')->nullable()->change();
            $table->string('customer_name')->nullable()->change();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreign('customer_id')->references('id')->on('customers')->nullOnDelete();
        });

        // ─── customer_addresses ───────────────────────────────
        Schema::table('customer_addresses', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('customer_addresses', function (Blueprint $table) {
            $table->renameColumn('user_id', 'customer_id');
        });

        Schema::table('customer_addresses', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->change();
        });

        Schema::table('customer_addresses', function (Blueprint $table) {
            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        // ─── orders ───────────────────────────────────────────
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('customer_id', 'user_id');
            $table->renameColumn('customer_phone', 'guest_email');
            $table->renameColumn('customer_name', 'guest_name');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
            $table->string('guest_email')->nullable()->change();
            $table->string('guest_name')->nullable()->change();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });

        // ─── customer_addresses ───────────────────────────────
        Schema::table('customer_addresses', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
        });

        Schema::table('customer_addresses', function (Blueprint $table) {
            $table->renameColumn('customer_id', 'user_id');
        });

        Schema::table('customer_addresses', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
        });

        Schema::table('customer_addresses', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
