@extends('admin.layouts.app')

@section('title', 'নতুন ইউজার তৈরি - Admin Panel')

@section('content')
<div class="p-6">
    <div class="mb-6">
        <a href="{{ route('admin.users.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            ইউজার লিস্টে ফিরে যান
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h1 class="text-xl font-bold text-gray-900 mb-6">নতুন ইউজার তৈরি করুন</h1>

        @if ($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg text-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf

            <div class="grid md:grid-cols-2 gap-6 mb-8">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">নাম *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           placeholder="ইউজারের নাম">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">ইমেইল *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           placeholder="admin@example.com">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">পাসওয়ার্ড *</label>
                    <input type="password" name="password" required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           placeholder="••••••••">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">পাসওয়ার্ড কনফার্ম *</label>
                    <input type="password" name="password_confirmation" required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           placeholder="••••••••">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">রোল *</label>
                    <select name="role" required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>অ্যাডমিন</option>
                        <option value="super_admin" {{ old('role') === 'super_admin' ? 'selected' : '' }}>সুপার অ্যাডমিন</option>
                    </select>
                </div>
            </div>

            {{-- Permission Section --}}
            <div x-data="{ allChecked: false }">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-gray-900">মেনু পারমিশন</h2>
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" x-model="allChecked" @click="
                            let checkboxes = document.querySelectorAll('.perm-check');
                            checkboxes.forEach(cb => cb.checked = allChecked ? false : true);
                        " class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <span class="text-sm text-gray-600">সব সিলেক্ট করুন</span>
                    </label>
                </div>

                <div class="space-y-4">
                    @foreach($menuGroups as $group)
                        @if($group['id'] === 'dashboard') @continue @endif
                        <div class="border border-gray-200 rounded-xl p-4">
                            <h3 class="font-semibold text-gray-800 mb-3">{{ $group['title'] }}</h3>
                            @foreach($group['items'] as $item)
                                <div class="ml-4 mb-3">
                                    <div class="flex items-center mb-2">
                                        <input type="checkbox" id="perm_{{ $item['slug'] }}" class="perm-check w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                        <label for="perm_{{ $item['slug'] }}" class="ml-2 text-sm font-medium text-gray-700">{{ $item['title'] }}</label>
                                    </div>
                                    <div class="ml-6 flex flex-wrap gap-3">
                                        @foreach($item['permissions'] as $perm)
                                            <label class="flex items-center space-x-1.5 cursor-pointer">
                                                <input type="checkbox" name="permissions[{{ $item['slug'] }}][{{ $perm }}]" value="1"
                                                       class="perm-check w-3.5 h-3.5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
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

            <div class="flex items-center justify-end space-x-3 mt-8 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.users.index') }}" class="px-5 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">বাতিল</a>
                <button type="submit" class="px-5 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">ইউজার তৈরি করুন</button>
            </div>
        </form>
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
