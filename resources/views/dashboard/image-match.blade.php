@extends('layouts.dashboard')

@section('title', 'ছবি ম্যাচিং - Image Recognition')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">ছবি ম্যাচিং</h1>
            <p class="text-gray-600">আপনার ক্যাটালগের প্রোডাক্টের সাথে ছবি ম্যাচ করুন</p>
        </div>

        <!-- CLIP Server Status -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">CLIP Server স্ট্যাটাস</h2>
            <div class="flex items-center space-x-4">
                @if($clipStatus['status'] === 'healthy')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        স্বাস্থ্য সঠিক
                    </span>
                    <div class="text-sm text-gray-600">
                        <p><strong>মডেল:</strong> {{ $clipStatus['details']['model'] ?? 'ViT-B/32' }}</p>
                        <p><strong>ডিভাইস:</strong> {{ $clipStatus['details']['device'] ?? 'unknown' }}</p>
                        <p><strong>এমбедিং ডাইমেনশন:</strong> {{ $clipStatus['details']['embedding_dimension'] ?? 512 }}</p>
                    </div>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                        সংযোগ বিচ্ছিন্ন
                    </span>
                    <div class="text-sm text-red-600">
                        <p>{{ $clipStatus['details']['error'] ?? 'Unknown error' }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Catalog Stats -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">ক্যাটালগ পরিসংখ্যান</h2>
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-blue-50 rounded-lg p-4">
                    <p class="text-sm text-blue-600 font-medium">প্রোডাক্ট ইমেজ</p>
                    <p class="text-2xl font-bold text-blue-800">{{ $productCount }}</p>
                </div>
                <div class="bg-green-50 rounded-lg p-4">
                    <p class="text-sm text-green-600 font-medium">ভ্যারিয়েন্ট ইমেজ</p>
                    <p class="text-2xl font-bold text-green-800">{{ $variantCount }}</p>
                </div>
            </div>
        </div>

        <!-- Upload Form -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">ছবি আপলোড করুন</h2>
            <form action="{{ route('dashboard.image-match.match') }}" method="POST" enctype="multipart/form-data" id="matchForm">
                @csrf
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        প্রোডাক্টের ছবি নির্বাচন করুন
                    </label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-blue-400 transition-colors">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-600 justify-center">
                                <label for="image" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                    <span>ছবি আপলোড করুন</span>
                                    <input id="image" name="image" type="file" class="sr-only" accept="image/*" required onchange="previewImage(this)">
                                </label>
                                <p class="pl-1">অথবা ড্র্যাগ করুন</p>
                            </div>
                            <p class="text-xs text-gray-500">PNG, JPG, GIF সর্বোচ্চ ৫MB</p>
                        </div>
                    </div>
                    
                    <!-- Image Preview -->
                    <div id="imagePreview" class="mt-4 hidden">
                        <img id="previewImg" class="max-w-full h-auto rounded-lg shadow-md" alt="Preview">
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" id="submitBtn" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        ম্যাচ খুঁজুন
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.classList.remove('hidden');
        }
        
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.classList.add('hidden');
    }
}

document.getElementById('matchForm').addEventListener('submit', function() {
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> ম্যাচিং হচ্ছে...';
});
</script>
@endsection
