@php
    $current = $current ?? 'hub';
    $tabs = [
        ['key' => 'hub', 'label' => __('sidebar.reports'), 'route' => 'reports'],
        ['key' => 'sales', 'label' => __('sidebar.sales_reports'), 'route' => 'reports.sales'],
        ['key' => 'inventory', 'label' => __('sidebar.inventory_reports'), 'route' => 'reports.inventory'],
        ['key' => 'accounting', 'label' => __('sidebar.accounting_reports'), 'route' => 'reports.accounting'],
        ['key' => 'pos', 'label' => __('sidebar.pos_reports'), 'route' => 'pos.reports'],
        ['key' => 'purchase', 'label' => __('sidebar.purchase_reports'), 'route' => 'purchase.reports'],
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
