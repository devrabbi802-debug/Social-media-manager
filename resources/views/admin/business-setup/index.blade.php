@extends('admin.layouts.app')

@section('title', 'Business Setup - Admin')

@section('content')
<div class="max-w-4xl space-y-6">
    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Business Setup</h1>
        <p class="text-gray-500 mt-1">প্ল্যাটফর্মের বিজনেস তথ্য ও যোগাযোগ সেটআপ।</p>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-sm">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Form Card --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-base font-bold text-gray-900">বিজনেস তথ্য</h2>
        </div>

        <form action="{{ route('admin.business-setup.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="p-6 space-y-6">

                {{-- Business Logo --}}
                <div x-data="{ logoPreview: '{{ $setup->getLogoUrl() ?? '' }}', fileName: '' }">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Business Logo</label>

                    @if($setup->logo_path)
                        <div class="mb-4">
                            <p class="text-xs text-gray-400 mb-2">বর্তমান লোগো:</p>
                            <img src="{{ $setup->getLogoUrl() }}" alt="Current Logo" class="h-20 rounded-xl object-contain border border-gray-200">
                        </div>
                    @endif

                    <div class="relative">
                        <label for="logoUpload" class="flex items-center gap-3 w-full px-4 py-3 border-2 border-dashed border-gray-300 rounded-xl hover:border-emerald-400 hover:bg-emerald-50/50 transition cursor-pointer">
                            <svg class="w-8 h-8 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-700" x-text="fileName || 'ফাইল নির্বাচন করুন'"></p>
                                <p class="text-xs text-gray-400">PNG, JPG, SVG (সর্বোচ্চ ২MB)</p>
                            </div>
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </label>
                        <input type="file" id="logoUpload" name="logo" accept="image/*" class="hidden"
                               @change="fileName = $event.target.files[0]?.name || ''; logoPreview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : logoPreview">
                    </div>
                    @if($setup->logo_path)
                        <p class="text-xs text-gray-400 mt-2">নতুন লোগো আপলোড করলে পুরানোটি রিপ্লেস হবে।</p>
                    @endif
                </div>

                {{-- Support Number --}}
                <div>
                    <label for="support_number" class="block text-sm font-medium text-gray-700 mb-1">Support Number</label>
                    <input
                        type="text"
                        id="support_number"
                        name="support_number"
                        value="{{ old('support_number', $setup->support_number) }}"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors text-sm"
                        placeholder="যেমন: +880 1XXX-XXXXXX"
                    >
                    @error('support_number')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Support Email --}}
                <div>
                    <label for="support_email" class="block text-sm font-medium text-gray-700 mb-1">Support Email</label>
                    <input
                        type="email"
                        id="support_email"
                        name="support_email"
                        value="{{ old('support_email', $setup->support_email) }}"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors text-sm"
                        placeholder="যেমন: support@example.com"
                    >
                    @error('support_email')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            {{-- Submit --}}
            <div class="px-6 pb-6">
                <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-emerald-600 text-white rounded-lg font-medium hover:bg-emerald-700 transition-all duration-200 text-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    সংরক্ষণ করুন
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
