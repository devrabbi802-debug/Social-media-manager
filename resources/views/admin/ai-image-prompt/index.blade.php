@extends('admin.layouts.app')

@section('title', 'AI Image Prompt - Admin')

@section('content')
<div class="p-6">
    <div class="max-w-4xl">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">AI Image Prompt</h1>
            <p class="text-gray-500 mt-1">Facebook Messenger এ কাস্টমার যখন ছবি পাঠাবে, CLIP Server এই প্রম্পট ব্যবহার করে ছবি বিশ্লেষণ করবে।</p>
        </div>

        @if(session('success'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <form action="{{ route('admin.ai-image-prompt.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="p-6">
                    <label for="prompt_text" class="block text-sm font-medium text-gray-700 mb-2">Image Analysis Prompt</label>
                    <textarea
                        id="prompt_text"
                        name="prompt_text"
                        rows="15"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition text-sm font-mono"
                    >{{ old('prompt_text', $prompt->prompt_text) }}</textarea>
                </div>

                <div class="px-6 pb-6">
                    <button type="submit" class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        সংরক্ষণ করুন
                    </button>
                </div>
            </form>
        </div>

        {{-- Info --}}
        <div class="mt-6 bg-amber-50 rounded-xl border border-amber-200 p-6">
            <h3 class="text-lg font-bold text-amber-800 mb-3">কিভাবে কাজ করে?</h3>
            <ul class="text-sm text-amber-700 space-y-2">
                <li>• কাস্টমার যখন Facebook Messenger এ ছবি পাঠাবে, CLIP Server এই প্রম্পট দিয়ে ছবি বিশ্লেষণ করবে।</li>
                <li>• বিশ্লেষণের ফলাফল AI চ্যাটে ব্যবহার হবে কাস্টমারকে উত্তর দেওয়ার জন্য।</li>
                <li>• প্রম্পট পরিবর্তন করলে নতুন ছবি থেকেই নতুন প্রম্পট ব্যবহার হবে।</li>
            </ul>
        </div>
    </div>
</div>
@endsection
