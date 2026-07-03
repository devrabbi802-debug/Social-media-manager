{{-- Message AI Tab --}}
<div class="bg-white rounded-2xl p-6 shadow-sm">
    <div class="flex items-center mb-6">
        <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mr-4">
            <svg class="w-7 h-7 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
        </div>
        <div>
            <h2 class="text-lg font-bold text-gray-900">নতুন Message AI Key যোগ করুন</h2>
            <p class="text-sm text-gray-500">Groq API Key যোগ করুন — একাধিক Key যোগ করতে পারবেন</p>
        </div>
    </div>

    <form action="{{ route('ai.setup.store') }}" method="POST">
        @csrf
        <input type="hidden" name="type" value="message">
        <div class="mb-4">
            <label for="message_api_key" class="block text-sm font-medium text-gray-700 mb-2">API Key *</label>
            <input
                type="password"
                id="message_api_key"
                name="api_key"
                value="{{ old('api_key') }}"
                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition font-mono text-sm"
                placeholder="gsk_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
                required
            >
        </div>
        <button type="submit" class="inline-flex items-center px-6 py-3 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition shadow-lg shadow-purple-200">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Key যোগ করুন
        </button>
    </form>
</div>

@if($messageKeys->isNotEmpty())
    <div class="bg-white rounded-2xl p-6 shadow-sm">
        <div class="flex items-center mb-6">
            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mr-4">
                <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-bold text-gray-900">Message AI Keys ({{ $messageKeys->count() }})</h2>
                <p class="text-sm text-gray-500">অগ্রাধিকার অনুযায়ী সাজানো — উপরের Key আগে ব্যবহার হবে</p>
            </div>
        </div>

        <div class="space-y-3">
            @foreach($messageKeys as $index => $setting)
                <div class="border border-gray-200 rounded-xl p-4 {{ $setting->is_active ? 'bg-white' : 'bg-gray-50 opacity-60' }}">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="w-10 h-10 {{ $setting->is_active ? 'bg-purple-100' : 'bg-gray-200' }} rounded-full flex items-center justify-center flex-shrink-0">
                                <span class="text-sm font-bold {{ $setting->is_active ? 'text-purple-600' : 'text-gray-500' }}">#{{ $index + 1 }}</span>
                            </div>
                            <div>
                                <div class="flex items-center space-x-2">
                                    <span class="font-medium text-gray-900">Key #{{ $index + 1 }}</span>
                                    @if($setting->is_active)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">সক্রিয়</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">নিষ্ক্রিয়</span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-500 font-mono mt-0.5">
                                    {{ substr($setting->api_key, 0, 8) }}...{{ substr($setting->api_key, -4) }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-2">
                            <a href="{{ route('ai.setup.test', $setting) }}"
                               class="inline-flex items-center px-3 py-2 text-sm text-gray-600 hover:text-purple-600 hover:bg-purple-50 rounded-lg transition"
                               title="টেস্ট করুন">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </a>

                            <form action="{{ route('ai.setup.toggle', $setting) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit"
                                        class="inline-flex items-center px-3 py-2 text-sm {{ $setting->is_active ? 'text-green-600 hover:text-yellow-600' : 'text-gray-400 hover:text-green-600' }} hover:bg-gray-50 rounded-lg transition"
                                        title="{{ $setting->is_active ? 'নিষ্ক্রিয় করুন' : 'সক্রিয় করুন' }}">
                                    @if($setting->is_active)
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

                            <form action="{{ route('ai.setup.destroy', $setting) }}" method="POST"
                                  onsubmit="return confirm('আপনি কি নিশ্চিত এই Key মুছে ফেলতে চান?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="inline-flex items-center px-3 py-2 text-sm text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition"
                                        title="মুছে ফেলুন">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4 p-4 bg-blue-50 rounded-xl border border-blue-100">
            <p class="text-sm text-blue-700">
                <strong>কিভাবে কাজ করে:</strong> AI সিস্টেম প্রথম Key দিয়ে শুরু করবে। যদি কোনো Key এর দৈনিক লিমিট শেষ হয় (429 error), তাহলে স্বয়ংক্রিয়ভাবে পরবর্তী Key ব্যবহার করবে।
            </p>
        </div>
    </div>
@endif
