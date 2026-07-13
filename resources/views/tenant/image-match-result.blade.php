@extends('layouts.tenant')

@section('title', __('image_match.result_title').' - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    {{-- Header --}}
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">@lang('image_match.result_title')</h1>
                    <p class="text-gray-600">{{ $sourceImage }}</p>
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
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            {{-- Source Image --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900 mb-4">@lang('image_match.source_image')</h2>
                <div class="text-center">
                    <div id="sourceContainer" class="relative inline-block">
                        <img src="{{ asset('storage/temp/' . $sourceImage) }}" class="max-w-full h-auto rounded-xl" alt="Source">
                        <canvas id="sourceCanvas" class="hidden absolute top-0 left-0 w-full h-full rounded-xl"></canvas>
                    </div>
                </div>
                <div class="flex space-x-2 mt-4">
                    <button onclick="toggleAllBoxes()" class="flex-1 px-4 py-2 border border-gray-300 rounded-xl text-sm font-medium hover:bg-gray-50 transition">
                        @lang('image_match.toggle_boxes')
                    </button>
                    <a href="{{ url('/dashboard') }}" class="flex-1 px-4 py-2 bg-purple-600 text-white rounded-xl text-sm font-medium hover:bg-purple-700 transition text-center">
                        @lang('image_match.new_search')
                    </a>
                </div>
            </div>

            {{-- Results --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900 mb-4">@lang('image_match.found_matches', ['count' => count($results)])</h2>

                @if(count($results) > 0)
                    <div class="space-y-4 max-h-[600px] overflow-y-auto">
                        @foreach($results as $index => $result)
                            <div class="p-4 border border-gray-200 rounded-xl hover:border-purple-300 transition cursor-pointer {{ $index === 0 ? 'border-green-500 bg-green-50' : '' }}" onclick="highlightBox({{ $index }})">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-bold {{ $index === 0 ? 'text-green-700' : 'text-gray-700' }}">
                                        @if($index === 0)
                                            @lang('image_match.best_match')
                                        @else
                                            @lang('image_match.match_num', ['num' => $index + 1])
                                        @endif
                                    </span>
                                    <span class="text-xs px-2 py-1 rounded-full {{ $index === 0 ? 'bg-green-200 text-green-800' : 'bg-gray-200 text-gray-600' }}">
                                        {{ round($result['score'] * 100, 1) }}%
                                    </span>
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
                                @if(isset($result['confidence']))
                                    <div class="mt-2">
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="bg-purple-600 h-2 rounded-full" style="width: {{ $result['confidence'] * 100 }}%"></div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-gray-500">@lang('image_match.no_matches')</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const bboxes = @json($bboxes ?? []);
const boxes = @json($boxes ?? []);
const canvas = document.getElementById('sourceCanvas');
const img = document.getElementById('sourceContainer').querySelector('img');
const ctx = canvas.getContext('2d');
let showBoxes = false;

img.onload = () => {
    canvas.width = img.naturalWidth;
    canvas.height = img.naturalHeight;
    canvas.style.width = img.offsetWidth + 'px';
    canvas.style.height = img.offsetHeight + 'px';
};

function toggleAllBoxes() {
    showBoxes = !showBoxes;
    if (showBoxes) {
        drawAllBoxes();
    } else {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
    }
}

function drawAllBoxes() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    const colors = ['green', 'red', 'blue', 'orange', 'purple'];
    boxes.forEach((box, i) => {
        if (box.bbox && box.bbox.length === 4) {
            ctx.strokeStyle = colors[i % colors.length];
            ctx.lineWidth = 3;
            ctx.strokeRect(box.bbox[0], box.bbox[1], box.bbox[2], box.bbox[3]);
            ctx.fillStyle = colors[i % colors.length];
            ctx.font = 'bold 20px Arial';
            ctx.fillText(box.label || `#${i + 1}`, box.bbox[0], box.bbox[1] - 5);
        }
    });
}

function highlightBox(index) {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    const box = boxes[index];
    if (box && box.bbox && box.bbox.length === 4) {
        canvas.classList.remove('hidden');
        ctx.strokeStyle = 'red';
        ctx.lineWidth = 4;
        ctx.strokeRect(box.bbox[0], box.bbox[1], box.bbox[2], box.bbox[3]);
        ctx.fillStyle = 'red';
        ctx.font = 'bold 24px Arial';
        ctx.fillText(box.label || `#${index + 1}`, box.bbox[0], box.bbox[1] - 8);
    }
}
</script>
@endpush
@endsection
