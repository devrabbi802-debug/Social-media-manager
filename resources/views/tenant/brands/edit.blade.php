@extends('layouts.tenant')

@section('title', 'এডিট - ' . $brand->name . ' - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <h1 class="text-2xl font-bold text-gray-900">ব্র্যান্ড এডিট করুন</h1>
        </div>
    </div>
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <form action="{{ route('inventory.brands.update', $brand) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf @method('PUT')
            <div class="bg-white rounded-2xl p-6 shadow-sm space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ব্র্যান্ডের নাম *</label>
                    <input type="text" name="name" value="{{ old('name', $brand->name) }}" required class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                    @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">লোগো</label>
                    @if($brand->logo)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $brand->logo) }}" class="w-16 h-16 rounded-lg object-cover">
                        </div>
                    @endif
                    <div id="image-dropzone" class="relative border-2 border-dashed border-gray-300 rounded-xl p-6 text-center cursor-pointer hover:border-purple-400 hover:bg-purple-50 transition-all duration-200">
                        <input type="file" name="logo" accept="image/*" class="hidden" id="image-input">
                        <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <p class="mt-2 text-sm text-gray-600">ড্র্যাগ করুন অথবা <span class="text-purple-600 font-medium">ক্লিক করে সিলেক্ট করুন</span></p>
                        <p class="mt-1 text-xs text-gray-400">PNG, JPG, WEBP (সর্বোচ্চ ৫MB)</p>
                    </div>
                    <div id="image-preview" class="mt-3"></div>
                </div>
                <div class="flex items-center">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $brand->is_active) ? 'checked' : '' }} class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                    <label class="ml-2 text-sm text-gray-700">সক্রিয়</label>
                </div>
            </div>
            <div class="flex justify-end space-x-4">
                <a href="{{ route('inventory.brands.index') }}" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-xl font-medium hover:bg-gray-50">বাতিল</a>
                <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700">আপডেট করুন</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const imageDropzone = document.getElementById('image-dropzone');
const imageInput = document.getElementById('image-input');
const imagePreview = document.getElementById('image-preview');

imageDropzone.addEventListener('click', () => imageInput.click());

imageDropzone.addEventListener('dragover', (e) => {
    e.preventDefault();
    imageDropzone.classList.add('border-purple-500', 'bg-purple-50');
});

imageDropzone.addEventListener('dragleave', () => {
    imageDropzone.classList.remove('border-purple-500', 'bg-purple-50');
});

imageDropzone.addEventListener('drop', (e) => {
    e.preventDefault();
    imageDropzone.classList.remove('border-purple-500', 'bg-purple-50');
    const file = e.dataTransfer.files[0];
    if (file && file.type.startsWith('image/')) {
        imageInput.files = e.dataTransfer.files;
        showPreview(file);
    }
});

imageInput.addEventListener('change', (e) => {
    if (e.target.files[0]) showPreview(e.target.files[0]);
});

function showPreview(file) {
    const reader = new FileReader();
    reader.onload = function(ev) {
        imagePreview.innerHTML = `<div class="relative inline-block"><img src="${ev.target.result}" class="w-20 h-20 object-cover rounded-lg"><button type="button" onclick="clearImage()" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs">✕</button></div>`;
    };
    reader.readAsDataURL(file);
}

function clearImage() {
    imageInput.value = '';
    imagePreview.innerHTML = '';
}
</script>
@endpush
@endsection
