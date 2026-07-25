@props([
    'name' => '',
    'options' => [],
    'selected' => null,
    'placeholder' => __('common.select'),
    'label' => '',
    'required' => false,
    'error' => null,
])

<div
    x-data="{
        open: false,
        search: '',
        selectedId: '{{ $selected }}',
        selectedText: '',
        options: {{ json_encode(array_map(fn($opt) => ['value' => $opt['value'] ?? $opt['id'] ?? '', 'label' => $opt['label'] ?? $opt['name'] ?? $opt], $options)) }},
        init() {
            const opt = this.options.find(o => String(o.value) === String(this.selectedId));
            if (opt) this.selectedText = opt.label;
        },
        get filtered() {
            return this.options.filter(o =>
                o.label.toLowerCase().includes(this.search.toLowerCase())
            );
        },
        select(opt) {
            this.selectedId = opt.value;
            this.selectedText = opt.label;
            this.search = '';
            this.open = false;
            $refs.hidden.value = opt.value;
            $refs.hidden.dispatchEvent(new Event('change', { bubbles: true }));
        },
        clear() {
            this.selectedId = '';
            this.selectedText = '';
            $refs.hidden.value = '';
            $refs.hidden.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }"
    class="relative"
>
    @if($label)
        <label class="block text-sm font-medium text-gray-700 mb-1">
            {{ $label }}
            @if($required) <span class="text-red-500">*</span> @endif
        </label>
    @endif

    <button type="button" @click="open = !open"
        class="w-full px-4 py-3 border border-gray-300 rounded-xl text-left flex items-center justify-between focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
        :class="open ? 'ring-2 ring-purple-500 border-transparent' : ''"
    >
        <span x-text="selectedText || '{{ $placeholder }}'" :class="selectedText ? 'text-gray-900' : 'text-gray-400'" class="truncate"></span>
        <svg class="w-4 h-4 text-gray-400 shrink-0 transition" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <div x-show="open" @click.outside="open = false; search = ''"
        class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-lg overflow-hidden"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
    >
        <div class="p-2 border-b border-gray-100">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" x-model="search" placeholder="@lang('common.search')..."
                    class="w-full pl-9 pr-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                    @keydown.escape="open = false"
                >
            </div>
        </div>
        <div class="overflow-y-auto max-h-48">
            <template x-for="opt in filtered" :key="opt.value">
                <button type="button" @click="select(opt)"
                    class="w-full px-4 py-2.5 text-left text-sm transition flex items-center gap-2"
                    :class="String(opt.value) === String(selectedId) ? 'bg-purple-50 text-purple-700 font-medium' : 'text-gray-700 hover:bg-purple-50 hover:text-purple-700'"
                    x-text="opt.label">
                </button>
            </template>
            <div x-show="filtered.length === 0" class="px-4 py-6 text-sm text-gray-400 text-center">
                @lang('common.no_data')
            </div>
        </div>
    </div>

    <input type="hidden" name="{{ $name }}" x-ref="hidden" :value="selectedId" {{ $required ? 'required' : '' }}>
    @if($error)
        <p class="text-red-500 text-xs mt-1">{{ $error }}</p>
    @endif
</div>
