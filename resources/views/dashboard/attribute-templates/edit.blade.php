@extends('layouts.app')

@section('title', 'এডিট - ' . $attribute->name . ' - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <h1 class="text-2xl font-bold text-gray-900">অ্যাট্রিবিউট এডিট করুন</h1>
        </div>
    </div>
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <form action="{{ route('inventory.attributes.update', $attribute) }}" method="POST" class="space-y-6">
            @csrf @method('PUT')
            <div class="bg-white rounded-2xl p-6 shadow-sm space-y-4">
                {{-- Global Toggle --}}
                <div class="flex items-center space-x-3 p-3 bg-purple-50 rounded-xl">
                    <input type="hidden" name="is_global" value="0">
                    <input type="checkbox" name="is_global" value="1" id="is_global"
                        {{ old('is_global', $attribute->is_global) ? 'checked' : '' }}
                        class="w-5 h-5 rounded border-gray-300 text-purple-600 focus:ring-purple-500"
                        onchange="toggleCategoryField()">
                    <div>
                        <label for="is_global" class="text-sm font-bold text-gray-900 cursor-pointer">গ্লোবাল অ্যাট্রিবিউট</label>
                        <p class="text-xs text-gray-500">চেক করলে এই অ্যাট্রিবিউট সব ক্যাটাগরিতে দেখা যাবে</p>
                    </div>
                </div>

                {{-- Category (hidden when global) --}}
                <div id="category-field" {{ old('is_global', $attribute->is_global) ? 'style="display: none;"' : '' }}>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ক্যাটাগরি *</label>
                    <select name="category_id" id="category_id" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $attribute->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">অ্যাট্রিবিউটের নাম *</label>
                    <input type="text" name="name" value="{{ old('name', $attribute->name) }}" required class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ধরন *</label>
                    <select name="type" id="attr_type" required class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                        <option value="text" {{ old('type', $attribute->type) === 'text' ? 'selected' : '' }}>টেক্সট</option>
                        <option value="number" {{ old('type', $attribute->type) === 'number' ? 'selected' : '' }}>নাম্বার</option>
                        <option value="select" {{ old('type', $attribute->type) === 'select' ? 'selected' : '' }}>সিলেক্ট</option>
                        <option value="boolean" {{ old('type', $attribute->type) === 'boolean' ? 'selected' : '' }}>হ্যাঁ/না</option>
                        <option value="date" {{ old('type', $attribute->type) === 'date' ? 'selected' : '' }}>তারিখ</option>
                    </select>
                </div>
                <div id="options_field" {{ old('type', $attribute->type) !== 'select' ? 'style="display: none;"' : '' }}>
                    <label class="block text-sm font-medium text-gray-700 mb-1">অপশন (কমা দিয়ে আলাদা)</label>
                    <input type="text" name="options" value="{{ old('options', $attribute->options ? implode(', ', $attribute->options) : '') }}" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                </div>
                <div class="flex items-center">
                    <input type="hidden" name="is_required" value="0">
                    <input type="checkbox" name="is_required" value="1" {{ old('is_required', $attribute->is_required) ? 'checked' : '' }} class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                    <label class="ml-2 text-sm text-gray-700">আবশ্যিক</label>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">সাজানোর ক্রম</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $attribute->sort_order) }}" min="0" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                </div>
            </div>
            <div class="flex justify-end space-x-4">
                <a href="{{ route('inventory.attributes.index') }}" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-xl font-medium hover:bg-gray-50">বাতিল</a>
                <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700">আপডেট করুন</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('attr_type').addEventListener('change', function() {
    document.getElementById('options_field').style.display = this.value === 'select' ? 'block' : 'none';
});

function toggleCategoryField() {
    const isGlobal = document.getElementById('is_global').checked;
    const categoryField = document.getElementById('category-field');
    const categoryIdSelect = document.getElementById('category_id');

    if (isGlobal) {
        categoryField.style.display = 'none';
        categoryIdSelect.removeAttribute('required');
    } else {
        categoryField.style.display = 'block';
        categoryIdSelect.setAttribute('required', 'required');
    }
}

// Init on load
toggleCategoryField();
</script>
@endpush
@endsection
