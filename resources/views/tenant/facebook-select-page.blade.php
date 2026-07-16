@extends('layouts.tenant')

@section('title', __('facebook.page_select_title').' - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">@lang('facebook.page_select_title')</h1>
                    <p class="text-gray-600">@lang('facebook.page_select_subtitle')</p>
                </div>
                <a href="{{ url('/integration') }}" class="text-gray-600 hover:text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl">
                {{ session('error') }}
            </div>
        @endif

        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl">
                {{ session('success') }}
            </div>
        @endif

        {{-- Profile Header --}}
        <div class="bg-white rounded-2xl p-6 shadow-sm mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mr-4">
                        <svg class="w-7 h-7 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">{{ $profile['name'] ?? ($pages[0]['name'] ?? 'Zernio Profile') }}</h2>
                        <p class="text-sm text-gray-500">
                            <span class="font-mono text-xs">{{ $profile['id'] ?? 'N/A' }}</span>
                            @if(!empty($pages) && count($pages) > 0)
                                — <span class="text-green-600 font-medium">{{ count($pages) }} @lang('facebook.pages_available')</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pages List --}}
        @if(!empty($pages) && count($pages) > 0)
            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <h3 class="text-lg font-bold text-gray-900 mb-4">@lang('facebook.select_page_title')</h3>
                <div class="space-y-3">
                    @foreach($pages as $page)
                        <form action="{{ route('zernio.connect.page') }}" method="POST">
                            @csrf
                            <input type="hidden" name="page_id" value="{{ $page['id'] }}">
                            <input type="hidden" name="page_name" value="{{ $page['name'] }}">
                            <input type="hidden" name="access_token" value="{{ $page['access_token'] ?? '' }}">
                            <input type="hidden" name="zernio_account_id" value="{{ $accountId ?? '' }}">
                            <input type="hidden" name="tempToken" value="{{ $tempToken ?? '' }}">
                            <button type="submit" class="w-full text-left p-4 border-2 border-gray-200 rounded-xl hover:border-purple-500 hover:bg-purple-50 transition flex items-center justify-between group">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center mr-3">
                                        <svg class="w-6 h-6 text-gray-500 group-hover:text-purple-600" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388,10.954,10.125,11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007,1.792-4.669,4.533-4.669 1.312,0,2.686.235,2.686.235v2.953H15.83c-1.491,0-1.956.925-1.956,1.874v2.25h3.328l-.532,3.47h-2.796v8.385C19.612,23.027,24,18.062,24,12.073z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 group-hover:text-purple-700">{{ $page['name'] }}</p>
                                        <p class="text-xs text-gray-500">ID: {{ $page['id'] }}</p>
                                    </div>
                                </div>
                                <svg class="w-5 h-5 text-gray-400 group-hover:text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                        </form>
                    @endforeach
                </div>
                <p class="text-xs text-gray-500 mt-4">@lang('facebook.no_pages_help')</p>
            </div>
        @else
            <div class="bg-white rounded-2xl p-8 shadow-sm text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">@lang('facebook.no_pages_found')</h3>
                <p class="text-sm text-gray-500 mb-4">@lang('facebook.no_pages_desc')</p>
                <ul class="text-sm text-gray-600 text-left max-w-md mx-auto space-y-2 mb-6">
                    <li>• @lang('facebook.pages_add_help1')</li>
                    <li>• @lang('facebook.pages_add_help2')</li>
                    <li>• @lang('facebook.pages_add_help3')</li>
                </ul>
                <a href="{{ url('/integration') }}" class="inline-flex items-center px-6 py-3 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition">
                    @lang('facebook.go_back')
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
