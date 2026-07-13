@extends('admin.layouts.app')

@section('title', 'AI Image Prompt - Admin')

@section('content')
<div class="max-w-4xl space-y-6">
    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-900">AI Image Prompt</h1>
        <p class="text-gray-500 mt-1">Facebook Messenger এ কাস্টমার যখন ছবি পাঠাবে, CLIP Server এই প্রম্পট ব্যবহার করে ছবি বিশ্লেষণ করবে।</p>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-sm">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Form Card --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-base font-bold text-gray-900">Image Analysis Prompt</h2>
        </div>

        <form action="{{ route('admin.ai-image-prompt.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="p-6">
                <textarea
                    id="prompt_text"
                    name="prompt_text"
                    rows="15"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors text-sm font-mono"
                >{{ old('prompt_text', $prompt->prompt_text) }}</textarea>
            </div>

            <div class="px-6 pb-6">
                <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-emerald-600 text-white rounded-lg font-medium hover:bg-emerald-700 transition-all duration-200 text-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    সংরক্ষণ করুন
                </button>
            </div>
        </form>
    </div>

    {{-- Info --}}
    <div class="bg-emerald-50 rounded-xl border border-emerald-200 p-6">
        <h3 class="text-base font-bold text-emerald-800 mb-3">কিভাবে কাজ করে?</h3>
        <ul class="text-sm text-emerald-700 space-y-2">
            <li class="flex items-start">
                <svg class="w-4 h-4 mr-2 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                কাস্টমার যখন Facebook Messenger এ ছবি পাঠাবে, CLIP Server এই প্রম্পট দিয়ে ছবি বিশ্লেষণ করবে।
            </li>
            <li class="flex items-start">
                <svg class="w-4 h-4 mr-2 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                বিশ্লেষণের ফলাফল AI চ্যাটে ব্যবহার হবে কাস্টমারকে উত্তর দেওয়ার জন্য।
            </li>
            <li class="flex items-start">
                <svg class="w-4 h-4 mr-2 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                প্রম্পট পরিবর্তন করলে নতুন ছবি থেকেই নতুন প্রম্পট ব্যবহার হবে।
            </li>
        </ul>
    </div>
</div>
@endsection
