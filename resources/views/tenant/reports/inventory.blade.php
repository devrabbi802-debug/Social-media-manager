@extends('layouts.tenant')

@section('title', __('sidebar.inventory_reports').' - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">@lang('sidebar.inventory_reports')</h1>
                    <p class="text-gray-600">স্টক ভ্যালু, মুভমেন্ট ও লো স্টক বিশ্লেষণ</p>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @include('tenant.reports.partials._nav', ['current' => 'inventory'])

        <form method="GET" class="bg-white rounded-2xl p-4 shadow-sm mb-6 flex flex-wrap gap-3">
            <div class="flex items-center gap-2">
                <label class="text-sm text-gray-600">থেকে</label>
                <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="border border-gray-300 rounded-xl px-4 py-2 text-sm">
            </div>
            <div class="flex items-center gap-2">
                <label class="text-sm text-gray-600">পর্যন্ত</label>
                <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="border border-gray-300 rounded-xl px-4 py-2 text-sm">
            </div>
            <select name="type" class="border border-gray-300 rounded-xl px-4 py-2 text-sm">
                <option value="">সব মুভমেন্ট টাইপ</option>
                @foreach(['in', 'out', 'adjustment'] as $t)
                    <option value="{{ $t }}" {{ $type === $t ? 'selected' : '' }}>{{ \App\Models\StockMovement::typeLabel($t) }}</option>
                @endforeach
            </select>
            <select name="warehouse_id" class="border border-gray-300 rounded-xl px-4 py-2 text-sm">
                <option value="">সব গুদাম</option>
                @foreach($warehouses as $wh)
                    <option value="{{ $wh->id }}" {{ $warehouseId == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-5 py-2 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition text-sm">রিপোর্ট দেখুন</button>
            @if($type || $warehouseId || request()->filled('from') || request()->filled('to'))
                <a href="{{ route('reports.inventory') }}" class="px-5 py-2 border border-gray-300 text-gray-700 rounded-xl font-medium hover:bg-gray-50 transition text-sm">রিসেট</a>
            @endif
        </form>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-9 gap-4 mb-6">
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">মোট পণ্য</p>
                <p class="text-xl font-bold text-gray-900">{{ $summary['total_products'] }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">মোট স্টক</p>
                <p class="text-xl font-bold text-gray-900">{{ number_format($summary['total_stock']) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">স্টক ভ্যালু</p>
                <p class="text-xl font-bold text-purple-600">৳{{ number_format($summary['stock_value'], 2) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">লো স্টক</p>
                <p class="text-xl font-bold {{ $summary['low_stock_count'] > 0 ? 'text-orange-600' : 'text-gray-900' }}">{{ $summary['low_stock_count'] }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">আউট অফ স্টক</p>
                <p class="text-xl font-bold {{ $summary['out_of_stock_count'] > 0 ? 'text-red-600' : 'text-gray-900' }}">{{ $summary['out_of_stock_count'] }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">মুভমেন্ট (পিরিয়ড)</p>
                <p class="text-xl font-bold text-gray-900">{{ $summary['movements_count'] }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">স্টক ইন</p>
                <p class="text-xl font-bold text-green-600">+{{ number_format($summary['in_qty']) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">স্টক আউট</p>
                <p class="text-xl font-bold text-red-600">-{{ number_format($summary['out_qty']) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">নিট মুভমেন্ট</p>
                <p class="text-xl font-bold {{ $summary['net_movement'] < 0 ? 'text-red-600' : 'text-green-600' }}">{{ $summary['net_movement'] >= 0 ? '+' : '' }}{{ number_format($summary['net_movement']) }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200"><h3 class="font-bold text-gray-900">ক্যাটাগরি অনুযায়ী স্টক ভ্যালু</h3></div>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ক্যাটাগরি</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">পণ্য</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">স্টক</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">ভ্যালু</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($byCategory as $row)
                            <tr>
                                <td class="px-6 py-3 text-sm text-gray-900">{{ $row['category'] }}</td>
                                <td class="px-6 py-3 text-sm text-right text-gray-600">{{ $row['count'] }}</td>
                                <td class="px-6 py-3 text-sm text-right text-gray-600">{{ number_format($row['stock']) }}</td>
                                <td class="px-6 py-3 text-sm text-right font-semibold text-gray-900">৳{{ number_format($row['value'], 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-6 py-8 text-center text-gray-500">কোনো ডেটা নেই</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200"><h3 class="font-bold text-gray-900">ব্র্যান্ড অনুযায়ী স্টক ভ্যালু</h3></div>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ব্র্যান্ড</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">পণ্য</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">স্টক</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">ভ্যালু</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($byBrand as $row)
                            <tr>
                                <td class="px-6 py-3 text-sm text-gray-900">{{ $row['brand'] }}</td>
                                <td class="px-6 py-3 text-sm text-right text-gray-600">{{ $row['count'] }}</td>
                                <td class="px-6 py-3 text-sm text-right text-gray-600">{{ number_format($row['stock']) }}</td>
                                <td class="px-6 py-3 text-sm text-right font-semibold text-gray-900">৳{{ number_format($row['value'], 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-6 py-8 text-center text-gray-500">কোনো ডেটা নেই</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200"><h3 class="font-bold text-gray-900">মুভমেন্ট টাইপ (পিরিয়ড)</h3></div>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">টাইপ</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">ইভেন্ট</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">পরিমাণ</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($byType as $row)
                            <tr>
                                <td class="px-6 py-3 text-sm text-gray-900">{{ \App\Models\StockMovement::typeLabel($row->type) }}</td>
                                <td class="px-6 py-3 text-sm text-right text-gray-600">{{ $row->count }}</td>
                                <td class="px-6 py-3 text-sm text-right font-semibold {{ $row->type === 'in' ? 'text-green-600' : ($row->type === 'out' ? 'text-red-600' : 'text-gray-900') }}">{{ $row->quantity }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-6 py-8 text-center text-gray-500">কোনো ডেটা নেই</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200"><h3 class="font-bold text-gray-900">সবচেয়ে বেশি মুভড পণ্য</h3></div>
                <div class="divide-y divide-gray-200">
                    @forelse($topMoved as $index => $tm)
                        <div class="px-6 py-3 flex items-center gap-3">
                            <span class="w-6 h-6 flex items-center justify-center rounded-full bg-blue-100 text-blue-700 text-xs font-bold">{{ $index + 1 }}</span>
                            <div class="flex-1 min-w-0">
                                @if($tm->variant)
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $tm->variant->display }} <span class="text-gray-400 text-xs">({{ $tm->variant->product->name ?? '' }})</span></p>
                                @elseif($tm->product)
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $tm->product->name }}</p>
                                @else
                                    <p class="text-sm font-medium text-gray-500">#{{ $tm->product_id }}</p>
                                @endif
                            </div>
                            <span class="text-sm font-semibold text-gray-900">{{ $tm->quantity }} pcs</span>
                        </div>
                    @empty
                        <div class="px-6 py-8 text-center text-gray-500">কোনো ডেটা নেই</div>
                    @endforelse
                </div>
            </div>
        </div>

        @if($lowStockProducts->count())
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-gray-200"><h3 class="font-bold text-gray-900">লো স্টক পণ্য</h3></div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">পণ্য</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">স্টক</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">থ্রেশহোল্ড</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($lowStockProducts as $product)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $product->name }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $product->sku ?: '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-right font-semibold text-orange-600">{{ $product->total_stock }}</td>
                                    <td class="px-4 py-3 text-sm text-right text-gray-600">{{ $product->low_stock_threshold }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="font-bold text-gray-900">স্টক মুভমেন্ট ডিটেইল</h3>
                <span class="text-sm text-gray-500">{{ $movements->total() }} টি মুভমেন্ট</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">তারিখ</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">পণ্য</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">গুদাম</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">টাইপ</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">রেফারেন্স</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">পরিমাণ</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($movements as $movement)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $movement->created_at->format('d M Y h:i A') }}</td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                    @if($movement->variant)
                                        {{ $movement->variant->display }} <span class="text-gray-400 text-xs">({{ $movement->variant->product->name ?? '' }})</span>
                                    @elseif($movement->product)
                                        {{ $movement->product->name }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $movement->warehouse?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-sm">
                                    @php
                                        $typeColors = ['in' => 'text-green-600', 'out' => 'text-red-600', 'adjustment' => 'text-gray-600'];
                                    @endphp
                                    <span class="font-medium {{ $typeColors[$movement->type] ?? 'text-gray-600' }}">{{ $movement->type_label }}</span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $movement->reference ?: '—' }}</td>
                                <td class="px-4 py-3 text-sm font-semibold text-right {{ $movement->type === 'in' ? 'text-green-600' : ($movement->type === 'out' ? 'text-red-600' : 'text-gray-900') }}">{{ $movement->quantity_display }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-10 text-center text-gray-500">কোনো মুভমেন্ট নেই</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($movements->hasPages())
                <div class="px-4 py-3 border-t border-gray-200">
                    {{ $movements->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
