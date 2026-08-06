@extends('layouts.tenant')

@section('title', 'New Role')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Create New Role</h1>
                    <p class="text-gray-600">Define a role and the permissions it grants</p>
                </div>
                <a href="{{ route('roles.index') }}" class="text-purple-600 hover:text-purple-800 font-medium text-sm">← Back</a>
            </div>
        </div>
    </div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if($errors->any())
            <div class="mb-6 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-sm">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('roles.store') }}">
            @csrf

            <div class="bg-white rounded-2xl shadow-sm p-6 mb-6">
                <div class="grid md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Role Name *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Inventory Manager"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <input type="text" name="description" value="{{ old('description') }}" placeholder="What is this role responsible for?"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors">
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-6">
                @include('tenant.partials._permission-matrix', [
                    'selectedPermissions' => [],
                    'superAdmin' => false,
                ])
            </div>

            <div class="flex items-center justify-end space-x-3 mt-6">
                <a href="{{ route('roles.index') }}" class="px-5 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition text-sm font-medium">Cancel</a>
                <button type="submit" class="px-5 py-2.5 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition text-sm font-medium">Create Role</button>
            </div>
        </form>

        @stack('scripts')
    </div>
</div>
@endsection