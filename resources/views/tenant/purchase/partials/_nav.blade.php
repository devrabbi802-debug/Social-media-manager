@php
    $current = $current ?? 'dashboard';
    $tabs = [
        ['key' => 'dashboard', 'label' => __('sidebar.purchase_dashboard'), 'route' => 'purchase.index'],
        ['key' => 'direct', 'label' => __('sidebar.purchase_direct'), 'route' => 'purchase.direct.create'],
        ['key' => 'suppliers', 'label' => __('sidebar.suppliers'), 'route' => 'purchase.suppliers.index'],
        ['key' => 'orders', 'label' => __('sidebar.purchase_orders'), 'route' => 'purchase.orders.index'],
        ['key' => 'receipts', 'label' => __('sidebar.purchase_receipts'), 'route' => 'purchase.receipts.index'],
        ['key' => 'invoices', 'label' => __('sidebar.purchase_invoices'), 'route' => 'purchase.invoices.index'],
        ['key' => 'payments', 'label' => __('sidebar.supplier_payments'), 'route' => 'purchase.payments.index'],
        ['key' => 'returns', 'label' => __('sidebar.purchase_returns'), 'route' => 'purchase.returns.index'],
        ['key' => 'reports', 'label' => __('sidebar.purchase_reports'), 'route' => 'purchase.reports'],
        ['key' => 'settings', 'label' => __('sidebar.purchase_settings'), 'route' => 'purchase.settings'],
    ];
@endphp

<div class="mb-6 overflow-x-auto">
    <div class="flex gap-2 min-w-max">
        @foreach($tabs as $tab)
            <a href="{{ route($tab['route']) }}"
               class="px-4 py-2 rounded-xl text-sm font-medium transition
                {{ $current === $tab['key'] ? 'bg-purple-600 text-white shadow-sm' : 'bg-white text-gray-700 border hover:bg-purple-50 hover:text-purple-600' }}">
                {{ $tab['label'] }}
            </a>
        @endforeach
    </div>
</div>
