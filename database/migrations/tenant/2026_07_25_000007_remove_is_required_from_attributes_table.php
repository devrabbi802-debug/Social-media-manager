<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $attrs = DB::table('attributes')
            ->where('is_required', true)
            ->whereNotNull('category_id')
            ->get();

        foreach ($attrs as $attr) {
            DB::table('category_attributes')->updateOrInsert(
                ['category_id' => $attr->category_id, 'attribute_id' => $attr->id],
                ['required' => true],
            );
        }

        Schema::table('attributes', function (Blueprint $table) {
            $table->dropColumn('is_required');
        });
    }

    public function down(): void
    {
        Schema::table('attributes', function (Blueprint $table) {
            $table->boolean('is_required')->default(false)->after('data_type');
        });
    }
};
