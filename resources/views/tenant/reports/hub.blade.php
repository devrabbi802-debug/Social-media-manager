@extends('layouts.tenant')

@section('title', __('sidebar.reports').' - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <h1 class="text-2xl font-bold text-gray-900">@lang('sidebar.reports')</h1>
            <p class="text-gray-600">সবগুলো মডিউলের অ্যাডভান্সড রিপোর্ট এক জায়গায়</p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @include('tenant.reports.partials._nav', ['current' => 'hub'])

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Sales Report --}}
            <a href="{{ route('reports.sales') }}" class="bg-white rounded-2xl shadow-sm hover:shadow-md transition p-6 border border-transparent hover:border-purple-200">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">@lang('sidebar.sales_reports')</h2>
                        <p class="text-sm text-gray-500">স্টোরফ্রন্ট অর্ডার, রেভিনিউ ও পেমেন্ট বিশ্লেষণ</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center text-purple-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <p class="text-xs text-gray-500">এই মাসের রেভিনিউ</p>
                        <p class="text-xl font-bold text-gray-900">৳{{ number_format($summary['sales']['revenue'], 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">অর্ডার</p>
                        <p class="text-xl font-bold text-gray-900">{{ $summary['sales']['orders_count'] }}</p>
                    </div>
                </div>
                <p class="text-sm text-purple-600 font-medium mt-4">বিস্তারিত দেখুন →</p>
            </a>

            {{-- Inventory Report --}}
            <a href="{{ route('reports.inventory') }}" class="bg-white rounded-2xl shadow-sm hover:shadow-md transition p-6 border border-transparent hover:border-purple-200">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">@lang('sidebar.inventory_reports')</h2>
                        <p class="text-sm text-gray-500">স্টক ভ্যালু, মুভমেন্ট ও লো স্টক বিশ্লেষণ</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center text-blue-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <p class="text-xs text-gray-500">স্টক ভ্যালু</p>
                        <p class="text-xl font-bold text-gray-900">৳{{ number_format($summary['inventory']['stock_value'], 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">মোট পণ্য</p>
                        <p class="text-xl font-bold text-gray-900">{{ $summary['inventory']['products_count'] }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">লো স্টক</p>
                        <p class="text-xl font-bold {{ $summary['inventory']['low_stock'] > 0 ? 'text-orange-600' : 'text-gray-900' }}">{{ $summary['inventory']['low_stock'] }}</p>
                    </div>
                </div>
                <p class="text-sm text-purple-600 font-medium mt-4">বিস্তারিত দেখুন →</p>
            </a>

            {{-- POS Report --}}
            <a href="{{ route('pos.reports') }}" class="bg-white rounded-2xl shadow-sm hover:shadow-md transition p-6 border border-transparent hover:border-purple-200">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">@lang('sidebar.pos_reports')</h2>
                        <p class="text-sm text-gray-500">ক্যাশিয়ার, পেমেন্ট ও ডেইলি সেলস ট্রেন্ড</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center text-green-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <p class="text-xs text-gray-500">এই মাসের সেলস</p>
                        <p class="text-xl font-bold text-gray-900">৳{{ number_format($summary['pos']['sales'], 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">সেলস সংখ্যা</p>
                        <p class="text-xl font-bold text-gray-900">{{ $summary['pos']['orders_count'] }}</p>
                    </div>
                </div>
                <p class="text-sm text-purple-600 font-medium mt-4">বিস্তারিত দেখুন →</p>
            </a>

            {{-- Purchase Report --}}
            <a href="{{ route('purchase.reports') }}" class="bg-white rounded-2xl shadow-sm hover:shadow-md transition p-6 border border-transparent hover:border-purple-200">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">@lang('sidebar.purchase_reports')</h2>
                        <p class="text-sm text-gray-500">সাপ্লায়ার, বাকি ও AP এজিং বিশ্লেষণ</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-orange-100 flex items-center justify-center text-orange-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <p class="text-xs text-gray-500">এই মাসের ক্রয়</p>
                        <p class="text-xl font-bold text-gray-900">৳{{ number_format($summary['purchase']['total'], 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">মোট বাকি</p>
                        <p class="text-xl font-bold {{ $summary['purchase']['due'] > 0 ? 'text-orange-600' : 'text-gray-900' }}">৳{{ number_format($summary['purchase']['due'], 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">বিল</p>
                        <p class="text-xl font-bold text-gray-900">{{ $summary['purchase']['invoice_count'] }}</p>
                    </div>
                </div>
                <p class="text-sm text-purple-600 font-medium mt-4">বিস্তারিত দেখুন →</p>
            </a>
        </div>

        {{-- Inventory warning strip --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">মোট স্টক</p>
                <p class="text-xl font-bold text-gray-900">{{ number_format($summary['inventory']['total_stock']) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">আউট অফ স্টক</p>
                <p class="text-xl font-bold {{ $summary['inventory']['out_of_stock'] > 0 ? 'text-red-600' : 'text-gray-900' }}">{{ $summary['inventory']['out_of_stock'] }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">নিট স্টক মুভমেন্ট (এই মাস)</p>
                <p class="text-xl font-bold {{ $summary['inventory']['net_movement'] < 0 ? 'text-red-600' : 'text-green-600' }}">{{ $summary['inventory']['net_movement'] >= 0 ? '+' : '' }}{{ number_format($summary['inventory']['net_movement']) }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
