@extends('layouts.app')

@section('title', 'Facebook Page নির্বাচন - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <h1 class="text-2xl font-bold text-gray-900">Facebook Page নির্বাচন করুন</h1>
            <p class="text-gray-600">আপনার Facebook অ্যাকাউন্টে একাধিক Page আছে। কোনটি সংযুক্ত করতে চান?</p>
        </div>
    </div>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid gap-4">
            @foreach($pages as $page)
                <form action="{{ route('facebook.connect.page') }}" method="POST">
                    @csrf
                    <input type="hidden" name="page_id" value="{{ $page['id'] }}">
                    <button type="submit" class="w-full bg-white rounded-2xl p-6 shadow-sm border-2 border-gray-200 hover:border-blue-500 hover:shadow-md transition text-left flex items-center justify-between group">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mr-4 group-hover:bg-blue-200 transition">
                                <svg class="w-7 h-7 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388,10.954,10.125,11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007,1.792-4.669,4.533-4.669 1.312,0,2.686.235,2.686.235v2.953H15.83c-1.491,0-1.956.925-1.956,1.874v2.25h3.328l-.532,3.47h-2.796v8.385C19.612,23.027,24,18.062,24,12.073z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">{{ $page['name'] }}</h3>
                                <p class="text-sm text-gray-500">ID: {{ $page['id'] }}@if(isset($page['category'])) · {{ $page['category'] }}@endif</p>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </form>
            @endforeach
        </div>

        <div class="mt-6 text-center">
            <a href="{{ route('facebook.settings') }}" class="text-gray-500 hover:text-gray-700 text-sm">
                ← ফিরে যান
            </a>
        </div>
    </div>
</div>
@endsection
