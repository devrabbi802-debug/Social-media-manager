@extends('layouts.tenant')

@section('title', 'কথোপকথন - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">কথোপকথন</h1>
                    <p class="text-gray-600">Facebook Messenger কাস্টমারদের সাথে কথোপকথন</p>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @include('tenant.partials._nav-tabs', ['activePage' => 'conversations'])

        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900">সব কথোপকথন</h2>
                <p class="text-sm text-gray-500">মোট {{ $conversations->total() }}টি কথোপকথন</p>
            </div>

            @if($conversations->count())
            <div class="divide-y divide-gray-100">
                @foreach($conversations as $conversation)
                <a href="{{ route('conversations.show', $conversation) }}" class="flex items-center px-6 py-4 hover:bg-gray-50 transition">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                            <span class="text-purple-600 font-bold text-lg">
                                {{ mb_substr($conversation->sender_name ?? $conversation->sender_id, 0, 1) }}
                            </span>
                        </div>
                    </div>
                    <div class="ml-4 flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-900 truncate">
                                {{ $conversation->sender_name ?? 'ID: '.$conversation->sender_id }}
                            </h3>
                            @if($conversation->last_message_at)
                            <span class="text-xs text-gray-500">
                                {{ $conversation->last_message_at->diffForHumans() }}
                            </span>
                            @endif
                        </div>
                        @if($conversation->latestMessage)
                        <p class="text-sm text-gray-500 truncate mt-1">
                            @if($conversation->latestMessage->direction === 'outgoing')
                                <span class="text-blue-600">AI:</span>
                            @endif
                            {{ Str::limit($conversation->latestMessage->content, 80) }}
                        </p>
                        @endif
                    </div>
                    <div class="ml-4 flex-shrink-0">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </a>
                @endforeach
            </div>

            <div class="px-6 py-4 border-t border-gray-200">
                {{ $conversations->links() }}
            </div>
            @else
            <div class="px-6 py-12 text-center">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-1">কোনো কথোপকথন নেই</h3>
                <p class="text-gray-500">Facebook Messenger এ কাস্টমার মেসেজ পাঠালে এখানে দেখা যাবে।</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
