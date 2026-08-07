@extends('layouts.tenant')

@section('title', 'POS Sessions - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Register Sessions</h1>
                    <p class="text-gray-600">রেজিস্টার সেশন ম্যানেজমেন্ট</p>
                </div>
                <a href="{{ route('pos.index') }}" class="px-4 py-2 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition">Back to POS</a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Open sessions --}}
        @if($openSessions->count())
            <div class="mb-6">
                <h3 class="font-bold text-gray-900 mb-3">Open Sessions</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($openSessions as $session)
                        <div class="bg-green-50 border border-green-200 rounded-2xl p-5">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-bold text-green-800">Session #{{ $session->id }}</p>
                                    <p class="text-sm text-green-700">{{ $session->user->name ?? '-' }} · <span class="font-medium">গুদাম: {{ $session->warehouse?->name ?? 'ডিফল্ট' }}</span> · খোলা {{ $session->opened_at->diffForHumans() }}</p>
                                </div>
                                <a href="{{ route('pos.sessions.show', $session) }}" class="text-sm px-3 py-1.5 bg-green-600 text-white rounded-lg hover:bg-green-700">View</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Sessions table --}}
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="font-bold text-gray-900">সকল সেশন</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ক্যাশিয়ার</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">গুদাম</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">খোলা</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">বন্ধ</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">মোট বিক্রয়</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">ক্যাশ</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">কার্ড</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">মোবাইল</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">স্ট্যাটাস</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ডিফারেন্স</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($sessions as $session)
                            <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('pos.sessions.show', $session) }}'">
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900">#{{ $session->id }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $session->user->name ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $session->warehouse?->name ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $session->opened_at?->format('d M H:i') }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $session->closed_at?->format('d M H:i') ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900 text-right">৳{{ number_format($session->total_sales, 2) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700 text-right">৳{{ number_format($session->cash_sales, 2) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700 text-right">৳{{ number_format($session->card_sales, 2) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700 text-right">৳{{ number_format($session->mobile_sales, 2) }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs rounded-full font-medium {{ $session->status === 'open' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">{{ $session->status }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium {{ $session->cash_difference < 0 ? 'text-red-600' : 'text-green-600' }}">৳{{ number_format($session->cash_difference, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="px-6 py-10 text-center text-gray-500">কোনো সেশন নেই</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $sessions->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
