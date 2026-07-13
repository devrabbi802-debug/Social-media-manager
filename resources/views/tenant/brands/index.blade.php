@extends('layouts.tenant')

@section('title', __('brands.list_title').' - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">@lang('brands.list_title')</h1>
                    <p class="text-gray-600">@lang('brands.list_subtitle')</p>
                </div>
                <a href="{{ route('inventory.brands.create') }}" class="bg-purple-600 text-white px-6 py-3 rounded-xl font-medium hover:bg-purple-700 transition shadow-sm">
                    @lang('brands.add_new')
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl">{{ session('success') }}</div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            @if($brands->count() > 0)
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">@lang('brands.name')</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">@lang('brands.products_count')</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">@lang('common.actions')</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($brands as $brand)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $brand->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $brand->products_count ?? 0 }}</td>
                                <td class="px-6 py-4 text-right text-sm">
                                    <div class="flex items-center justify-end space-x-2">
                                        <a href="{{ route('inventory.brands.edit', $brand) }}" class="text-blue-600 hover:text-blue-800 p-1">@lang('common.edit')</a>
                                        <form action="{{ route('inventory.brands.destroy', $brand) }}" method="POST" onsubmit="return confirm('{{ __('brands.delete_confirm') }}')">
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
                    {{ $brands->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0,1.024.195,1.414.586l7 7a2,2 0 010,2.828l-7 7a2,2 0 01-2.828 0l-7-7A2,2 0 013,12V7a4,4 0 014-4z"/>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-1">@lang('brands.no_brands')</h3>
                    <p class="text-gray-500 mb-4">@lang('brands.no_brands_desc')</p>
                    <a href="{{ route('inventory.brands.create') }}" class="inline-flex items-center px-6 py-3 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition">
                        @lang('brands.add_first')
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
