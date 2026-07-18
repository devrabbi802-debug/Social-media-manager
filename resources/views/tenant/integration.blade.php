@extends('layouts.tenant')

@section('title', __('integration.title').' - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    {{-- Dashboard Header --}}
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">@lang('integration.title')</h1>
                    <p class="text-gray-600">@lang('integration.subtitle')</p>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                        @lang('common.active')
                    </span>
                    <a href="{{ route('settings') }}" class="text-gray-600 hover:text-purple-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @include('tenant.partials._nav-tabs', ['activePage' => 'integration'])

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Facebook Messenger - Active --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mr-4">
                            <svg class="w-7 h-7 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Messenger</h3>
                            <p class="text-sm text-green-600">@lang('common.connected')</p>
                        </div>
                    </div>
                    <span class="w-3 h-3 bg-green-500 rounded-full"></span>
                </div>
                <div class="space-y-3 mb-6">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">@lang('integration.page')</span>
                        <span class="font-medium text-gray-900">My Business Page</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">@lang('integration.today_messages')</span>
                        <span class="font-medium text-gray-900">১৮টি</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">@lang('integration.ai_reply')</span>
                        <span class="font-medium text-gray-900">@lang('common.active')</span>
                    </div>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('facebook.post') }}" class="flex-1 text-center bg-blue-600 text-white px-4 py-2 rounded-xl font-medium hover:bg-blue-700 transition">@lang('integration.view_messages')</a>
                    <a href="{{ route('facebook.settings') }}" class="px-4 py-2 border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 transition">@lang('settings.title')</a>
                </div>
            </div>

            {{-- Instagram - Upcoming --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm relative overflow-hidden">
                <div class="absolute top-0 right-0 bg-yellow-400 text-yellow-900 text-xs font-bold px-3 py-1 rounded-bl-xl">@lang('integration.coming_soon_badge')</div>
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-pink-100 rounded-xl flex items-center justify-center mr-4 opacity-50">
                            <svg class="w-7 h-7 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <rect x="2" y="2" width="20" height="20" rx="5" stroke-width="2"/>
                                <circle cx="12" cy="12" r="5" stroke-width="2"/>
                                <circle cx="17.5" cy="6.5" r="1.5" fill="currentColor"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Instagram</h3>
                            <p class="text-sm text-yellow-600">@lang('integration.coming_soon')</p>
                        </div>
                    </div>
                    <span class="w-3 h-3 bg-yellow-400 rounded-full"></span>
                </div>
                <p class="text-sm text-gray-500 mb-6">@lang('integration.instagram_description')</p>
                <button disabled class="w-full bg-gray-300 text-gray-500 px-4 py-2 rounded-xl font-medium cursor-not-allowed">@lang('integration.coming_soon')</button>
            </div>

            {{-- WhatsApp - Upcoming --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm relative overflow-hidden">
                <div class="absolute top-0 right-0 bg-yellow-400 text-yellow-900 text-xs font-bold px-3 py-1 rounded-bl-xl">@lang('integration.coming_soon_badge')</div>
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mr-4 opacity-50">
                            <svg class="w-7 h-7 text-green-600" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2C6.477 2 2 6.477 2 12c0 1.89.525 3.66 1.438 5.168L2 22l4.832-1.438A9.955 9.955 0 0012 22c5.523 0 10-4.477 10-10S17.523 2 12 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">WhatsApp</h3>
                            <p class="text-sm text-yellow-600">@lang('integration.coming_soon')</p>
                        </div>
                    </div>
                    <span class="w-3 h-3 bg-yellow-400 rounded-full"></span>
                </div>
                <p class="text-sm text-gray-500 mb-6">@lang('integration.whatsapp_description')</p>
                <button disabled class="w-full bg-gray-300 text-gray-500 px-4 py-2 rounded-xl font-medium cursor-not-allowed">@lang('integration.coming_soon')</button>
            </div>
        </div>
    </div>
</div>
@endsection
