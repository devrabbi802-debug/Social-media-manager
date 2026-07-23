@extends('layouts.tenant')

@section('title', __('categories.list_title').' - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">@lang('categories.list_title')</h1>
                    <p class="text-gray-600">@lang('categories.list_subtitle')</p>
                </div>
                <a href="{{ route('inventory.categories.create') }}" class="bg-purple-600 text-white px-6 py-3 rounded-xl font-medium hover:bg-purple-700 transition shadow-sm">
                    @lang('categories.add_new')
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl">{{ session('success') }}</div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="p-4 border-b bg-gray-50">
                <form method="GET" class="flex gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Main Category</label>
                        <select name="parent_id" class="rounded-xl border-gray-300 shadow-sm focus:ring-purple-500 focus:border-purple-500 px-4 py-2 border" onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            <option value="root" {{ request('parent_id') === 'root' ? 'selected' : '' }}>Root Categories</option>
                            @foreach($rootCategories as $root)
                                <option value="{{ $root->id }}" {{ request('parent_id') == $root->id ? 'selected' : '' }}>{{ $root->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search categories..." class="rounded-xl border-gray-300 shadow-sm focus:ring-purple-500 focus:border-purple-500 px-4 py-2 border">
                    </div>
                    <button type="submit" class="bg-purple-600 text-white px-4 py-2 rounded-xl hover:bg-purple-700 transition">Filter</button>
                    @if(request()->anyFilled(['search', 'parent_id']))
                        <a href="{{ route('inventory.categories.index') }}" class="text-gray-600 px-4 py-2 hover:text-gray-800">Clear</a>
                    @endif
                </form>
            </div>
            @if($categories->count() > 0)
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">@lang('categories.name')</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">@lang('categories.parent')</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">@lang('categories.products_count')</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">@lang('categories.is_active')</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">@lang('common.actions')</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($categories as $category)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $category->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $category->parent->name ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $category->products_count ?? 0 }}</td>
                                <td class="px-6 py-4 text-sm">
                                    @if($category->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">@lang('common.active')</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">@lang('common.inactive')</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right text-sm">
                                    <div class="flex items-center justify-end space-x-2">
                                        <a href="{{ route('inventory.categories.edit', $category) }}" class="text-blue-600 hover:text-blue-800 p-1">@lang('common.edit')</a>
                                        <form action="{{ route('inventory.categories.destroy', $category) }}" method="POST" onsubmit="return confirm('{{ __('categories.delete_confirm') }}')">
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
                    {{ $categories->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-1">@lang('categories.no_categories')</h3>
                    <p class="text-gray-500 mb-4">@lang('categories.no_categories_desc')</p>
                    <a href="{{ route('inventory.categories.create') }}" class="inline-flex items-center px-6 py-3 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition">
                        @lang('categories.add_first')
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
