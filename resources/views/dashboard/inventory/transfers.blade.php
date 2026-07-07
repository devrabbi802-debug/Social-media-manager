@extends('layouts.app')

@section('title', 'স্টক ট্রান্সফার - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">স্টক ট্রান্সফার</h1>
                    <p class="text-gray-600">গুদমের মধ্যে স্টক স্থানান্তর</p>
                </div>
                <a href="{{ route('inventory.index') }}" class="text-gray-600 hover:text-purple-600 font-medium">← ফিরে যান</a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Sub-Navigation --}}
        <div class="mb-6 flex flex-wrap gap-2">
            <a href="{{ route('inventory.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white text-gray-700 border hover:bg-purple-50 hover:text-purple-600 transition">সারাংশ</a>
            <a href="{{ route('inventory.products.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white text-gray-700 border hover:bg-purple-50 hover:text-purple-600 transition">প্রোডাক্ট</a>
            <a href="{{ route('inventory.transfers.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-purple-600 text-white">ট্রান্সফার</a>
            <a href="{{ route('inventory.movements') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white text-gray-700 border hover:bg-purple-50 hover:text-purple-600 transition">মুভমেন্ট</a>
        </div>

        @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-2xl p-4 mb-6 flex items-center">
            <svg class="w-6 h-6 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <p class="text-green-800 font-medium">{{ session('success') }}</p>
        </div>
        @endif

        @if(session('error'))
        <div class="bg-red-50 border border-red-200 rounded-2xl p-4 mb-6 flex items-center">
            <svg class="w-6 h-6 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            <p class="text-red-800 font-medium">{{ session('error') }}</p>
        </div>
        @endif

        {{-- Transfer Form --}}
        <div class="bg-white rounded-2xl p-6 shadow-sm mb-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">নতুন ট্রান্সফার</h2>
            <form action="{{ route('inventory.transfers.store') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">প্রোডাক্ট *</label>
                        <select name="product_id" required class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                            <option value="">নির্বাচন করুন</option>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->sku }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">থেকে (উৎস গুদম) *</label>
                        <select name="from_warehouse_id" required class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                            <option value="">নির্বাচন করুন</option>
                            @foreach($warehouses as $w)
                                <option value="{{ $w->id }}">{{ $w->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">যেখানে (গন্তব্য গুদম) *</label>
                        <select name="to_warehouse_id" required class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                            <option value="">নির্বাচন করুন</option>
                            @foreach($warehouses as $w)
                                <option value="{{ $w->id }}">{{ $w->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">পরিমাণ *</label>
                        <input type="number" name="quantity" min="1" required class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">নোট</label>
                        <input type="text" name="notes" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition">ট্রান্সফার অনুরোধ করুন</button>
                </div>
            </form>
        </div>

        {{-- Transfers List --}}
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b">
                <h2 class="text-lg font-bold text-gray-900">ট্রান্সফার ইতিহাস</h2>
            </div>
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 text-left text-sm text-gray-500">
                        <th class="px-6 py-4 font-medium">প্রোডাক্ট</th>
                        <th class="px-6 py-4 font-medium">থেকে</th>
                        <th class="px-6 py-4 font-medium">যেখানে</th>
                        <th class="px-6 py-4 font-medium">পরিমাণ</th>
                        <th class="px-6 py-4 font-medium">স্ট্যাটাস</th>
                        <th class="px-6 py-4 font-medium">তারিখ</th>
                        <th class="px-6 py-4 font-medium text-right">কাজ</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($transfers as $transfer)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <span class="font-medium text-gray-900">{{ $transfer->product->name }}</span>
                            @if($transfer->variant)
                                <p class="text-xs text-gray-500">{{ $transfer->variant->sku }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $transfer->fromWarehouse->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $transfer->toWarehouse->name }}</td>
                        <td class="px-6 py-4 font-semibold text-gray-900">{{ $transfer->quantity }}</td>
                        <td class="px-6 py-4">
                            @if($transfer->status === 'pending')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">অপেক্ষমান</span>
                            @elseif($transfer->status === 'completed')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">সম্পন্ন</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">বাতিল</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $transfer->created_at->format('d M Y H:i') }}</td>
                        <td class="px-6 py-4 text-right">
                            @if($transfer->status === 'pending')
                                <div class="flex items-center justify-end space-x-2">
                                    <form action="{{ route('inventory.transfers.complete', $transfer) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-green-600 hover:text-green-800 text-xs font-medium">সম্পন্ন</button>
                                    </form>
                                    <form action="{{ route('inventory.transfers.cancel', $transfer) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-orange-600 hover:text-orange-800 text-xs font-medium">বাতিল</button>
                                    </form>
                                    <form action="{{ route('inventory.transfers.destroy', $transfer) }}" method="POST" class="inline" onsubmit="return confirm('ডিলিট করতে চান?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium">ডিলিট</button>
                                    </form>
                                </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-6 py-12 text-center text-gray-500">কোনো ট্রান্সফার নেই</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($transfers->hasPages())<div class="px-6 py-4 border-t">{{ $transfers->withQueryString()->links() }}</div>@endif
        </div>
    </div>
</div>
@endsection
