@extends('layouts.tenant')

@section('title', __('warehouses.list_title').' - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">@lang('warehouses.list_title')</h1>
                    <p class="text-gray-600">@lang('warehouses.list_subtitle')</p>
                </div>
                <a href="{{ route('inventory.warehouses.create') }}" class="bg-purple-600 text-white px-6 py-3 rounded-xl font-medium hover:bg-purple-700 transition shadow-sm">
                    @lang('warehouses.add_new')
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl">{{ session('success') }}</div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            @if($warehouses->count() > 0)
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">@lang('warehouses.name')</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">@lang('warehouses.location')</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">@lang('warehouses.products_count')</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">@lang('common.actions')</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($warehouses as $warehouse)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $warehouse->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $warehouse->address ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $warehouse->products_count ?? 0 }}</td>
                                <td class="px-6 py-4 text-right text-sm">
                                    <div class="flex items-center justify-end space-x-2">
                                        <a href="{{ route('inventory.warehouses.edit', $warehouse) }}" class="text-blue-600 hover:text-blue-800 p-1">@lang('common.edit')</a>
                                        <form action="{{ route('inventory.warehouses.destroy', $warehouse) }}" method="POST" onsubmit="return confirm('{{ __('warehouses.delete_confirm') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800 p-1">@lang('common.delete')</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="px-6 py-4 border-t">
                    {{ $warehouses->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-1">@lang('warehouses.no_warehouses')</h3>
                    <p class="text-gray-500 mb-4">@lang('warehouses.no_warehouses_desc')</p>
                    <a href="{{ route('inventory.warehouses.create') }}" class="inline-flex items-center px-6 py-3 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition">
                        @lang('warehouses.add_first')
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
