@extends('layouts.tenant')

@section('title', __('categories.create_title').' - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">@lang('categories.create_title')</h1>
                    <p class="text-gray-600">@lang('categories.create_subtitle')</p>
                </div>
                <a href="{{ route('inventory.categories.index') }}" class="text-gray-600 hover:text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <form action="{{ route('inventory.categories.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="bg-white rounded-2xl p-6 shadow-sm space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">@lang('categories.name') *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">@lang('categories.parent')</label>
                    <select name="parent_id" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="">@lang('categories.none')</option>
                        @foreach($parentCategories as $category)
                            <option value="{{ $category->id }}" {{ old('parent_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">@lang('categories.description')</label>
                    <textarea name="description" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent">{{ old('description') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">@lang('categories.image')</label>
                    <input type="file" name="image" accept="image/*"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
                    @error('image') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                               class="w-5 h-5 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                        <span class="text-sm font-medium text-gray-700">@lang('categories.is_active')</span>
                    </label>
                </div>
            </div>
            <div class="mt-6 flex space-x-3">
                <button type="submit" class="bg-purple-600 text-white px-6 py-3 rounded-xl font-medium hover:bg-purple-700 transition">@lang('categories.create_btn')</button>
                <a href="{{ route('inventory.categories.index') }}" class="bg-gray-100 text-gray-700 px-6 py-3 rounded-xl font-medium hover:bg-gray-200 transition">@lang('common.cancel')</a>
            </div>
        </form>
    </div>
</div>
@endsection
