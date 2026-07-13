@extends('admin.layouts.app')

@section('title', 'Dashboard - Admin Panel')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
        <p class="text-gray-500 mt-1">Welcome back, <span class="text-gray-700 font-medium">{{ Auth::guard('admin')->user()->name ?? 'Admin' }}</span></p>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        {{-- Total Tenants --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Tenants</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ \App\Models\Tenant::count() }}</p>
                </div>
                <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center border border-emerald-100">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-xs text-emerald-600 font-medium">All registered</span>
            </div>
        </div>

        {{-- Admin Users --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Admin Users</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ \App\Models\Admin::count() }}</p>
                </div>
                <div class="w-12 h-12 bg-violet-50 rounded-xl flex items-center justify-center border border-violet-100">
                    <svg class="w-6 h-6 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-xs text-violet-600 font-medium">Staff accounts</span>
            </div>
        </div>

        {{-- Active Tenants --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Active Tenants</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ \App\Models\Tenant::where('data->status', 'active')->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-teal-50 rounded-xl flex items-center justify-center border border-teal-100">
                    <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-xs text-teal-600 font-medium">Currently active</span>
            </div>
        </div>

        {{-- Trial Tenants --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Trial Tenants</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ \App\Models\Tenant::where('data->status', 'trial')->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center border border-amber-100">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-xs text-amber-600 font-medium">On trial</span>
            </div>
        </div>
    </div>

    {{-- Bottom Section --}}
    <div class="grid lg:grid-cols-2 gap-5">
        {{-- System Info --}}
        <div class="bg-white rounded-xl border border-gray-200 hover:shadow-md transition-shadow duration-200">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-base font-bold text-gray-900">System Info</h2>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <div class="flex items-center justify-between py-2">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-emerald-50 rounded-lg flex items-center justify-center border border-emerald-100">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                                </svg>
                            </div>
                            <span class="text-sm text-gray-600">Laravel Version</span>
                        </div>
                        <span class="text-sm font-semibold text-gray-900">{{ app()->version() }}</span>
                    </div>
                    <div class="border-t border-gray-100"></div>
                    <div class="flex items-center justify-between py-2">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-violet-50 rounded-lg flex items-center justify-center border border-violet-100">
                                <svg class="w-4 h-4 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <span class="text-sm text-gray-600">PHP Version</span>
                        </div>
                        <span class="text-sm font-semibold text-gray-900">{{ phpversion() }}</span>
                    </div>
                    <div class="border-t border-gray-100"></div>
                    <div class="flex items-center justify-between py-2">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-amber-50 rounded-lg flex items-center justify-center border border-amber-100">
                                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <span class="text-sm text-gray-600">Server Time</span>
                        </div>
                        <span class="text-sm font-semibold text-gray-900">{{ now()->format('d M Y, h:i A') }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="bg-white rounded-xl border border-gray-200 hover:shadow-md transition-shadow duration-200">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-base font-bold text-gray-900">Quick Actions</h2>
            </div>
            <div class="p-4">
                <a href="{{ route('admin.tenants.index') }}" class="flex items-center p-3 rounded-xl hover:bg-emerald-50 transition-colors group">
                    <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center mr-3 border border-emerald-100 group-hover:bg-emerald-100 transition-colors">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-gray-900 group-hover:text-emerald-700 transition-colors">Manage Tenants</p>
                        <p class="text-xs text-gray-500">All registered tenants</p>
                    </div>
                    <svg class="w-4 h-4 text-gray-400 group-hover:text-emerald-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>

                <a href="{{ route('admin.users.index') }}" class="flex items-center p-3 rounded-xl hover:bg-violet-50 transition-colors group">
                    <div class="w-10 h-10 bg-violet-50 rounded-xl flex items-center justify-center mr-3 border border-violet-100 group-hover:bg-violet-100 transition-colors">
                        <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-gray-900 group-hover:text-violet-700 transition-colors">View Users</p>
                        <p class="text-xs text-gray-500">All registered users</p>
                    </div>
                    <svg class="w-4 h-4 text-gray-400 group-hover:text-violet-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>

                <a href="{{ route('admin.ai-prompt.index') }}" class="flex items-center p-3 rounded-xl hover:bg-teal-50 transition-colors group">
                    <div class="w-10 h-10 bg-teal-50 rounded-xl flex items-center justify-center mr-3 border border-teal-100 group-hover:bg-teal-100 transition-colors">
                        <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-gray-900 group-hover:text-teal-700 transition-colors">AI Prompts</p>
                        <p class="text-xs text-gray-500">System prompts</p>
                    </div>
                    <svg class="w-4 h-4 text-gray-400 group-hover:text-teal-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
