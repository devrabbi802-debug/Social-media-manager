@extends('layouts.tenant')

@section('title', 'Storefront Settings - SocialBoost AI')

@section('content')
<div class="p-6" x-data="storefrontSettings()">
    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Storefront Settings</h1>
        <p class="text-gray-600 mt-1">Customize your online store appearance and settings</p>
    </div>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">
            {{ session('success') }}
        </div>
    @endif

    {{-- Tabs --}}
    <div class="border-b border-gray-200 mb-6">
        <nav class="flex space-x-8" aria-label="Tabs">
            <button @click="activeTab = 'theme'" :class="activeTab === 'theme' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition">
                Theme Selection
            </button>
            <button @click="activeTab = 'general'" :class="activeTab === 'general' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition">
                General Settings
            </button>
            <button @click="activeTab = 'banners'" :class="activeTab === 'banners' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition">
                Banner Management
            </button>
        </nav>
    </div>

    {{-- Theme Selection Tab --}}
    <div x-show="activeTab === 'theme'" x-cloak>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Choose Theme</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($themes as $slug => $theme)
                    <div class="relative border-2 rounded-xl p-4 cursor-pointer transition hover:shadow-md {{ $storefront->theme_slug === $slug ? 'border-purple-500 bg-purple-50' : 'border-gray-200' }}" onclick="document.getElementById('theme-{{ $slug }}').submit()">
                        <div class="aspect-video bg-gray-100 rounded-lg mb-3 flex items-center justify-center overflow-hidden">
                            <div class="w-full h-full p-4" style="background: {{ $theme['config']['colors']['primary'] }}">
                                <div class="bg-white rounded p-2 h-full flex items-center justify-center">
                                    <span class="text-sm font-medium" style="color: {{ $theme['config']['colors']['text'] }}">{{ $theme['name'] }} Theme</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ $theme['name'] }}</h3>
                                <p class="text-sm text-gray-500">{{ $slug === 'modern' ? 'Dark header, shadow cards, rounded buttons' : 'Clean white, border cards, sharp corners' }}</p>
                            </div>
                            @if($storefront->theme_slug === $slug)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    Active
                                </span>
                            @endif
                        </div>
                        <form id="theme-{{ $slug }}" method="POST" action="{{ route('storefront-settings.apply-theme') }}" class="hidden">
                            @csrf
                            <input type="hidden" name="theme_slug" value="{{ $slug }}">
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- General Settings Tab --}}
    <div x-show="activeTab === 'general'" x-cloak>
        <form method="POST" action="{{ route('storefront-settings.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Store Identity --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Store Identity</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Store Name</label>
                        <input type="text" name="store_name" value="{{ old('store_name', $storefront->store_name) }}" class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500" placeholder="My Store">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Logo</label>
                        <input type="file" name="logo" accept="image/*" class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                        @if($storefront->store_logo)
                            <p class="mt-1 text-sm text-gray-500">Current: {{ basename($storefront->store_logo) }}</p>
                        @endif
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Favicon</label>
                        <input type="file" name="favicon" accept="image/*" class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                        @if($storefront->store_favicon)
                            <p class="mt-1 text-sm text-gray-500">Current: {{ basename($storefront->store_favicon) }}</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Layout Options --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Layout Options</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Layout Style</label>
                        <select name="layout_style" class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                            <option value="grid" {{ $storefront->layout_style === 'grid' ? 'selected' : '' }}>Grid</option>
                            <option value="list" {{ $storefront->layout_style === 'list' ? 'selected' : '' }}>List</option>
                            <option value="masonry" {{ $storefront->layout_style === 'masonry' ? 'selected' : '' }}>Masonry</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Products Per Row (Desktop)</label>
                        <select name="products_per_row" class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                            @for($i = 2; $i <= 6; $i++)
                                <option value="{{ $i }}" {{ $storefront->products_per_row === $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Products Per Row (Mobile)</label>
                        <select name="products_per_row_mobile" class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                            @for($i = 1; $i <= 4; $i++)
                                <option value="{{ $i }}" {{ $storefront->products_per_row_mobile === $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
                <div class="mt-4 flex flex-wrap gap-4">
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" name="show_header_slider" value="1" {{ $storefront->show_header_slider ? 'checked' : '' }} class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                        <span class="text-sm text-gray-700">Show Hero Banner Slider</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" name="show_brands_section" value="1" {{ $storefront->show_brands_section ? 'checked' : '' }} class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                        <span class="text-sm text-gray-700">Show Brands Section</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" name="show_newsletter" value="1" {{ $storefront->show_newsletter ? 'checked' : '' }} class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                        <span class="text-sm text-gray-700">Show Newsletter Signup</span>
                    </label>
                </div>
            </div>

            {{-- Contact & Social --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Contact & Social</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                        <input type="text" name="contact_phone" value="{{ old('contact_phone', $storefront->contact_phone) }}" class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="contact_email" value="{{ old('contact_email', $storefront->contact_email) }}" class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                        <textarea name="contact_address" rows="2" class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">{{ old('contact_address', $storefront->contact_address) }}</textarea>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Facebook URL</label>
                        <input type="url" name="facebook_url" value="{{ old('facebook_url', $storefront->facebook_url) }}" class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Instagram URL</label>
                        <input type="url" name="instagram_url" value="{{ old('instagram_url', $storefront->instagram_url) }}" class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">YouTube URL</label>
                        <input type="url" name="youtube_url" value="{{ old('youtube_url', $storefront->youtube_url) }}" class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">WhatsApp Number</label>
                        <input type="text" name="whatsapp_number" value="{{ old('whatsapp_number', $storefront->whatsapp_number) }}" class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500" placeholder="+880...">
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Footer Settings</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">About Text</label>
                        <textarea name="footer_about_text" rows="3" class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">{{ old('footer_about_text', $storefront->footer_about_text) }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Copyright Text</label>
                        <input type="text" name="footer_copyright_text" value="{{ old('footer_copyright_text', $storefront->footer_copyright_text) }}" class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500" placeholder="© 2026 My Store. All rights reserved.">
                    </div>
                </div>
            </div>

            {{-- Custom CSS --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Custom CSS</h2>
                <textarea name="custom_css" rows="4" class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500 font-mono text-sm" placeholder="/* Add custom CSS here */">{{ old('custom_css', $storefront->custom_css) }}</textarea>
                <p class="mt-1 text-sm text-gray-500">Advanced: Add custom CSS to override theme styles</p>
            </div>

            {{-- Submit --}}
            <div class="flex justify-end">
                <button type="submit" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition font-medium">
                    Save Settings
                </button>
            </div>
        </form>
    </div>

    {{-- Banner Management Tab --}}
    <div x-show="activeTab === 'banners'" x-cloak>
        {{-- Add Banner Form --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Add New Banner</h2>
            <form method="POST" action="{{ route('storefront-settings.banners.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                        <input type="text" name="title" class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Subtitle</label>
                        <input type="text" name="subtitle" class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Image</label>
                        <input type="file" name="image" accept="image/*" required class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Link URL</label>
                        <input type="url" name="link" class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Button Text</label>
                        <input type="text" name="btn_text" class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                        <input type="number" name="sort_order" value="0" min="0" class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition font-medium text-sm">
                        Add Banner
                    </button>
                </div>
            </form>
        </div>

        {{-- Banner List --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Manage Banners</h2>
            @if($banners->isEmpty())
                <p class="text-gray-500 text-center py-8">No banners yet. Add your first banner above.</p>
            @else
                <div class="space-y-4" id="banners-list">
                    @foreach($banners as $banner)
                        <div class="border border-gray-200 rounded-lg p-4 flex items-center gap-4" data-banner-id="{{ $banner->id }}">
                            <div class="w-32 h-20 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                                @if($banner->image)
                                    <img src="{{ Storage::disk('public')->url($banner->image) }}" alt="{{ $banner->title }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-400">No Image</div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-medium text-gray-900">{{ $banner->title ?? 'Untitled' }}</h3>
                                <p class="text-sm text-gray-500 truncate">{{ $banner->subtitle }}</p>
                                <p class="text-xs text-gray-400 mt-1">Order: {{ $banner->sort_order }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <form method="POST" action="{{ route('storefront-settings.banners.destroy', $banner) }}" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 transition">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function storefrontSettings() {
    return {
        activeTab: '{{ request("tab", "theme") }}',
    }
}
</script>
@endsection