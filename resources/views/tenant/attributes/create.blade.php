@extends('layouts.tenant')

@section('title', __('attributes.create_title').' - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">@lang('attributes.create_title')</h1>
                    <p class="text-gray-600">@lang('attributes.create_subtitle')</p>
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
        <form action="{{ route('inventory.attributes.store') }}" method="POST">
            @csrf

            @if($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl">
                <ul class="list-disc list-inside text-sm">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="bg-white rounded-2xl p-6 shadow-sm space-y-4">
                <div id="category-field">
                    <x-searchable-select
                        name="category_id"
                        :options="$categories->map(fn($c) => ['value' => $c->id, 'label' => $c->name])->values()->toArray()"
                        :selected="old('category_id', $categoryId ?? '')"
                        :label="__('attributes.category')"
                        :error="$errors->first('category_id')"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">@lang('attributes.name') *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">@lang('attributes.data_type')</label>
                    <select name="data_type" id="data-type-select" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="text" {{ old('data_type') === 'text' ? 'selected' : '' }}>Text</option>
                        <option value="textarea" {{ old('data_type') === 'textarea' ? 'selected' : '' }}>Textarea</option>
                        <option value="number" {{ old('data_type') === 'number' ? 'selected' : '' }}>Number</option>
                        <option value="select" {{ old('data_type') === 'select' ? 'selected' : '' }}>Select</option>
                        <option value="multiselect" {{ old('data_type') === 'multiselect' ? 'selected' : '' }}>Multi Select</option>
                        <option value="boolean" {{ old('data_type') === 'boolean' ? 'selected' : '' }}>Boolean (Yes/No)</option>
                        <option value="date" {{ old('data_type') === 'date' ? 'selected' : '' }}>Date</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="flex items-center space-x-3">
                            <input type="checkbox" name="is_variant" value="1" {{ old('is_variant') ? 'checked' : '' }} class="w-5 h-5 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                            <span class="text-sm text-gray-700">@lang('attributes.mark_variant')</span>
                        </label>
                        <p class="text-xs text-gray-500 mt-1">@lang('attributes.variant_help')</p>
                    </div>
                    <div>
                        <label class="flex items-center space-x-3">
                            <input type="checkbox" name="is_filterable" value="1" {{ old('is_filterable') ? 'checked' : '' }} class="w-5 h-5 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                            <span class="text-sm text-gray-700">@lang('attributes.is_filterable')</span>
                        </label>
                        <p class="text-xs text-gray-500 mt-1">@lang('attributes.filterable_help')</p>
                    </div>
                </div>

                <div>
                    <label class="flex items-center space-x-3">
                        <input type="checkbox" name="is_global" value="1" {{ old('is_global') ? 'checked' : '' }} class="w-5 h-5 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                        <span class="text-sm text-gray-700">@lang('attributes.is_global')</span>
                    </label>
                    <p class="text-xs text-gray-500 mt-1">@lang('attributes.global_help')</p>
                </div>

                <!-- Attribute Values (for select/multiselect) -->
                <div id="values-section" class="hidden" x-data="{
                    values: [],
                    newValue: '',
                    newSwatch: '#000000',
                    showSwatch: false,
                    editingColor: null,
                    addValue() {
                        var v = this.newValue.trim();
                        if (v) {
                            this.values.push({ id: null, value: v, swatch_hex: this.showSwatch ? this.newSwatch : null });
                            this.newValue = '';
                        }
                    },
                    removeValue(idx) { this.values.splice(idx, 1); },
                    pickColor(idx) {
                        this.editingColor = this.editingColor === idx ? null : idx;
                    },
                    setColor(idx, hex) {
                        this.values[idx].swatch_hex = hex;
                    },
                    get valuesJson() { return JSON.stringify(this.values); }
                }">
                    <label class="block text-sm font-medium text-gray-700 mb-1">@lang('attributes.values')</label>
                    <div class="flex gap-2 mb-2">
                        <input type="text" x-model="newValue" @keydown.enter.prevent="addValue()"
                               placeholder="@lang('attributes.values_placeholder')"
                               class="flex-1 px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <label class="flex items-center space-x-1 text-xs text-gray-500 px-2">
                            <input type="checkbox" x-model="showSwatch" class="w-4 h-4 text-purple-600 rounded">
                            <span>Color?</span>
                        </label>
                        <input type="color" x-model="newSwatch" x-show="showSwatch"
                               class="w-12 h-12 rounded-xl border border-gray-300 cursor-pointer">
                        <button type="button" @click="addValue()"
                                class="px-4 py-3 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition text-sm whitespace-nowrap">
                            + @lang('attributes.add')
                        </button>
                    </div>
                    <div class="flex flex-wrap gap-2 mb-1">
                        <template x-for="(val, idx) in values" :key="idx">
                            <span class="relative inline-flex items-center gap-1.5 px-3 py-1.5 bg-purple-50 text-purple-700 rounded-lg text-sm font-medium">
                                <span x-show="val.swatch_hex" @click="pickColor(idx)"
                                      class="w-4 h-4 rounded-full border cursor-pointer hover:ring-2 hover:ring-purple-400"
                                      :style="'background-color: ' + val.swatch_hex"
                                      :title="val.swatch_hex">
                                </span>
                                <input type="color" x-show="editingColor === idx" x-model="val.swatch_hex"
                                       @change="setColor(idx, val.swatch_hex)"
                                       class="absolute left-0 top-full mt-1 z-10 w-8 h-8 border rounded cursor-pointer"
                                       @click.outside="editingColor = null">
                                <span x-text="val.value"></span>
                                <button type="button" @click="removeValue(idx)" class="text-purple-400 hover:text-red-500 transition">&times;</button>
                            </span>
                        </template>
                    </div>
                    <input type="hidden" name="values" x-model="valuesJson">
                    <p class="text-xs text-gray-500 mt-1">@lang('attributes.values_help')</p>
                </div>
            </div>

            <div class="mt-6 flex space-x-3">
                <button type="submit" class="bg-purple-600 text-white px-6 py-3 rounded-xl font-medium hover:bg-purple-700 transition">@lang('attributes.create_btn')</button>
                <a href="{{ route('inventory.attributes.index') }}" class="bg-gray-100 text-gray-700 px-6 py-3 rounded-xl font-medium hover:bg-gray-200 transition">@lang('common.cancel')</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const typeSelect = document.getElementById('data-type-select');
        const valuesSection = document.getElementById('values-section');
        const globalCheckbox = document.querySelector('[name="is_global"]');
        const categoryField = document.getElementById('category-field');

        function toggleValues() {
            const val = typeSelect.value;
            valuesSection.classList.toggle('hidden', val !== 'select' && val !== 'multiselect');
        }

        function toggleCategory() {
            categoryField.classList.toggle('hidden', globalCheckbox.checked);
        }

        typeSelect.addEventListener('change', toggleValues);
        globalCheckbox.addEventListener('change', toggleCategory);
        toggleValues();
        toggleCategory();
    });
</script>
@endpush
@endsection
