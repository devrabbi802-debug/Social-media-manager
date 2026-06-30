@extends('admin.layouts.app')

@section('title', 'Create Tenant')

@section('content')
<div class="p-6">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Create New Tenant</h1>
        <p class="text-gray-600">Set up a new tenant with their own database and subdomain</p>
    </div>

    @if($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form action="{{ route('admin.tenants.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="id" class="block text-sm font-medium text-gray-700 mb-2">Subdomain (ID) <span class="text-red-500">*</span></label>
                    <input type="text" id="id" name="id" value="{{ old('id') }}" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                           placeholder="e.g. acme">
                    <p class="mt-1 text-sm text-gray-500">This will be used as subdomain (e.g., <strong>acme</strong>.socialboost.com)</p>
                </div>

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Name <span class="text-red-500">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                           placeholder="Tenant name">
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email <span class="text-red-500">*</span></label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                           placeholder="tenant@example.com">
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                           placeholder="+8801712345678">
                </div>

                <div>
                    <label for="company" class="block text-sm font-medium text-gray-700 mb-2">Company</label>
                    <input type="text" id="company" name="company" value="{{ old('company') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                           placeholder="Company name">
                </div>

                <div>
                    <label for="plan" class="block text-sm font-medium text-gray-700 mb-2">Plan <span class="text-red-500">*</span></label>
                    <select id="plan" name="plan" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                        <option value="trial" {{ old('plan') == 'trial' ? 'selected' : '' }}>Trial</option>
                        <option value="basic" {{ old('plan') == 'basic' ? 'selected' : '' }}>Basic</option>
                        <option value="pro" {{ old('plan') == 'pro' ? 'selected' : '' }}>Pro</option>
                        <option value="enterprise" {{ old('plan') == 'enterprise' ? 'selected' : '' }}>Enterprise</option>
                    </select>
                </div>
            </div>

            <div class="mt-8 flex items-center space-x-4">
                <button type="submit" class="bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700 transition font-medium">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Create Tenant
                </button>
                <a href="{{ route('admin.tenants.index') }}" class="bg-gray-100 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-200 transition font-medium">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
