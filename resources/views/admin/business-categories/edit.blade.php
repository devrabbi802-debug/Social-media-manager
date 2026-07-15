@extends('admin.layouts.app')

@section('title', 'Edit Business Category - Admin')

@section('content')
<div class="max-w-3xl" x-data="categoryForm()">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Business Category</h1>
            <p class="text-gray-500 text-sm mt-1">Update "{{ $category->name }}"</p>
        </div>
        <a href="{{ route('admin.business-categories.index') }}" class="text-gray-500 hover:text-gray-700 text-sm font-medium">&larr; Back</a>
    </div>

    @if ($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg text-sm">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('admin.business-categories.update', $category) }}">
        @csrf
        @method('PUT')
        <div class="bg-white rounded-xl shadow-sm border p-6 space-y-6">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                    <input type="text" name="name" value="{{ old('name', $category->name) }}" required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Icon (emoji)</label>
                    <input type="text" name="icon" value="{{ old('icon', $category->icon) }}"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent text-sm">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="flex items-center space-x-3">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }}
                           class="w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                    <label class="text-sm font-medium text-gray-700">Active</label>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $category->sort_order) }}" min="0"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent text-sm">
                </div>
            </div>

            {{-- Extra Fields --}}
            <div class="border-t pt-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-gray-900">Extra Fields</h3>
                    <button type="button" @click="addField()"
                            class="text-emerald-600 hover:text-emerald-800 text-sm font-medium">+ Add Field</button>
                </div>

                <template x-for="(field, index) in fields" :key="index">
                    <div class="border border-gray-200 rounded-lg p-4 mb-3 space-y-3 relative">
                        <button type="button" @click="removeField(index)"
                                class="absolute top-2 right-2 text-red-400 hover:text-red-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Field Name *</label>
                                <input :name="'extra_fields[' + index + '][name]'" x-model="field.name" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Label *</label>
                                <input :name="'extra_fields[' + index + '][label]'" x-model="field.label" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Type *</label>
                                <select :name="'extra_fields[' + index + '][type]'" x-model="field.type" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                                    <option value="text">Text</option>
                                    <option value="textarea">Textarea</option>
                                    <option value="number">Number</option>
                                    <option value="boolean">Boolean (Checkbox)</option>
                                    <option value="select">Select</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Placeholder</label>
                                <input :name="'extra_fields[' + index + '][placeholder]'" x-model="field.placeholder"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Default Value</label>
                                <input :name="'extra_fields[' + index + '][default]'" x-model="field.default"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                            </div>
                        </div>
                        <div class="flex items-center space-x-4">
                            <label class="flex items-center space-x-2">
                                <input type="checkbox" :name="'extra_fields[' + index + '][required]'" :checked="field.required" @change="field.required = $event.target.checked"
                                       class="w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                                <span class="text-xs text-gray-600">Required</span>
                            </label>
                        </div>
                        <div x-show="field.type === 'select'">
                            <label class="block text-xs text-gray-500 mb-1">Options (one per line)</label>
                            <textarea :name="'extra_fields[' + index + '][options]'" x-model="field.optionsText" rows="2"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500"
                                      @blur="field.options = field.optionsText.split('\n').filter(o => o.trim())"></textarea>
                        </div>
                    </div>
                </template>

                <div x-show="fields.length === 0" class="text-center py-6 text-gray-400 text-sm">
                    No extra fields defined.
                </div>
            </div>
        </div>

        <div class="mt-6 flex justify-end space-x-3">
            <a href="{{ route('admin.business-categories.index') }}"
               class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition text-sm">Cancel</a>
            <button type="submit"
                    class="px-6 py-2.5 bg-emerald-500 text-white rounded-lg font-medium hover:bg-emerald-600 transition text-sm">Update Category</button>
        </div>
    </form>
</div>

<script>
function categoryForm() {
    const existing = @json($category->extra_fields ?? []);
    return {
        fields: existing.map(f => ({
            ...f,
            optionsText: Array.isArray(f.options) ? f.options.join('\n') : (f.options || ''),
            required: !!f.required,
            placeholder: f.placeholder || '',
            default: f.default || '',
        })),
        addField() {
            this.fields.push({ name: '', label: '', type: 'text', required: false, placeholder: '', default: '', options: [], optionsText: '' });
        },
        removeField(index) {
            this.fields.splice(index, 1);
        }
    }
}
</script>
@endsection
