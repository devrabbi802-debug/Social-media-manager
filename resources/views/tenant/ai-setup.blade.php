@extends('layouts.tenant')

@section('title', __('ai.title').' - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    {{-- Dashboard Header --}}
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">@lang('ai.title')</h1>
                    <p class="text-gray-600">@lang('ai.subtitle')</p>
                </div>
                <div class="flex items-center space-x-4">
                    @php
                        $totalActive = collect([$groqKey, $cerebrasKey, $geminiKey])->filter(fn($k) => $k && $k->is_active)->count();
                    @endphp
                    @if($totalActive > 0)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                            @lang('ai.active_count', ['count' => $totalActive])
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                            <span class="w-2 h-2 bg-yellow-500 rounded-full mr-2"></span>
                            @lang('common.disconnected')
                        </span>
                    @endif
                    <a href="{{ route('integration') }}" class="text-gray-600 hover:text-purple-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @include('tenant.partials._nav-tabs', ['activePage' => 'ai.setup'])

        {{-- Success/Error Messages --}}
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Main Content --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Groq Key --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <div class="flex items-center mb-6">
                        <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mr-4">
                            <svg class="w-7 h-7 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">Groq API Key (Primary)</h2>
                            <p class="text-sm text-gray-500">@lang('ai.groq_desc')</p>
                        </div>
                    </div>

                    @if($groqKey)
                        <div class="border border-gray-200 rounded-xl p-4 mb-4 {{ $groqKey->is_active ? 'bg-white' : 'bg-gray-50 opacity-60' }}">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="w-10 h-10 {{ $groqKey->is_active ? 'bg-purple-100' : 'bg-gray-200' }} rounded-full flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5 {{ $groqKey->is_active ? 'text-purple-600' : 'text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="flex items-center space-x-2">
                                            <span class="font-medium text-gray-900">Groq Key</span>
                                            @if($groqKey->is_active)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">@lang('common.active')</span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">@lang('common.inactive')</span>
                                            @endif
                                        </div>
                                        <p class="text-sm text-gray-500 font-mono mt-0.5">
                                            {{ substr($groqKey->api_key, 0, 8) }}...{{ substr($groqKey->api_key, -4) }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('ai.setup.test', $groqKey) }}"
                                       class="inline-flex items-center px-3 py-2 text-sm text-gray-600 hover:text-purple-600 hover:bg-purple-50 rounded-lg transition"
                                       title="@lang('ai.test')">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                        </svg>
                                    </a>

                                    <form action="{{ route('ai.setup.toggle', $groqKey) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit"
                                                class="inline-flex items-center px-3 py-2 text-sm {{ $groqKey->is_active ? 'text-green-600 hover:text-yellow-600' : 'text-gray-400 hover:text-green-600' }} hover:bg-gray-50 rounded-lg transition"
                                                title="{{ $groqKey->is_active ? __('ai.deactivate') : __('ai.activate') }}">
                                            @if($groqKey->is_active)
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                                </svg>
                                            @endif
                                        </button>
                                    </form>

                                    <form action="{{ route('ai.setup.destroy', $groqKey) }}" method="POST"
                                          onsubmit="return confirm('{{ __('ai.delete_key_confirm') }}');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center px-3 py-2 text-sm text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition"
                                                title="@lang('ai.delete')">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('ai.setup.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="type" value="message">
                        <div class="mb-4">
                            <label for="groq_api_key" class="block text-sm font-medium text-gray-700 mb-2">Groq API Key</label>
                            <input
                                type="password"
                                id="groq_api_key"
                                name="api_key"
                                value="{{ old('api_key') }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition font-mono text-sm"
                                placeholder="gsk_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
                                required
                            >
                            <p class="mt-2 text-xs text-gray-500">@lang('ai.groq_help')</p>
                        </div>
                        <button type="submit" class="inline-flex items-center px-6 py-3 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition shadow-lg shadow-purple-200">
                            {{ $groqKey ? __('ai.update_btn') : __('ai.add_key') }}
                        </button>
                    </form>
                </div>

                {{-- Cerebras Key --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <div class="flex items-center mb-6">
                        <div class="w-12 h-12 bg-teal-100 rounded-xl flex items-center justify-center mr-4">
                            <svg class="w-7 h-7 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">Cerebras API Key (Secondary)</h2>
                            <p class="text-sm text-gray-500">@lang('ai.cerebras_desc')</p>
                        </div>
                    </div>

                    @if($cerebrasKey)
                        <div class="border border-gray-200 rounded-xl p-4 mb-4 {{ $cerebrasKey->is_active ? 'bg-white' : 'bg-gray-50 opacity-60' }}">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="w-10 h-10 {{ $cerebrasKey->is_active ? 'bg-teal-100' : 'bg-gray-200' }} rounded-full flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5 {{ $cerebrasKey->is_active ? 'text-teal-600' : 'text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="flex items-center space-x-2">
                                            <span class="font-medium text-gray-900">Cerebras Key</span>
                                            @if($cerebrasKey->is_active)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">@lang('common.active')</span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">@lang('common.inactive')</span>
                                            @endif
                                        </div>
                                        <p class="text-sm text-gray-500 font-mono mt-0.5">
                                            {{ substr($cerebrasKey->api_key, 0, 8) }}...{{ substr($cerebrasKey->api_key, -4) }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('ai.setup.test', $cerebrasKey) }}"
                                       class="inline-flex items-center px-3 py-2 text-sm text-gray-600 hover:text-teal-600 hover:bg-teal-50 rounded-lg transition"
                                       title="@lang('ai.test')">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                        </svg>
                                    </a>

                                    <form action="{{ route('ai.setup.toggle', $cerebrasKey) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit"
                                                class="inline-flex items-center px-3 py-2 text-sm {{ $cerebrasKey->is_active ? 'text-green-600 hover:text-yellow-600' : 'text-gray-400 hover:text-green-600' }} hover:bg-gray-50 rounded-lg transition"
                                                title="{{ $cerebrasKey->is_active ? __('ai.deactivate') : __('ai.activate') }}">
                                            @if($cerebrasKey->is_active)
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                                </svg>
                                            @endif
                                        </button>
                                    </form>

                                    <form action="{{ route('ai.setup.destroy', $cerebrasKey) }}" method="POST"
                                          onsubmit="return confirm('{{ __('ai.delete_key_confirm') }}');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center px-3 py-2 text-sm text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition"
                                                title="@lang('ai.delete')">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('ai.setup.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="type" value="cerebras">
                        <div class="mb-4">
                            <label for="cerebras_api_key" class="block text-sm font-medium text-gray-700 mb-2">Cerebras API Key</label>
                            <input
                                type="password"
                                id="cerebras_api_key"
                                name="api_key"
                                value="{{ old('api_key') }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-transparent transition font-mono text-sm"
                                placeholder="csk-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
                                required
                            >
                            <p class="mt-2 text-xs text-gray-500">@lang('ai.cerebras_help')</p>
                        </div>
                        <button type="submit" class="inline-flex items-center px-6 py-3 bg-teal-600 text-white rounded-xl font-medium hover:bg-teal-700 transition shadow-lg shadow-teal-200">
                            {{ $cerebrasKey ? __('ai.update_btn') : __('ai.add_key') }}
                        </button>
                    </form>
                </div>

                {{-- Gemini Key --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <div class="flex items-center mb-6">
                        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mr-4">
                            <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">Gemini API Key (Fallback)</h2>
                            <p class="text-sm text-gray-500">@lang('ai.gemini_desc')</p>
                        </div>
                    </div>

                    @if($geminiKey)
                        <div class="border border-gray-200 rounded-xl p-4 mb-4 {{ $geminiKey->is_active ? 'bg-white' : 'bg-gray-50 opacity-60' }}">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="w-10 h-10 {{ $geminiKey->is_active ? 'bg-blue-100' : 'bg-gray-200' }} rounded-full flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5 {{ $geminiKey->is_active ? 'text-blue-600' : 'text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="flex items-center space-x-2">
                                            <span class="font-medium text-gray-900">Gemini Key</span>
                                            @if($geminiKey->is_active)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">@lang('common.active')</span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">@lang('common.inactive')</span>
                                            @endif
                                        </div>
                                        <p class="text-sm text-gray-500 font-mono mt-0.5">
                                            {{ substr($geminiKey->api_key, 0, 8) }}...{{ substr($geminiKey->api_key, -4) }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('ai.setup.test', $geminiKey) }}"
                                       class="inline-flex items-center px-3 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition"
                                       title="@lang('ai.test')">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                        </svg>
                                    </a>

                                    <form action="{{ route('ai.setup.toggle', $geminiKey) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit"
                                                class="inline-flex items-center px-3 py-2 text-sm {{ $geminiKey->is_active ? 'text-green-600 hover:text-yellow-600' : 'text-gray-400 hover:text-green-600' }} hover:bg-gray-50 rounded-lg transition"
                                                title="{{ $geminiKey->is_active ? __('ai.deactivate') : __('ai.activate') }}">
                                            @if($geminiKey->is_active)
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                                </svg>
                                            @endif
                                        </button>
                                    </form>

                                    <form action="{{ route('ai.setup.destroy', $geminiKey) }}" method="POST"
                                          onsubmit="return confirm('{{ __('ai.delete_key_confirm') }}');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center px-3 py-2 text-sm text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition"
                                                title="@lang('ai.delete')">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('ai.setup.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="type" value="image">
                        <div class="mb-4">
                            <label for="gemini_api_key" class="block text-sm font-medium text-gray-700 mb-2">Gemini API Key</label>
                            <input
                                type="password"
                                id="gemini_api_key"
                                name="api_key"
                                value="{{ old('api_key') }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition font-mono text-sm"
                                placeholder="AIzaSyxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
                                required
                            >
                            <p class="mt-2 text-xs text-gray-500">@lang('ai.gemini_help')</p>
                        </div>
                        <button type="submit" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-xl font-medium hover:bg-blue-700 transition shadow-lg shadow-blue-200">
                            {{ $geminiKey ? __('ai.update_btn') : __('ai.add_key') }}
                        </button>
                    </form>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Status Card --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">@lang('ai.status_title')</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500">Groq (Primary)</span>
                            @if($groqKey && $groqKey->is_active)
                                <span class="text-sm font-medium text-green-600">@lang('common.active')</span>
                            @else
                                <span class="text-sm font-medium text-yellow-600">@lang('ai.not_set')</span>
                            @endif
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500">Cerebras (Secondary)</span>
                            @if($cerebrasKey && $cerebrasKey->is_active)
                                <span class="text-sm font-medium text-green-600">@lang('common.active')</span>
                            @else
                                <span class="text-sm font-medium text-yellow-600">@lang('ai.not_set')</span>
                            @endif
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500">Gemini (Fallback)</span>
                            @if($geminiKey && $geminiKey->is_active)
                                <span class="text-sm font-medium text-green-600">@lang('common.active')</span>
                            @else
                                <span class="text-sm font-medium text-yellow-600">@lang('ai.not_set')</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- API Key Guide --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">@lang('ai.api_key_guide')</h3>
                    <div class="space-y-5">
                        {{-- Groq --}}
                        <div>
                            <div class="flex items-center mb-2">
                                <div class="w-5 h-5 bg-purple-100 rounded-full flex items-center justify-center mr-2">
                                    <span class="text-xs font-bold text-purple-600">১</span>
                                </div>
                                <span class="text-sm font-bold text-gray-900">Groq (Primary)</span>
                            </div>
                            <ol class="ml-7 space-y-1 text-xs text-gray-600 list-decimal">
                                <li><a href="https://console.groq.com" target="_blank" class="text-purple-600 hover:underline font-medium">console.groq.com</a> @lang('ai.go_to_site')</li>
                                <li>@lang('ai.signup_or_login')</li>
                                <li>@lang('ai.right_tab')</li>
                                <li>@lang('ai.create_api_key')</li>
                                <li>@lang('ai.copy_paste')</li>
                            </ol>
                        </div>

                        {{-- Cerebras --}}
                        <div>
                            <div class="flex items-center mb-2">
                                <div class="w-5 h-5 bg-teal-100 rounded-full flex items-center justify-center mr-2">
                                    <span class="text-xs font-bold text-teal-600">২</span>
                                </div>
                                <span class="text-sm font-bold text-gray-900">Cerebras (Secondary)</span>
                            </div>
                            <ol class="ml-7 space-y-1 text-xs text-gray-600 list-decimal">
                                <li><a href="https://cloud.cerebras.ai" target="_blank" class="text-teal-600 hover:underline font-medium">cloud.cerebras.ai</a> @lang('ai.go_to_site')</li>
                                <li>@lang('ai.signup_or_login')</li>
                                <li>@lang('ai.right_tab')</li>
                                <li>@lang('ai.create_api_key')</li>
                                <li>@lang('ai.copy_paste')</li>
                            </ol>
                        </div>

                        {{-- Gemini --}}
                        <div>
                            <div class="flex items-center mb-2">
                                <div class="w-5 h-5 bg-blue-100 rounded-full flex items-center justify-center mr-2">
                                    <span class="text-xs font-bold text-blue-600">৩</span>
                                </div>
                                <span class="text-sm font-bold text-gray-900">Gemini (Fallback)</span>
                            </div>
                            <ol class="ml-7 space-y-1 text-xs text-gray-600 list-decimal">
                                <li><a href="https://aistudio.google.com/apikey" target="_blank" class="text-blue-600 hover:underline font-medium">aistudio.google.com/apikey</a> @lang('ai.go_to_site')</li>
                                <li>@lang('ai.google_login')</li>
                                <li>@lang('ai.create_api_key')</li>
                                <li>@lang('ai.copy_paste')</li>
                            </ol>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
