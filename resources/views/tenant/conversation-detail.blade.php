@extends('layouts.tenant')

@section('title', __('conversations.title').' - '.$conversation->sender_name ?? $conversation->sender_id)

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center">
                <a href="{{ route('conversations') }}" class="mr-4 text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                        <span class="text-purple-600 font-bold">
                            {{ mb_substr($conversation->sender_name ?? $conversation->sender_id, 0, 1) }}
                        </span>
                    </div>
                    <div class="ml-3">
                        <h1 class="text-lg font-bold text-gray-900">
                            {{ $conversation->sender_name ?? 'ID: '.$conversation->sender_id }}
                        </h1>
                        <p class="text-sm text-gray-500">
                            Facebook Messenger
                            @if($conversation->last_message_at)
                                · {{ $conversation->last_message_at->diffForHumans() }}
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @include('tenant.partials._nav-tabs', ['activePage' => 'conversations'])
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if($conversation->messages->count())
        @php
            $grouped = [];
            $currentGroup = null;

            foreach ($conversation->messages as $message) {
                $isImage = $message->type === 'image' && $message->direction === 'incoming';

                if ($isImage && $currentGroup !== null && $currentGroup['type'] === 'image_group') {
                    $currentGroup['messages'][] = $message;
                } else {
                    if ($currentGroup !== null) {
                        $grouped[] = $currentGroup;
                    }
                    $currentGroup = $isImage
                        ? ['type' => 'image_group', 'messages' => [$message], 'time' => $message->created_at]
                        : ['type' => 'single', 'message' => $message, 'time' => $message->created_at];
                }
            }

            if ($currentGroup !== null) {
                $grouped[] = $currentGroup;
            }
        @endphp

        <div class="space-y-4">
            @foreach($grouped as $group)
                @if($group['type'] === 'image_group')
                <div class="flex justify-start">
                    <div class="max-w-[75%]">
                        <div class="text-xs text-gray-500 mb-1 ml-1">
                            {{ $conversation->sender_name ?? __('conversations.customer') }}
                            · {{ $group['time']->format('d M, H:i') }}
                        </div>
                        <div class="bg-white shadow-sm border border-gray-100 rounded-2xl px-4 py-3">
                            <div class="flex flex-wrap gap-2">
                                @foreach($group['messages'] as $msg)
                                <img src="{{ $msg->image_path }}" alt="Customer image" class="w-28 h-28 object-cover rounded-lg cursor-pointer hover:opacity-90 transition" onclick="window.open('{{ $msg->image_path }}', '_blank')">
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @else
                @php $message = $group['message']; @endphp
                <div class="flex {{ $message->direction === 'outgoing' ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[75%]">
                        @if($message->direction === 'incoming')
                        <div class="text-xs text-gray-500 mb-1 ml-1">
                            {{ $message->sender_name ?? __('conversations.customer') }}
                            · {{ $message->created_at->format('d M, H:i') }}
                        </div>
                        @endif

                        <div class="rounded-2xl px-4 py-3 {{ $message->direction === 'outgoing' ? 'bg-blue-600 text-white' : 'bg-white shadow-sm border border-gray-100 text-gray-900' }}">
                            @if($message->image_path && $message->direction === 'incoming')
                            <div class="mb-2">
                                <img src="{{ $message->image_path }}" alt="Customer image" class="rounded-lg w-32 h-32 object-cover cursor-pointer hover:opacity-90 transition" onclick="window.open('{{ $message->image_path }}', '_blank')">
                            </div>
                            @endif

                            @if($message->content)
                            <p class="text-sm whitespace-pre-wrap">{{ $message->content }}</p>
                            @endif

                            @if($message->image_analysis)
                            <div class="mt-2 pt-2 border-t {{ $message->direction === 'outgoing' ? 'border-blue-400' : 'border-gray-200' }}">
                                <button onclick="this.nextElementSibling.classList.toggle('hidden')" class="text-xs {{ $message->direction === 'outgoing' ? 'text-blue-200' : 'text-purple-600' }} hover:underline">
                                    @lang('conversations.view_image_analysis')
                                </button>
                                <div class="hidden mt-2 p-3 rounded-lg {{ $message->direction === 'outgoing' ? 'bg-blue-700' : 'bg-gray-50' }}">
                                    @if(isset($message->image_analysis['descriptions']))
                                    @foreach($message->image_analysis['descriptions'] as $desc)
                                    <p class="text-xs {{ $message->direction === 'outgoing' ? 'text-blue-100' : 'text-gray-600' }} mb-2">
                                        <span class="font-semibold">@lang('conversations.image_analysis_label')</span><br>
                                        {{ $desc }}
                                    </p>
                                    @endforeach
                                    @elseif(isset($message->image_analysis['description']))
                                    <p class="text-xs {{ $message->direction === 'outgoing' ? 'text-blue-100' : 'text-gray-600' }}">
                                        <span class="font-semibold">@lang('conversations.image_analysis_label')</span><br>
                                        {{ $message->image_analysis['description'] }}
                                    </p>
                                    @endif
                                    @if(isset($message->image_analysis['image_urls']))
                                    <div class="flex flex-wrap gap-2 mt-2">
                                    @foreach($message->image_analysis['image_urls'] as $url)
                                        <img src="{{ $url }}" alt="Analyzed image" class="w-20 h-20 object-cover rounded-lg cursor-pointer hover:opacity-80 transition" onclick="window.open('{{ $url }}', '_blank')">
                                    @endforeach
                                    </div>
                                    @elseif(isset($message->image_analysis['original_image_url']))
                                    <img src="{{ $message->image_analysis['original_image_url'] }}" alt="Analyzed image" class="w-20 h-20 object-cover rounded-lg cursor-pointer hover:opacity-80 transition mt-2" onclick="window.open('{{ $message->image_analysis['original_image_url'] }}', '_blank')">
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>

                        @if($message->direction === 'outgoing')
                        <div class="text-xs text-gray-500 mb-1 mr-1 text-right">
                            @lang('conversations.ai_reply')
                            · {{ $message->created_at->format('d M, H:i') }}
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            @endforeach
        </div>
        @else
        <div class="bg-white rounded-2xl shadow-sm px-6 py-12 text-center">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-1">@lang('conversations.no_messages')</h3>
            <p class="text-gray-500">@lang('conversations.no_messages_desc')</p>
        </div>
        @endif
    </div>
</div>
@endsection
