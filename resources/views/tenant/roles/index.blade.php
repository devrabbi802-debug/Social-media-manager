@extends('layouts.tenant')

@section('title', 'User Management - Roles')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">User Management</h1>
                    <p class="text-gray-600">Roles & staff access control</p>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('users.index') }}" class="border border-gray-300 text-gray-700 px-6 py-3 rounded-xl font-medium hover:bg-gray-50 transition">Staff</a>
                    <a href="{{ route('roles.index') }}" class="bg-purple-600 text-white px-6 py-3 rounded-xl font-medium hover:bg-purple-700 transition shadow-sm">Roles</a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-6 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl">{{ session('error') }}</div>
        @endif

        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <a href="{{ route('users.index') }}" class="text-sm px-4 py-2 rounded-lg text-gray-500 hover:bg-gray-100 transition">Staff</a>
                <a href="{{ route('roles.index') }}" class="text-sm px-4 py-2 rounded-lg bg-purple-50 text-purple-700 font-semibold">Roles</a>
            </div>
            @if(auth()->user()->hasPermission('user_management', 'create'))
            <a href="{{ route('roles.create') }}" class="bg-purple-600 text-white px-4 py-2 rounded-lg font-medium text-sm hover:bg-purple-700 transition">
                + New Role
            </a>
            @endif
        </div>

        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            @if($roles->count() > 0)
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Permissions</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Staff</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Created</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($roles as $role)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="w-9 h-9 bg-purple-100 rounded-lg flex items-center justify-center text-purple-600 font-bold text-sm">
                                            {{ mb_substr($role->name, 0, 1) }}
                                        </div>
                                        <div class="ml-3">
                                            <p class="font-medium text-gray-900">
                                                {{ $role->name }}
                                                @if($role->is_system)
                                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-500">SYSTEM</span>
                                                @endif
                                            </p>
                                            @if($role->description)
                                                <p class="text-xs text-gray-500">{{ $role->description }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @php $permCount = count($role->permissionList()); @endphp
                                    <span class="text-sm text-gray-600">{{ $permCount }} permission{{ $permCount === 1 ? '' : 's' }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-50 text-purple-700">{{ $role->users_count }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $role->created_at->diffForHumans() }}</td>
                                <td class="px-6 py-4 text-right text-sm">
                                    <div class="flex items-center justify-end space-x-2">
                                        @if(auth()->user()->hasPermission('user_management', 'edit'))
                                        <a href="{{ route('roles.edit', $role) }}" class="text-blue-600 hover:text-blue-800 p-1">Edit</a>
                                        @endif
                                        @if(auth()->user()->hasPermission('user_management', 'delete') && !$role->is_system)
                                        <form action="{{ route('roles.destroy', $role) }}" method="POST" onsubmit="return confirm('Delete this role?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800 p-1">Delete</button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="text-center py-12">
                    <p class="text-gray-500">No roles yet. Create your first role to start managing access.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection