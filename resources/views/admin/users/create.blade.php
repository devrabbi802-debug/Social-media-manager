@extends('admin.layouts.app')

@section('title', 'Create User - Admin Panel')

@section('content')
<div class="max-w-4xl space-y-6">
    {{-- Back Link --}}
    <a href="{{ route('admin.users.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-emerald-600 transition-colors">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Back to User List
    </a>

    {{-- Form Card --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden" x-data="{ role: '{{ old('role', 'admin') }}' }">
        <div class="px-6 py-4 border-b border-gray-100">
            <h1 class="text-lg font-bold text-gray-900">Create New User</h1>
        </div>

        <div class="p-6">
            @if ($errors->any())
                <div class="mb-6 bg-rose-50 border border-rose-200 text-rose-600 px-4 py-3 rounded-xl text-sm">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.users.store') }}">
                @csrf

                <div class="grid md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Name *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors"
                               placeholder="Enter name">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors"
                               placeholder="admin@example.com">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Password *</label>
                        <input type="password" name="password" required
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors"
                               placeholder="••••••••">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Confirm Password *</label>
                        <input type="password" name="password_confirmation" required
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors"
                               placeholder="••••••••">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Role *</label>
                        <select name="role" required x-model="role"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                            <option value="admin">Admin</option>
                            <option value="super_admin">Super Admin</option>
                        </select>
                    </div>
                </div>

                {{-- Permission Section --}}
                <div x-show="role !== 'super_admin'" x-transition x-cloak class="mt-8">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-base font-bold text-gray-900">Menu Permissions</h2>
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" @click="
                                let checkboxes = document.querySelectorAll('.perm-check');
                                checkboxes.forEach(cb => cb.checked = $event.target.checked);
                            " class="w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                            <span class="text-sm text-gray-600">Select All</span>
                        </label>
                    </div>

                    <div class="space-y-3">
                        @foreach($menuGroups as $group)
                            @if($group['id'] === 'dashboard') @continue @endif
                            <div class="border border-gray-200 rounded-xl p-4 hover:border-emerald-200 transition-colors">
                                <h3 class="font-semibold text-gray-800 mb-3 text-sm">{{ $group['title'] }}</h3>
                                @foreach($group['items'] as $item)
                                    <div class="ml-4 mb-3">
                                        <div class="flex items-center mb-2">
                                            <input type="checkbox" id="perm_{{ $item['slug'] }}" class="perm-check w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                                            <label for="perm_{{ $item['slug'] }}" class="ml-2 text-sm font-medium text-gray-700">{{ $item['title'] }}</label>
                                        </div>
                                        <div class="ml-6 flex flex-wrap gap-3">
                                            @foreach($item['permissions'] as $perm)
                                                <label class="flex items-center space-x-1.5 cursor-pointer">
                                                    <input type="checkbox" name="permissions[{{ $item['slug'] }}][{{ $perm }}]" value="1"
                                                           class="perm-check w-3.5 h-3.5 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                                                    <span class="text-xs text-gray-600">{{ config('menu.permissions.' . $perm, $perm) }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>

                <div x-show="role === 'super_admin'" x-cloak class="mt-8">
                    <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 text-center">
                        <p class="text-emerald-700 font-medium text-sm">Super Admin has full access to all menus and actions.</p>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-3 mt-8 pt-6 border-t border-gray-200">
                    <a href="{{ route('admin.users.index') }}" class="px-5 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors text-sm font-medium">Cancel</a>
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-all duration-200 text-sm font-medium">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.querySelectorAll('.perm-check').forEach(cb => {
        cb.addEventListener('change', function() {
            let slug = this.name?.match(/permissions\[([^\]]+)\]/)?.[1];
            if (slug) {
                let groupChecks = document.querySelectorAll(`input[name^="permissions[${slug}]"]`);
                let anyChecked = [...groupChecks].some(c => c.checked);
                let parentCheck = document.getElementById(`perm_${slug}`);
                if (parentCheck) parentCheck.checked = anyChecked;
            }
        });
    });
</script>
@endpush
@endsection
