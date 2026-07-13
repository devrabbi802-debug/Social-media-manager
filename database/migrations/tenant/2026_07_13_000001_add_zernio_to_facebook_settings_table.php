<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facebook_settings', function (Blueprint $table) {
            $table->string('connection_type')->default('facebook_app')->after('user_id');
            $table->text('zernio_api_key')->nullable()->after('connection_type');
            $table->string('zernio_account_id')->nullable()->after('zernio_api_key');
            $table->string('zernio_profile_id')->nullable()->after('zernio_account_id');
            $table->string('page_name')->nullable()->after('page_id');
        });
    }

    public function down(): void
    {
        Schema::table('facebook_settings', function (Blueprint $table) {
            $table->dropColumn([
                'connection_type',
                'zernio_api_key',
                'zernio_account_id',
                'zernio_profile_id',
                'page_name',
            ]);
        });
    }
};
