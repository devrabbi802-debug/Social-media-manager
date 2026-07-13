@php
    $activePage = $activePage ?? 'dashboard';
@endphp

<div class="mb-8 border-b border-gray-200">
    <nav class="flex space-x-8">
        <a href="{{ route('dashboard') }}" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition {{ $activePage === 'dashboard' ? 'border-purple-600 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
            @lang('nav.dashboard')
        </a>
        <a href="{{ route('integration') }}" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition {{ $activePage === 'integration' ? 'border-purple-600 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
            @lang('nav.social_media_integration')
        </a>
        <a href="{{ route('ai.setup') }}" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition {{ $activePage === 'ai.setup' ? 'border-purple-600 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
            @lang('nav.ai_setup')
        </a>
        <a href="{{ route('conversations') }}" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition {{ $activePage === 'conversations' ? 'border-purple-600 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
            @lang('nav.conversations')
        </a>
        <a href="{{ route('inventory.index') }}" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition {{ $activePage === 'inventory' ? 'border-purple-600 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
            @lang('nav.inventory')
        </a>
    </nav>
</div>
