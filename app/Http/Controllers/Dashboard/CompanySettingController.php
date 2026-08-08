<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanySettingController extends Controller
{
    public function index()
    {
        $settings = CompanySetting::current();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $paymentAccounts = $this->getPaymentAccounts();

        return view('tenant.company.settings', compact('settings', 'warehouses', 'paymentAccounts'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'business_name' => 'nullable|string|max:255',
            'store_name' => 'nullable|string|max:255',
            'store_phone' => 'nullable|string|max:50',
            'store_email' => 'nullable|email|max:255',
            'store_address' => 'nullable|string|max:500',
            'default_warehouse_id' => 'nullable|exists:warehouses,id',
            'currency' => 'nullable|string|max:10',
            'currency_symbol' => 'nullable|string|max:10',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'tax_type' => 'nullable|in:inclusive,exclusive',
            'payment_methods' => 'nullable|array',
            'default_payment_method' => 'nullable|string|max:20',
        ]);

        $settings = CompanySetting::current();
        $settings->update($validated);

        return back()->with('success', __('common.updated'));
    }

    /**
     * Query Chart of Accounts from the first available tenant DB
     * for payment-flagged accounts (is_pos_payment).
     */
    protected function getPaymentAccounts(): array
    {
        $tenantDb = $this->findFirstTenantDb();
        if (! $tenantDb) {
            return [];
        }

        try {
            config(['database.connections.tenant_company' => [
                'driver' => 'mysql',
                'host' => config('database.connections.mysql.host'),
                'port' => config('database.connections.mysql.port', 3306),
                'database' => $tenantDb,
                'username' => config('database.connections.mysql.username'),
                'password' => config('database.connections.mysql.password'),
            ]]);

            $accounts = DB::connection('tenant_company')
                ->select('SELECT id, code, name FROM chart_of_accounts WHERE is_pos_payment = 1 ORDER BY code');

            DB::purge('tenant_company');

            return collect($accounts)->map(fn ($a) => [
                'id' => $a->id,
                'code' => $a->code,
                'name' => $a->name,
            ])->toArray();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Find the first tenant database name (e.g. "noyan_socialboost").
     */
    protected function findFirstTenantDb(): ?string
    {
        $suffix = config('tenancy.database.suffix', '_socialboost');
        $dbs = DB::select("SHOW DATABASES LIKE '%{$suffix}'");
        foreach ($dbs as $row) {
            $name = reset($row);
            if ($name && str_ends_with($name, $suffix)) {
                return $name;
            }
        }

        return null;
    }
}
