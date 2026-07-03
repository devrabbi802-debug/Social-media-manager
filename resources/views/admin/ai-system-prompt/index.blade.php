@extends('admin.layouts.app')

@section('title', 'AI System Prompt - Admin')

@section('content')
<div class="p-6">
    <div class="max-w-4xl">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">AI System Prompt</h1>
            <p class="text-gray-500 mt-1">সকল কাস্টমারের Facebook Messenger AI রিপ্লাই এই প্রম্পট ব্যবহার করবে।</p>
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
            <form action="{{ route('admin.ai-prompt.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="p-6">
                    <label for="prompt_text" class="block text-sm font-medium text-gray-700 mb-2">System Prompt</label>
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

        {{-- Placeholder Info --}}
        <div class="mt-6 bg-indigo-50 rounded-xl border border-indigo-200 p-6">
            <h3 class="text-lg font-bold text-indigo-800 mb-3">এভাবে ব্যবহার করুন (Placeholders)</h3>
            <p class="text-sm text-indigo-700 mb-4">প্রম্পটে এই কোডগুলো লিখলে অটোমেটিকলি রিপ্লেস হবে:</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-white rounded-lg p-4 border border-indigo-200">
                    <code class="text-sm font-bold text-indigo-600">{company_name}</code>
                    <p class="text-xs text-gray-600 mt-1">কোম্পানির নাম — কোন কোম্পানির জন্য AI রিপ্লাই দিচ্ছে</p>
                </div>
                <div class="bg-white rounded-lg p-4 border border-indigo-200">
                    <code class="text-sm font-bold text-indigo-600">{owner_name}</code>
                    <p class="text-xs text-gray-600 mt-1">পেজ মালিকের নাম</p>
                </div>
            </div>

            <div class="mt-4 p-4 bg-white rounded-lg border border-indigo-200">
                <h4 class="text-sm font-bold text-gray-700 mb-2">উদাহরণ প্রম্পট:</h4>
                <pre class="text-xs text-gray-600 whitespace-pre-wrap">তুমি {company_name} এর AI সহকারী। সবসময় বাংলায় কথা বলবে। সংক্ষিপ্ত উত্তর দেবে।</pre>
            </div>
        </div>
    </div>
</div>
@endsection
