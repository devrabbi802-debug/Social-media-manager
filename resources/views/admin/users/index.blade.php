@extends('admin.layouts.app')

@section('title', 'ইউজার লিস্ট - Admin Panel')

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">ইউজার ম্যানেজমেন্ট</h1>
            <p class="text-gray-600 text-sm">সব ইউজার দেখুন এবং ম্যানেজ করুন</p>
        </div>
        @if(Auth::guard('admin')->user()->hasPermission('user_management', 'create'))
        <a href="{{ route('admin.users.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            নতুন ইউজার
        </a>
        @endif
    </div>

    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">নাম</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">ইমেইল</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">রোল</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">পারমিশন</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">তৈরি</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($users as $index => $u)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $index + 1 }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 font-bold text-sm">{{ mb_substr($u->name, 0, 1, 'UTF-8') }}</div>
                                <span class="ml-3 font-medium text-gray-900">{{ $u->name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $u->email }}</td>
                        <td class="px-6 py-4">
                            @if($u->role === 'super_admin')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">সুপার অ্যাডমিন</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">অ্যাডমিন</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($u->role === 'super_admin')
                                <span class="text-xs text-gray-500">সব অ্যাক্সেস</span>
                            @else
                                <span class="text-xs text-gray-500">{{ $u->permissions->pluck('menu_slug')->unique()->count() }} টি মেনু</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $u->created_at->diffForHumans() }}</td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                @if(Auth::guard('admin')->user()->hasPermission('user_management', 'edit'))
                                <a href="{{ route('admin.users.edit', $u) }}" class="text-indigo-600 hover:text-indigo-900 p-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                @endif
                                @if(Auth::guard('admin')->user()->hasPermission('user_management', 'delete') && $u->role !== 'super_admin')
                                <form action="{{ route('admin.users.destroy', $u) }}" method="POST" onsubmit="return confirm('আপনি কি নিশ্চিত এই ইউজারটি ডিলিট করতে চান?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 p-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">কোনো ইউজার পাওয়া যায়নি।</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
