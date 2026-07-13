@extends('layouts.tenant')

@section('title', __('image_match.title').' - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    {{-- Header --}}
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">@lang('image_match.title')</h1>
                    <p class="text-gray-600">@lang('image_match.subtitle')</p>
                </div>
                <a href="{{ url('/dashboard') }}" class="text-gray-600 hover:text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @include('tenant.partials._nav-tabs', ['activePage' => 'integration'])

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            {{-- Image Upload --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900 mb-4">@lang('image_match.upload_title')</h2>

                <form action="{{ route('image-match.match') }}" method="POST" enctype="multipart/form-data" id="imageForm">
                    @csrf
                    <input type="hidden" name="action" value="match">

                    <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-purple-500 transition mb-4" id="dropZone">
                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <p class="text-gray-600 mb-2">@lang('image_match.drag_drop')</p>
                        <p class="text-sm text-gray-500 mb-4">@lang('image_match.file_types')</p>
                        <input type="file" name="image" id="imageInput" accept="image/*" class="hidden">
                        <button type="button" onclick="document.getElementById('imageInput').click()" class="inline-flex items-center px-6 py-3 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition">
                            @lang('image_match.choose_file')
                        </button>
                    </div>

                    {{-- Image Preview --}}
                    <div id="imagePreview" class="hidden mb-4">
                        <img id="previewImg" class="w-full h-48 object-cover rounded-xl" alt="">
                        <button type="button" onclick="removeImage()" class="mt-2 text-sm text-red-600 hover:underline">@lang('image_match.remove_image')</button>
                    </div>

                    <button type="submit" id="submitBtn" class="w-full bg-purple-600 text-white px-6 py-3 rounded-xl font-medium hover:bg-purple-700 transition" disabled>
                        @lang('image_match.find_matches')
                    </button>
                </form>
            </div>

            {{-- Results --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900 mb-4">@lang('image_match.results_title')</h2>

                @if(isset($results) && count($results) > 0)
                    <div class="space-y-4">
                        @foreach($results as $index => $result)
                            <div class="p-4 border border-gray-200 rounded-xl {{ $index === 0 ? 'border-green-500 bg-green-50' : '' }}">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-bold {{ $index === 0 ? 'text-green-700' : 'text-gray-700' }}">
                                        @if($index === 0)
                                            @lang('image_match.best_match')
                                        @else
                                            @lang('image_match.match_num', ['num' => $index + 1])
                                        @endif
                                    </span>
                                    @if(isset($result['score']))
                                        <span class="text-xs px-2 py-1 rounded-full {{ $index === 0 ? 'bg-green-200 text-green-800' : 'bg-gray-200 text-gray-600' }}">
                                            {{ round($result['score'] * 100, 1) }}%
                                        </span>
                                    @endif
                                </div>
                                <h3 class="font-bold text-gray-900">{{ $result['name'] ?? 'Unknown Product' }}</h3>
                                @if(isset($result['description']))
                                    <p class="text-sm text-gray-600 mt-1">{{ Str::limit($result['description'], 100) }}</p>
                                @endif
                                @if(isset($result['price']))
                                    <p class="text-lg font-bold text-purple-600 mt-2">{{ number_format($result['price'], 2) }} BDT</p>
                                @endif
                                @if(isset($result['sku']))
                                    <p class="text-xs text-gray-500 mt-1">SKU: {{ $result['sku'] }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <p class="text-gray-500">@lang('image_match.upload_to_search')</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const dropZone = document.getElementById('dropZone');
const imageInput = document.getElementById('imageInput');
const imagePreview = document.getElementById('imagePreview');
const previewImg = document.getElementById('previewImg');
const submitBtn = document.getElementById('submitBtn');

dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.classList.add('border-purple-500', 'bg-purple-50');
});

dropZone.addEventListener('dragleave', () => {
    dropZone.classList.remove('border-purple-500', 'bg-purple-50');
});

dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('border-purple-500', 'bg-purple-50');
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        imageInput.files = files;
        showPreview(files[0]);
    }
});

imageInput.addEventListener('change', (e) => {
    if (e.target.files.length > 0) {
        showPreview(e.target.files[0]);
    }
});

function showPreview(file) {
    const reader = new FileReader();
    reader.onload = (e) => {
        previewImg.src = e.target.result;
        imagePreview.classList.remove('hidden');
        dropZone.classList.add('hidden');
        submitBtn.disabled = false;
    };
    reader.readAsDataURL(file);
}

function removeImage() {
    imageInput.value = '';
    imagePreview.classList.add('hidden');
    dropZone.classList.remove('hidden');
    submitBtn.disabled = true;
}
</script>
@endpush
@endsection
