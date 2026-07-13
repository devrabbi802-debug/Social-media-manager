@extends('layouts.tenant')

@section('title', __('inventory.transfers').' - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">@lang('inventory.transfers')</h1>
                    <p class="text-gray-600">@lang('inventory.transfer_subtitle')</p>
                </div>
                <a href="{{ route('inventory.index') }}" class="text-gray-600 hover:text-purple-600 font-medium">← @lang('common.back')</a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Sub-Navigation --}}
        <div class="mb-6 flex flex-wrap gap-2">
            <a href="{{ route('inventory.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white text-gray-700 border hover:bg-purple-50 hover:text-purple-600 transition">@lang('common.summary')</a>
            <a href="{{ route('inventory.products.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white text-gray-700 border hover:bg-purple-50 hover:text-purple-600 transition">@lang('products.list_title')</a>
            <a href="{{ route('inventory.transfers.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-purple-600 text-white">@lang('inventory.transfers')</a>
            <a href="{{ route('inventory.movements') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white text-gray-700 border hover:bg-purple-50 hover:text-purple-600 transition">@lang('inventory.stock_movement')</a>
        </div>

        @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-2xl p-4 mb-6 flex items-center">
            <svg class="w-6 h-6 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <p class="text-green-800 font-medium">{{ session('success') }}</p>
        </div>
        @endif

        @if(session('error'))
        <div class="bg-red-50 border border-red-200 rounded-2xl p-4 mb-6 flex items-center">
            <svg class="w-6 h-6 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            <p class="text-red-800 font-medium">{{ session('error') }}</p>
        </div>
        @endif

        {{-- Transfer Form --}}
        <div class="bg-white rounded-2xl p-6 shadow-sm mb-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">@lang('inventory.new_transfer')</h2>
            <form action="{{ route('inventory.transfers.store') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">@lang('products.product') *</label>
                        <select name="product_id" required class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                            <option value="">@lang('common.select')</option>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->sku }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">@lang('inventory.from_warehouse') *</label>
                        <select name="from_warehouse_id" required class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                            <option value="">@lang('common.select')</option>
                            @foreach($warehouses as $w)
                                <option value="{{ $w->id }}">{{ $w->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">@lang('inventory.to_warehouse') *</label>
                        <select name="to_warehouse_id" required class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                            <option value="">@lang('common.select')</option>
                            @foreach($warehouses as $w)
                                <option value="{{ $w->id }}">{{ $w->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">@lang('inventory.quantity') *</label>
                        <input type="number" name="quantity" min="1" required class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">@lang('common.notes')</label>
                        <input type="text" name="notes" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition">@lang('inventory.request_transfer')</button>
                </div>
            </form>
        </div>

        {{-- Transfers List --}}
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b">
                <h2 class="text-lg font-bold text-gray-900">@lang('inventory.transfer_history')</h2>
            </div>
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 text-left text-sm text-gray-500">
                        <th class="px-6 py-4 font-medium">@lang('products.product')</th>
                        <th class="px-6 py-4 font-medium">@lang('common.from')</th>
                        <th class="px-6 py-4 font-medium">@lang('common.to')</th>
                        <th class="px-6 py-4 font-medium">@lang('inventory.quantity')</th>
                        <th class="px-6 py-4 font-medium">@lang('products.status')</th>
                        <th class="px-6 py-4 font-medium">@lang('common.date')</th>
                        <th class="px-6 py-4 font-medium text-right">@lang('common.actions')</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($transfers as $transfer)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <span class="font-medium text-gray-900">{{ $transfer->product->name }}</span>
                            @if($transfer->variant)
                                <p class="text-xs text-gray-500">{{ $transfer->variant->sku }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $transfer->fromWarehouse->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $transfer->toWarehouse->name }}</td>
                        <td class="px-6 py-4 font-semibold text-gray-900">{{ $transfer->quantity }}</td>
                        <td class="px-6 py-4">
                            @if($transfer->status === 'pending')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">@lang('common.pending')</span>
                            @elseif($transfer->status === 'completed')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">@lang('common.completed')</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">@lang('common.cancelled')</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $transfer->created_at->format('d M Y H:i') }}</td>
                        <td class="px-6 py-4 text-right">
                            @if($transfer->status === 'pending')
                                <div class="flex items-center justify-end space-x-2">
                                    <form action="{{ route('inventory.transfers.complete', $transfer) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-green-600 hover:text-green-800 text-xs font-medium">@lang('common.complete')</button>
                                    </form>
                                    <form action="{{ route('inventory.transfers.cancel', $transfer) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-orange-600 hover:text-orange-800 text-xs font-medium">@lang('common.cancel_btn')</button>
                                    </form>
                                    <form action="{{ route('inventory.transfers.destroy', $transfer) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('common.delete_confirm') }}')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium">@lang('common.delete')</button>
                                    </form>
                                </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-6 py-12 text-center text-gray-500">@lang('common.no_data')</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($transfers->hasPages())<div class="px-6 py-4 border-t">{{ $transfers->withQueryString()->links() }}</div>@endif
        </div>
    </div>
</div>
@endsection
