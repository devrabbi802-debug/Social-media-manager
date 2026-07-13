@extends('layouts.tenant')

@section('title', __('inventory.alert_title').' - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">@lang('inventory.alert_title')</h1>
                    <p class="text-gray-600">@lang('inventory.alert_subtitle')</p>
                </div>
                <a href="{{ route('inventory.index') }}" class="text-gray-600 hover:text-purple-600 font-medium">← @lang('common.back')</a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @include('tenant.partials._nav-tabs', ['activePage' => 'inventory'])

        {{-- Inventory Sub-Navigation --}}
        <div class="mb-6 flex flex-wrap gap-2">
            <a href="{{ route('inventory.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white text-gray-700 border hover:bg-purple-50 hover:text-purple-600 transition">@lang('common.summary')</a>
            <a href="{{ route('inventory.products.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white text-gray-700 border hover:bg-purple-50 hover:text-purple-600 transition">@lang('products.list_title')</a>
            <a href="{{ route('inventory.categories.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white text-gray-700 border hover:bg-purple-50 hover:text-purple-600 transition">@lang('categories.list_title')</a>
            <a href="{{ route('inventory.brands.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white text-gray-700 border hover:bg-purple-50 hover:text-purple-600 transition">@lang('brands.list_title')</a>
            <a href="{{ route('inventory.attributes.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white text-gray-700 border hover:bg-purple-50 hover:text-purple-600 transition">@lang('attributes.list_title')</a>
            <a href="{{ route('inventory.warehouses.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white text-gray-700 border hover:bg-purple-50 hover:text-purple-600 transition">@lang('warehouses.list_title')</a>
            <a href="{{ route('inventory.movements') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white text-gray-700 border hover:bg-purple-50 hover:text-purple-600 transition">@lang('inventory.stock_movement')</a>
            <a href="{{ route('inventory.alerts') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-purple-600 text-white">@lang('inventory.alert_title')</a>
        </div>

        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 text-left text-sm text-gray-500">
                        <th class="px-6 py-4 font-medium">@lang('products.product')</th>
                        <th class="px-6 py-4 font-medium">@lang('categories.name')</th>
                        <th class="px-6 py-4 font-medium">@lang('inventory.current_stock')</th>
                        <th class="px-6 py-4 font-medium">@lang('inventory.threshold')</th>
                        <th class="px-6 py-4 font-medium">@lang('products.status')</th>
                        <th class="px-6 py-4 font-medium text-right">@lang('common.actions')</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($alerts as $alert)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $alert->product->name ?? __('common.deleted') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $alert->product->category->name ?? '-' }}</td>
                        <td class="px-6 py-4 font-semibold {{ ($alert->product->stock_quantity ?? 0) <= $alert->threshold ? 'text-red-600' : 'text-green-600' }}">
                            {{ $alert->product->stock_quantity ?? 0 }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $alert->threshold }}</td>
                        <td class="px-6 py-4">
                            @if($alert->isLowStock())
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">@lang('inventory.low_stock')!</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">OK</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <form action="{{ route('inventory.alerts.destroy', $alert) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('common.confirm') }}')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-gray-400 hover:text-red-600 text-sm">@lang('common.delete')</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-6 py-12 text-center text-gray-500">@lang('inventory.no_alerts')</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($alerts->hasPages())<div class="px-6 py-4 border-t">{{ $alerts->withQueryString()->links() }}</div>@endif
        </div>
    </div>
</div>
@endsection
