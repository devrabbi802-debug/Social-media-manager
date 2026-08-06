<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chart_of_accounts', function (Blueprint $table) {
            $table->boolean('is_pos_payment')->default(false)->after('is_active');
        });

        $codes = ['1010', '1020', '1030'];
        DB::table('chart_of_accounts')->whereIn('code', $codes)->update(['is_pos_payment' => true]);

        $settings = DB::table('accounting_settings')->where('id', 1)->first();
        if ($settings) {
            $ids = array_filter([
                $settings->default_cash_account_id ?? null,
                $settings->default_bank_account_id ?? null,
            ]);
            if ($ids) {
                DB::table('chart_of_accounts')->whereIn('id', $ids)->update(['is_pos_payment' => true]);
            }
        }

        $legacyMap = [
            'cash' => '1010',
            'cod' => '1010',
            'card' => '1020',
            'bank' => '1020',
            'mobile' => '1030',
            'bkash' => '1030',
            'nagad' => '1030',
            'rocket' => '1030',
            'upay' => '1030',
        ];
        $posSettings = DB::table('pos_settings')->get();
        foreach ($posSettings as $row) {
            $methods = json_decode($row->payment_methods ?? 'null', true);
            if (! is_array($methods)) {
                $methods = ['cash', 'card', 'mobile'];
            }
            $mapped = array_map(fn ($m) => $legacyMap[$m] ?? $m, $methods);
            $default = $legacyMap[$row->default_payment_method ?? 'cash'] ?? $row->default_payment_method ?? '1010';
            DB::table('pos_settings')->where('id', $row->id)->update([
                'payment_methods' => json_encode(array_values(array_unique($mapped))),
                'default_payment_method' => $default,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('chart_of_accounts', function (Blueprint $table) {
            $table->dropColumn('is_pos_payment');
        });
    }
};
