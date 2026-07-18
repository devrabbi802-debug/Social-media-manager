@extends('layouts.tenant')

@section('title', __('facebook.settings_title').' - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    {{-- Dashboard Header --}}
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">@lang('facebook.settings_title')</h1>
                    <p class="text-gray-600">@lang('facebook.settings_subtitle')</p>
                </div>
                <div class="flex items-center space-x-4">
                    @if($facebookSetting)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                            @lang('common.connected')
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
        @include('tenant.partials._nav-tabs', ['activePage' => 'integration'])

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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Main Form --}}
            <div class="lg:col-span-2">
                @if($facebookSetting && $facebookSetting->isZernio())
                    {{-- Zernio Connected State --}}
                    <div class="bg-white rounded-2xl p-6 shadow-sm">
                        <div class="flex items-center mb-6">
                            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mr-4">
                                <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900">@lang('facebook.zernio_connected')</h2>
                                <p class="text-sm text-gray-500">@lang('facebook.zernio_desc')</p>
                            </div>
                        </div>

                        <div class="bg-gray-50 rounded-xl p-4 mb-6">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 uppercase">Page Name</p>
                                    <p class="text-sm text-gray-900 mt-1">{{ $facebookSetting->page_name ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 uppercase">Page ID</p>
                                    <p class="text-sm text-gray-900 mt-1">{{ $facebookSetting->page_id }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 uppercase">Zernio Account ID</p>
                                    <p class="text-sm text-gray-900 mt-1 font-mono text-xs">{{ $facebookSetting->zernio_account_id }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 uppercase">Status</p>
                                    <p class="text-sm text-green-600 mt-1 font-medium">@lang('common.connected')</p>
                                </div>
                            </div>
                        </div>

                        {{-- AI Auto-Reply Toggle --}}
                        <div class="bg-gradient-to-r from-purple-50 to-blue-50 rounded-xl p-6 mb-6 border border-purple-100">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="w-12 h-12 {{ $facebookSetting->ai_auto_reply_enabled ? 'bg-green-100' : 'bg-gray-200' }} rounded-xl flex items-center justify-center mr-4">
                                        <svg class="w-7 h-7 {{ $facebookSetting->ai_auto_reply_enabled ? 'text-green-600' : 'text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-900">@lang('facebook.ai_auto_reply')</h3>
                                        <p class="text-sm text-gray-500">
                                            @if($facebookSetting->ai_auto_reply_enabled)
                                                <span class="text-green-600 font-medium">@lang('facebook.active')</span> — @lang('facebook.active_desc')
                                            @else
                                                <span class="text-gray-500 font-medium">@lang('facebook.inactive')</span> — @lang('facebook.inactive_desc')
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                <form action="{{ route('facebook.settings.toggle.ai.reply') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="relative inline-flex h-12 w-24 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 {{ $facebookSetting->ai_auto_reply_enabled ? 'bg-green-500' : 'bg-gray-300' }}" role="switch" aria-checked="{{ $facebookSetting->ai_auto_reply_enabled ? 'true' : 'false' }}">
                                        <span class="sr-only">@lang('facebook.toggle_sr')</span>
                                        <span aria-hidden="true" class="pointer-events-none inline-block h-10 w-10 transform rounded-full bg-white shadow-lg ring-0 transition duration-200 ease-in-out {{ $facebookSetting->ai_auto_reply_enabled ? 'translate-x-12' : 'translate-x-1' }}"></span>
                                    </button>
                                </form>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="flex items-center space-x-4">
                            <a href="{{ route('zernio.connect.facebook') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition">
                                <svg class="w-5 h-5 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388,10.954,10.125,11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007,1.792-4.669,4.533-4.669 1.312,0,2.686.235,2.686.235v2.953H15.83c-1.491,0-1.956.925-1.956,1.874v2.25h3.328l-.532,3.47h-2.796v8.385C19.612,23.027,24,18.062,24,12.073z"/>
                                </svg>
                                @lang('facebook.change_facebook')
                            </a>
                        </div>
                    </div>

                @elseif($facebookSetting && $facebookSetting->isFacebookApp())
                    {{-- Facebook App Connected State (old flow) --}}
                    <div class="bg-white rounded-2xl p-6 shadow-sm">
                        <div class="flex items-center mb-6">
                            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mr-4">
                                <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900">@lang('facebook.facebook_app_connected')</h2>
                                <p class="text-sm text-gray-500">@lang('facebook.connected_via_app')</p>
                            </div>
                        </div>

                        <div class="bg-gray-50 rounded-xl p-4 mb-6">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 uppercase">Page ID</p>
                                    <p class="text-sm text-gray-900 mt-1">{{ $facebookSetting->page_id }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 uppercase">App ID</p>
                                    <p class="text-sm text-gray-900 mt-1">{{ $facebookSetting->app_id }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- AI Auto-Reply Toggle --}}
                        <div class="bg-gradient-to-r from-purple-50 to-blue-50 rounded-xl p-6 mb-6 border border-purple-100">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="w-12 h-12 {{ $facebookSetting->ai_auto_reply_enabled ? 'bg-green-100' : 'bg-gray-200' }} rounded-xl flex items-center justify-center mr-4">
                                        <svg class="w-7 h-7 {{ $facebookSetting->ai_auto_reply_enabled ? 'text-green-600' : 'text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-900">@lang('facebook.ai_auto_reply')</h3>
                                        <p class="text-sm text-gray-500">
                                            @if($facebookSetting->ai_auto_reply_enabled)
                                                <span class="text-green-600 font-medium">@lang('facebook.active')</span>
                                            @else
                                                <span class="text-gray-500 font-medium">@lang('facebook.inactive')</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <form action="{{ route('facebook.settings.toggle.ai.reply') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="relative inline-flex h-12 w-24 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 {{ $facebookSetting->ai_auto_reply_enabled ? 'bg-green-500' : 'bg-gray-300' }}" role="switch" aria-checked="{{ $facebookSetting->ai_auto_reply_enabled ? 'true' : 'false' }}">
                                        <span class="sr-only">@lang('facebook.toggle_sr')</span>
                                        <span aria-hidden="true" class="pointer-events-none inline-block h-10 w-10 transform rounded-full bg-white shadow-lg ring-0 transition duration-200 ease-in-out {{ $facebookSetting->ai_auto_reply_enabled ? 'translate-x-12' : 'translate-x-1' }}"></span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                @else
                    {{-- Not Connected State — Two Options --}}
                    <div class="space-y-6">
                        {{-- Option 1: Zernio (Recommended) --}}
                        <div class="bg-white rounded-2xl p-8 shadow-sm border-2 border-purple-200">
                            <div class="flex items-center mb-4">
                                <span class="bg-purple-100 text-purple-800 text-xs font-bold px-3 py-1 rounded-full mr-3">@lang('facebook.recommended')</span>
                            </div>
                            <div class="flex items-center mb-6">
                                <div class="w-14 h-14 bg-purple-100 rounded-2xl flex items-center justify-center mr-4">
                                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-xl font-bold text-gray-900">@lang('facebook.connect_via_zernio')</h2>
                                    <p class="text-gray-500">@lang('facebook.zernio_desc_short')</p>
                                </div>
                            </div>

                            <form action="{{ route('zernio.store.apikey') }}" method="POST" class="mb-4">
                                @csrf
                                <div class="mb-4">
                                    <label for="zernio_api_key" class="block text-sm font-medium text-gray-700 mb-2">Zernio API Key</label>
                                    <input
                                        type="text"
                                        name="zernio_api_key"
                                        id="zernio_api_key"
                                        placeholder="sk_xxxxxxxx..."
                                        required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition font-mono text-sm"
                                    >
                                    <p class="mt-2 text-xs text-gray-500">
                                        @lang('facebook.zernio_signup_help')
                                        @lang('facebook.first_two_free')
                                    </p>
                                </div>
                                <button type="submit" class="w-full inline-flex items-center justify-center px-8 py-4 bg-purple-600 text-white rounded-xl font-bold text-lg hover:bg-purple-700 transition shadow-lg shadow-purple-200">
                                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                    @lang('facebook.connect_zernio_btn')
                                </button>
                            </form>

                            <div class="bg-purple-50 rounded-xl p-4 mt-4">
                                <h4 class="text-sm font-bold text-purple-900 mb-2">@lang('facebook.why_zernio')</h4>
                                <ul class="text-xs text-purple-700 space-y-1">
                                    <li>✅ @lang('facebook.no_fb_app_needed')</li>
                                    <li>✅ @lang('facebook.one_api_key')</li>
                                    <li>✅ @lang('facebook.platform_support') (Facebook, Instagram, TikTok...)</li>
                                    <li>✅ @lang('facebook.first_two_free_list')</li>
                                </ul>
                            </div>
                        </div>

                        {{-- Option 2: Facebook App (Direct) --}}
                        <div class="bg-white rounded-2xl p-8 shadow-sm">
                            <div class="flex items-center mb-6">
                                <div class="w-14 h-14 bg-blue-100 rounded-2xl flex items-center justify-center mr-4">
                                    <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388,10.954,10.125,11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007,1.792-4.669,4.533-4.669 1.312,0,2.686.235,2.686.235v2.953H15.83c-1.491,0-1.956.925-1.956,1.874v2.25h3.328l-.532,3.47h-2.796v8.385C19.612,23.027,24,18.062,24,12.073z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-xl font-bold text-gray-900">@lang('facebook.connect_via_app')</h2>
                                    <p class="text-gray-500">@lang('facebook.use_own_app')</p>
                                </div>
                            </div>

                            <a href="{{ route('facebook.redirect') }}" class="w-full inline-flex items-center justify-center px-8 py-4 bg-blue-600 text-white rounded-xl font-bold text-lg hover:bg-blue-700 transition shadow-lg shadow-blue-200">
                                <svg class="w-6 h-6 mr-3" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388,10.954,10.125,11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007,1.792-4.669,4.533-4.669 1.312,0,2.686.235,2.686.235v2.953H15.83c-1.491,0-1.956.925-1.956,1.874v2.25h3.328l-.532,3.47h-2.796v8.385C19.612,23.027,24,18.062,24,12.073z"/>
                                </svg>
                                @lang('facebook.connect_fb_btn')
                            </a>

                            <p class="text-xs text-gray-400 mt-4 text-center">@lang('facebook.secure_login')</p>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                @if($facebookSetting && $facebookSetting->isZernio())
                    {{-- Zernio Webhook Info --}}
                    <div class="bg-white rounded-2xl p-6 shadow-sm">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">@lang('facebook.zernio_webhook')</h3>
                        <div class="space-y-4">
                            <div class="bg-gray-50 rounded-xl p-4">
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Webhook URL</p>
                                <code class="text-sm text-purple-600 break-all">{{ url('/webhook/zernio') }}</code>
                            </div>
                            <div class="bg-purple-50 rounded-xl p-4">
                                <p class="text-xs text-purple-700">
                                    @lang('facebook.webhook_url_help')
                                    <code class="font-mono text-xs">message.received</code> @lang('facebook.subscribe_event')
                                </p>
                            </div>

                            {{-- Test Webhook Button --}}
                            <button type="button" id="testWebhookBtn"
                               class="w-full inline-flex items-center justify-center px-4 py-3 bg-green-600 text-white rounded-xl font-medium hover:bg-green-700 transition shadow-sm">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                @lang('facebook.test_webhook')
                            </button>
                            <div id="testWebhookResult" class="text-sm text-center mt-2 hidden"></div>
                            <p class="text-xs text-gray-400 text-center">@lang('facebook.webhook_test_desc')</p>
                        </div>
                    </div>
                @elseif($facebookSetting && $facebookSetting->isFacebookApp())
                    {{-- Facebook App Webhook Info --}}
                    <div class="bg-white rounded-2xl p-6 shadow-sm">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">@lang('facebook.webhook_setup')</h3>
                        <div class="space-y-4">
                            <div class="bg-gray-50 rounded-xl p-4">
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Callback URL</p>
                                <code class="text-sm text-blue-600 break-all">{{ url('/webhook/facebook') }}</code>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-4">
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Verify Token</p>
                                <code class="text-sm text-blue-600">{{ $facebookSetting->verify_token ?? 'socialboost_verify_token_2026' }}</code>
                            </div>
                        </div>
                    </div>
                @else
                    {{-- Instructions --}}
                    <div class="bg-white rounded-2xl p-6 shadow-sm">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">@lang('facebook.how_to_get')</h3>
                        <div class="space-y-4">
                            <div class="flex items-start">
                                <div class="w-6 h-6 bg-purple-100 rounded-full flex items-center justify-center mr-3 mt-0.5">
                                    <span class="text-xs font-bold text-purple-600">১</span>
                                </div>
                                <p class="text-sm text-gray-600"><a href="https://zernio.com/signup" target="_blank" class="text-purple-600 hover:underline">Zernio.com</a> @lang('facebook.step1')</p>
                            </div>
                            <div class="flex items-start">
                                <div class="w-6 h-6 bg-purple-100 rounded-full flex items-center justify-center mr-3 mt-0.5">
                                    <span class="text-xs font-bold text-purple-600">২</span>
                                </div>
                                <p class="text-sm text-gray-600">@lang('facebook.step2')</p>
                            </div>
                            <div class="flex items-start">
                                <div class="w-6 h-6 bg-purple-100 rounded-full flex items-center justify-center mr-3 mt-0.5">
                                    <span class="text-xs font-bold text-purple-600">৩</span>
                                </div>
                                <p class="text-sm text-gray-600">@lang('facebook.step3')</p>
                            </div>
                            <div class="flex items-start">
                                <div class="w-6 h-6 bg-purple-100 rounded-full flex items-center justify-center mr-3 mt-0.5">
                                    <span class="text-xs font-bold text-purple-600">৪</span>
                                </div>
                                <p class="text-sm text-gray-600">@lang('facebook.step4')</p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Danger Zone --}}
                @if($facebookSetting)
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-red-200">
                        <h3 class="text-lg font-bold text-red-600 mb-2">@lang('facebook.danger_zone')</h3>
                        <p class="text-sm text-gray-600 mb-4">@lang('facebook.danger_desc')</p>
                        @if($facebookSetting->isZernio())
                            <form action="{{ route('zernio.disconnect') }}" method="POST" onsubmit="return confirm('{{ __('facebook.disconnect_zernio_confirm') }}')">
                                @csrf
                                <button type="submit" class="w-full bg-red-600 text-white px-4 py-2 rounded-xl font-medium hover:bg-red-700 transition">
                                    @lang('facebook.disconnect')
                                </button>
                            </form>
                        @else
                            <form action="{{ route('facebook.settings.destroy') }}" method="POST" onsubmit="return confirm('{{ __('facebook.delete_settings_confirm') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full bg-red-600 text-white px-4 py-2 rounded-xl font-medium hover:bg-red-700 transition">
                                    @lang('facebook.delete_settings')
                                </button>
                            </form>
                        @endif
                    </div>
                @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('testWebhookBtn')?.addEventListener('click', function() {
    const btn = this;
    const resultDiv = document.getElementById('testWebhookResult');
    btn.disabled = true;
    btn.innerHTML = '<svg class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> @lang('facebook.sending')';
    resultDiv.classList.add('hidden');
    fetch('/facebook/zernio/test-webhook', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
    })
    .then(r => r.json())
    .then(data => {
        resultDiv.classList.remove('hidden');
        resultDiv.className = 'text-sm text-center mt-2 ' + (data.success ? 'text-green-600' : 'text-red-600');
        resultDiv.textContent = data.message;
        btn.innerHTML = '@lang('facebook.test_webhook')';
        btn.disabled = false;
    })
    .catch(() => {
        resultDiv.classList.remove('hidden');
        resultDiv.className = 'text-sm text-center mt-2 text-red-600';
        resultDiv.textContent = 'Network error';
        btn.innerHTML = '@lang('facebook.test_webhook')';
        btn.disabled = false;
    });
});
</script>
@endpush
@endsection
