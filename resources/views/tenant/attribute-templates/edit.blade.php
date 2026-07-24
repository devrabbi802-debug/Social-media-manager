@extends('layouts.tenant')

@section('title', __('attributes.edit_title', ['name' => $attribute->name]).' - SocialBoost AI')

@section('content')
@php $optionValues = old('options') ? array_map('trim', explode(',', old('options'))) : (is_array($attribute->options) ? $attribute->options : []); @endphp
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">@lang('attributes.edit_title', ['name' => $attribute->name])</h1>
                </div>
                <a href="{{ route('inventory.attributes.index') }}" class="text-gray-600 hover:text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <form action="{{ route('inventory.attributes.update', $attribute) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="bg-white rounded-2xl p-6 shadow-sm space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">@lang('attributes.name') *</label>
                    <input type="text" name="name" value="{{ old('name', $attribute->name) }}" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">@lang('attributes.type')</label>
                    <select name="type" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="text" {{ old('type', $attribute->type) === 'text' ? 'selected' : '' }}>Text</option>
                        <option value="number" {{ old('type', $attribute->type) === 'number' ? 'selected' : '' }}>Number</option>
                        <option value="select" {{ old('type', $attribute->type) === 'select' ? 'selected' : '' }}>Select</option>
                    </select>
                </div>
                <div>
                    <label class="flex items-center space-x-3">
                        <input type="checkbox" name="is_variant_option" value="1" {{ old('is_variant_option', $attribute->is_variant_option) ? 'checked' : '' }} class="w-5 h-5 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                        <span class="text-sm text-gray-700">@lang('attributes.mark_variant')</span>
                    </label>
                </div>
                <div>
                    <label class="flex items-center space-x-3">
                        <input type="checkbox" name="is_color" value="1" {{ old('is_color', $attribute->is_color) ? 'checked' : '' }} class="w-5 h-5 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                        <span class="text-sm text-gray-700">@lang('attributes.is_color')</span>
                    </label>
                    <p class="text-xs text-gray-500 mt-1">@lang('attributes.color_help')</p>
                </div>
                <div x-data="{
                    values: @js($optionValues),
                    newValue: '',
                    get optionsString() { return this.values.join(', '); },
                    addValue() { var v = this.newValue.trim(); if (v && !this.values.includes(v)) { this.values.push(v); } this.newValue = ''; },
                    removeValue(idx) { this.values.splice(idx, 1); }
                }">
                    <label class="block text-sm font-medium text-gray-700 mb-1">@lang('attributes.options')</label>
                    <div class="flex gap-2 mb-2">
                        <input type="text" x-model="newValue" @keydown.enter.prevent="addValue()"
                               placeholder="@lang('attributes.options_placeholder')"
                               class="flex-1 px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <button type="button" @click="addValue()"
                                class="px-4 py-3 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition text-sm whitespace-nowrap">
                            + @lang('attributes.add')
                        </button>
                    </div>
                    <div class="flex flex-wrap gap-2 mb-1">
                        <template x-for="(val, idx) in values" :key="idx">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-purple-50 text-purple-700 rounded-lg text-sm font-medium">
                                <span x-text="val"></span>
                                <button type="button" @click="removeValue(idx)" class="text-purple-400 hover:text-red-500 transition">&times;</button>
                            </span>
                        </template>
                    </div>
                    <input type="hidden" name="options" x-model="optionsString">
                    <p class="text-xs text-gray-500 mt-1">@lang('attributes.options_help')</p>
                </div>
            </div>
            <div class="mt-6 flex space-x-3">
                <button type="submit" class="bg-purple-600 text-white px-6 py-3 rounded-xl font-medium hover:bg-purple-700 transition">@lang('attributes.update_btn')</button>
                <a href="{{ route('inventory.attributes.index') }}" class="bg-gray-100 text-gray-700 px-6 py-3 rounded-xl font-medium hover:bg-gray-200 transition">@lang('common.cancel')</a>
            </div>
        </form>
    </div>
</div>
@endsection
