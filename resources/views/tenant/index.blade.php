@extends('layouts.tenant')

@section('title', __('tenant.title').' - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    {{-- Dashboard Header --}}
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">@lang('tenant.title')</h1>
                    <p class="text-gray-600">@lang('tenant.welcome'), {{ Auth::user()->name ?? __('tenant.user') }}</p>
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
        @include('tenant.partials._nav-tabs', ['activePage' => 'dashboard'])

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-2xl p-6 shadow-sm card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">@lang('tenant.total_conversations')</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $totalConversations }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">@lang('tenant.ai_replies')</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $aiReplies }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">@lang('tenant.total_messages')</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $totalMessages }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">@lang('tenant.today_messages')</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $todayMessages }}</p>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-8">
            {{-- Left Column --}}
            <div class="lg:col-span-2 space-y-8">
                {{-- WhatsApp & Facebook Integration --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">@lang('tenant.platform_integration')</h2>
                    <div class="grid md:grid-cols-2 gap-4">
                        {{-- WhatsApp (coming soon) --}}
                        <div class="border border-gray-200 rounded-xl p-4 relative overflow-hidden">
                            <div class="absolute top-0 right-0 bg-yellow-400 text-yellow-900 text-xs font-bold px-2 py-0.5 rounded-bl-lg">@lang('common.coming_soon')</div>
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3 opacity-50">
                                        <svg class="w-6 h-6 text-green-600" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M12 2C6.477 2 2 6.477 2 12c0 1.89.525 3.66 1.438 5.168L2 22l4.832-1.438A9.955 9.955 0 0012 22c5.523 0 10-4.477 10-10S17.523 2 12 2z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900">WhatsApp</h3>
                                        <p class="text-sm text-yellow-600">@lang('integration.coming_soon')</p>
                                    </div>
                                </div>
                                <span class="w-3 h-3 bg-yellow-400 rounded-full"></span>
                            </div>
                            <p class="text-sm text-gray-500">@lang('tenant.whatsapp_coming_soon')</p>
                        </div>

                        {{-- Facebook --}}
                        <div class="border border-gray-200 rounded-xl p-4">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                        <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388,10.954,10.125,11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007,1.792-4.669,4.533-4.669 1.312,0,2.686.235,2.686.235v2.953H15.83c-1.491,0-1.956.925-1.956,1.874v2.25h3.328l-.532,3.47h-2.796v8.385C19.612,23.027,24,18.062,24,12.073z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900">Facebook</h3>
                                        @if($facebookSetting)
                                            <p class="text-sm text-green-600">@lang('common.connected')</p>
                                        @else
                                            <p class="text-sm text-gray-500">@lang('common.disconnected')</p>
                                        @endif
                                    </div>
                                </div>
                                @if($facebookSetting)
                                    <span class="w-3 h-3 bg-green-500 rounded-full"></span>
                                @else
                                    <span class="w-3 h-3 bg-gray-300 rounded-full"></span>
                                @endif
                            </div>
                            @if($facebookSetting)
                                <p class="text-sm text-gray-500">@lang('tenant.page_id') {{ \Illuminate\Support\Str::limit($facebookSetting->page_id, 20) }}</p>
                                <p class="text-sm text-gray-500">@lang('tenant.today_messages_count') {{ $todayMessages }}</p>
                                @if($facebookSetting->ai_auto_reply_enabled)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700 mt-2">@lang('tenant.ai_auto_reply_active')</span>
                                @endif
                            @else
                                <p class="text-sm text-gray-500">@lang('tenant.connect_facebook')</p>
                                <a href="{{ route('integration') }}" class="inline-flex items-center mt-2 text-sm text-blue-600 hover:text-blue-700 font-medium">
                                    @lang('tenant.connect')
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Recent Leads --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold text-gray-900">@lang('tenant.recent_leads')</h2>
                        <a href="{{ route('conversations') }}" class="text-purple-600 hover:text-purple-700 text-sm font-medium">@lang('tenant.see_all')</a>
                    </div>
                    <div class="overflow-x-auto">
                        @if($recentConversations->count())
                        <table class="w-full">
                            <thead>
                                <tr class="text-left text-sm text-gray-500 border-b">
                                    <th class="pb-3 font-medium">@lang('common.name')</th>
                                    <th class="pb-3 font-medium">@lang('tenant.platform')</th>
                                    <th class="pb-3 font-medium">@lang('tenant.last_message')</th>
                                    <th class="pb-3 font-medium">@lang('tenant.time')</th>
                                    <th class="pb-3 font-medium">@lang('common.status')</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach($recentConversations as $conversation)
                                <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('conversations.show', $conversation) }}'">
                                    <td class="py-3">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center text-purple-600 font-bold text-sm">
                                                {{ mb_substr($conversation->sender_name ?? $conversation->sender_id, 0, 1) }}
                                            </div>
                                            <span class="ml-3 font-medium text-gray-900">{{ $conversation->sender_name ?? 'ID: '.$conversation->sender_id }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Facebook</span>
                                    </td>
                                    <td class="py-3 text-gray-600 text-sm">
                                        @if($conversation->latestMessage)
                                            {{ Str::limit($conversation->latestMessage->content, 40) }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="py-3 text-gray-500 text-sm">
                                        @if($conversation->last_message_at)
                                            {{ $conversation->last_message_at->diffForHumans() }}
                                        @endif
                                    </td>
                                    <td class="py-3">
                                        @if($conversation->status === 'open')
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">@lang('common.open')</span>
                                        @elseif($conversation->status === 'closed')
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">@lang('common.close')</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">@lang('common.open')</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @else
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            <p class="text-gray-500 text-sm">@lang('tenant.no_leads')</p>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Inventory Status --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold text-gray-900">@lang('tenant.inventory_status')</h2>
                        <a href="{{ route('inventory.index') }}" class="text-purple-600 hover:text-purple-700 text-sm font-medium">@lang('tenant.see_all')</a>
                    </div>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">ফ্যাশন প্রোডাক্ট A</p>
                                    <p class="text-sm text-gray-500">স্টক: ৪৫ পিস</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">@lang('tenant.good')</span>
                        </div>

                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">ইলেকট্রনিকস প্রোডাক্ট B</p>
                                    <p class="text-sm text-gray-500">স্টক: ৮ পিস</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">@lang('tenant.low')</span>
                        </div>

                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">হোম ডেকোর C</p>
                                    <p class="text-sm text-gray-500">স্টক: ২ পিস</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">@lang('tenant.problem')</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column --}}
            <div class="space-y-8">
                {{-- Subscription Status --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">@lang('tenant.subscription')</h2>
                    <div class="bg-gradient-to-br from-purple-600 to-blue-600 rounded-xl p-4 text-white">
                        <div class="flex items-center justify-between mb-3">
                            <span class="font-semibold">@lang('tenant.professional_plan')</span>
                            <span class="text-sm text-white/70">@lang('tenant.monthly')</span>
                        </div>
                        <div class="mb-3">
                            <div class="flex justify-between text-sm mb-1">
                                <span>@lang('tenant.ai_reply_usage')</span>
                                <span>৪৫৬/২,০০০</span>
                            </div>
                            <div class="w-full bg-white/20 rounded-full h-2">
                                <div class="bg-white rounded-full h-2" style="width: 22.8%"></div>
                            </div>
                        </div>
                        <p class="text-sm text-white/70">@lang('tenant.billing_cycle')</p>
                    </div>
                    <a href="{{ url('/pricing') }}" class="block w-full text-center mt-4 border border-purple-600 text-purple-600 px-4 py-2 rounded-xl font-medium hover:bg-purple-50 transition">
                        @lang('tenant.upgrade_plan')
                    </a>
                </div>

                {{-- Quick Actions --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">@lang('tenant.quick_actions')</h2>
                    <div class="space-y-3">
                        <a href="{{ route('whatsapp.send') }}" class="flex items-center p-3 bg-green-50 rounded-xl hover:bg-green-100 transition">
                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-green-600" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2C6.477 2 2 6.477 2 12c0 1.89.525 3.66 1.438 5.168L2 22l4.832-1.438A9.955 9.955 0 0012 22c5.523 0 10-4.477 10-10S17.523 2 12 2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">@lang('tenant.send_whatsapp')</p>
                                <p class="text-sm text-gray-500">@lang('tenant.send_msg_to_customer')</p>
                            </div>
                        </a>

                        <a href="{{ route('facebook.post') }}" class="flex items-center p-3 bg-blue-50 rounded-xl hover:bg-blue-100 transition">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388,10.954,10.125,11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007,1.792-4.669,4.533-4.669 1.312,0,2.686.235,2.686.235v2.953H15.83c-1.491,0-1.956.925-1.956,1.874v2.25h3.328l-.532,3.47h-2.796v8.385C19.612,23.027,24,18.062,24,12.073z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">@lang('tenant.facebook_post')</p>
                                <p class="text-sm text-gray-500">@lang('tenant.create_new_post')</p>
                            </div>
                        </a>

                        <a href="{{ route('inventory.products.create') }}" class="flex items-center p-3 bg-orange-50 rounded-xl hover:bg-orange-100 transition">
                            <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">@lang('tenant.add_product')</p>
                                <p class="text-sm text-gray-500">@lang('tenant.add_product_desc')</p>
                            </div>
                        </a>

                        <a href="{{ route('reports') }}" class="flex items-center p-3 bg-purple-50 rounded-xl hover:bg-purple-100 transition">
                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">@lang('tenant.view_reports')</p>
                                <p class="text-sm text-gray-500">@lang('tenant.detailed_analytics')</p>
                            </div>
                        </a>
                    </div>
                </div>

                {{-- Recent Activity --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">@lang('tenant.recent_activity')</h2>
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-900">@lang('tenant.ai_reply_sent')</p>
                                <p class="text-xs text-gray-500">রাকিব হাসানকে WhatsApp-এ</p>
                                <p class="text-xs text-gray-400">২ মিনিট আগে</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-900">@lang('tenant.new_order')</p>
                                <p class="text-xs text-gray-500">সাবরিনা আক্তার - ৫০০ টাকা</p>
                                <p class="text-xs text-gray-400">৫ মিনিট আগে</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-900">@lang('tenant.stock_low')</p>
                                <p class="text-xs text-gray-500">হোম ডেকোর C - মাত্র ২ পিস বাকি</p>
                                <p class="text-xs text-gray-400">১ ঘণ্টা আগে</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
