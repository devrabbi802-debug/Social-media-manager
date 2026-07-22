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
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700 flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
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

        </nav>
    </div>

    {{-- Theme Selection Tab --}}
    <div x-show="activeTab === 'theme'" x-cloak>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Choose Theme</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($themes as $slug => $theme)
                    <div class="relative border-2 rounded-xl p-4 cursor-pointer transition hover:shadow-md {{ $storefront->theme_slug === $slug ? 'border-purple-500 bg-purple-50' : 'border-gray-200' }}" onclick="document.getElementById('theme-{{ $slug }}').submit()">
                        <div class="aspect-video bg-gray-100 rounded-lg mb-3 flex items-center justify-center overflow-hidden cursor-pointer" @click.stop="openLightbox('{{ asset($theme['thumbnail']) }}')">
                            <img src="{{ asset($theme['thumbnail']) }}" alt="{{ $theme['name'] }}" class="w-full h-full object-contain">
                        </div>
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ $theme['name'] }}</h3>
                                <p class="text-sm text-gray-500">{{ $slug === 'clothing-fashion' ? 'Dark header, shadow cards, rounded buttons' : 'Clean white, border cards, sharp corners' }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                @if($storefront->theme_slug === $slug)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        Active
                                    </span>
                                @endif
                                <a href="/?editor=true&theme={{ $slug }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-900 text-white text-xs font-medium rounded-lg hover:bg-gray-800 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    Customize
                                </a>
                            </div>
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

    {{-- Image Lightbox --}}
    <div x-show="lightboxOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4" @click="lightboxOpen = false">
        <div class="relative max-w-4xl max-h-[90vh] w-full" @click.stop>
            <img :src="lightboxSrc" alt="Theme preview" class="w-full h-full object-contain max-h-[90vh] rounded-lg">
            <button @click="lightboxOpen = false" class="absolute -top-3 -right-3 w-8 h-8 bg-white rounded-full shadow-lg flex items-center justify-center text-gray-700 hover:text-gray-900 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
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
                    <div x-data="fileUpload('logo-preview', '{{ $storefront->store_logo ? Storage::disk('public')->url($storefront->store_logo) : '' }}')">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Logo</label>
                        <div class="relative"
                             x-on:dragover.prevent="isDragging = true"
                             x-on:dragleave.prevent="isDragging = false"
                             x-on:drop.prevent="isDragging = false; handleDrop($event)"
                             :class="isDragging ? 'border-purple-500 bg-purple-50' : 'border-gray-300'"
                             class="border-2 border-dashed rounded-xl p-4 text-center cursor-pointer transition-all duration-200 hover:border-purple-400 hover:bg-purple-50/50"
                             @click="$refs.logoInput.click()">
                            <input type="file" name="logo" accept="image/*" x-ref="logoInput" class="hidden" onchange="previewFile(this, 'logo-preview')">
                            <template x-if="!previewUrl && !existingUrl">
                                <div>
                                    <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <p class="mt-1 text-sm text-gray-600"><span class="font-medium text-purple-600">Click to upload</span> or drag & drop</p>
                                    <p class="text-xs text-gray-400 mt-1">PNG, JPG, SVG (Max 2MB)</p>
                                </div>
                            </template>
                            <template x-if="previewUrl || existingUrl">
                                <div class="flex items-center justify-center gap-3">
                                    <img :src="previewUrl || existingUrl" alt="Logo preview" class="h-14 w-auto object-contain rounded-lg shadow-sm">
                                    <button type="button" @click.stop="clearFile('logo-input', 'logo-preview')" class="text-red-500 hover:text-red-700 p-1 rounded-full hover:bg-red-50 transition">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>
                    <div x-data="fileUpload('favicon-preview', '{{ $storefront->store_favicon ? Storage::disk('public')->url($storefront->store_favicon) : '' }}')">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Favicon</label>
                        <div class="relative"
                             x-on:dragover.prevent="isDragging = true"
                             x-on:dragleave.prevent="isDragging = false"
                             x-on:drop.prevent="isDragging = false; handleDrop($event)"
                             :class="isDragging ? 'border-purple-500 bg-purple-50' : 'border-gray-300'"
                             class="border-2 border-dashed rounded-xl p-4 text-center cursor-pointer transition-all duration-200 hover:border-purple-400 hover:bg-purple-50/50"
                             @click="$refs.faviconInput.click()">
                            <input type="file" name="favicon" accept="image/*" x-ref="faviconInput" class="hidden" onchange="previewFile(this, 'favicon-preview')">
                            <template x-if="!previewUrl && !existingUrl">
                                <div>
                                    <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <p class="mt-1 text-sm text-gray-600"><span class="font-medium text-purple-600">Click to upload</span> or drag & drop</p>
                                    <p class="text-xs text-gray-400 mt-1">PNG, ICO (Max 1MB)</p>
                                </div>
                            </template>
                            <template x-if="previewUrl || existingUrl">
                                <div class="flex items-center justify-center gap-3">
                                    <img :src="previewUrl || existingUrl" alt="Favicon preview" class="h-10 w-10 object-contain rounded-lg shadow-sm">
                                    <button type="button" @click.stop="clearFile('favicon-input', 'favicon-preview')" class="text-red-500 hover:text-red-700 p-1 rounded-full hover:bg-red-50 transition">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>
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
</div>

<script>
function storefrontSettings() {
    return {
        activeTab: '{{ request("tab", "theme") }}',
        lightboxSrc: null,
        lightboxOpen: false,
        openLightbox(src) {
            this.lightboxSrc = src;
            this.lightboxOpen = true;
        },
    }
}

function fileUpload(previewId, existing) {
    return {
        previewUrl: null,
        existingUrl: existing || null,
        isDragging: false,

        handleDrop(e) {
            const file = e.dataTransfer.files[0];
            if (file && file.type.startsWith('image/')) {
                this.showPreview(file);
                const input = this.$el.querySelector('input[type="file"]');
                const dt = new DataTransfer();
                dt.items.add(file);
                input.files = dt.files;
                input.dispatchEvent(new Event('change'));
            }
        },

        showPreview(file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                this.previewUrl = e.target.result;
                this.existingUrl = null;
            };
            reader.readAsDataURL(file);
        },

        clearFile(inputRef, previewId) {
            this.previewUrl = null;
            this.existingUrl = null;
            const input = this.$refs[inputRef];
            if (input) input.value = '';
        }
    }
}

function previewFile(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        const Alpine = window.Alpine;
        const component = Alpine.$data(input.closest('[x-data]'));
        if (component) {
            reader.onload = (e) => {
                component.previewUrl = e.target.result;
                component.existingUrl = null;
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
}
</script>
@endsection