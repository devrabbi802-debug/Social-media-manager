@extends('layouts.tenant')

@section('title', __('orders.return_history').' - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">@lang('orders.return_history')</h1>
                    <p class="text-gray-600">@lang('orders.return_history_desc')</p>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @include('tenant.partials._nav-tabs', ['activePage' => 'orders'])

        {{-- Filters --}}
        <div class="bg-white rounded-2xl p-4 shadow-sm mb-6">
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">@lang('orders.search')</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="RTN-... or ORD-..." class="border border-gray-300 rounded-xl px-3 py-2 text-sm w-48">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">@lang('orders.return_type_label')</label>
                    <select name="type" class="border border-gray-300 rounded-xl px-3 py-2 text-sm">
                        <option value="">@lang('orders.all')</option>
                        <option value="return" {{ request('type') === 'return' ? 'selected' : '' }}>@lang('orders.return')</option>
                        <option value="exchange" {{ request('type') === 'exchange' ? 'selected' : '' }}>@lang('orders.exchange')</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="border border-gray-300 rounded-xl px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="border border-gray-300 rounded-xl px-3 py-2 text-sm">
                </div>
                <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-xl text-sm font-medium hover:bg-purple-700">@lang('orders.search')</button>
                <a href="{{ route('orders.returns') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl text-sm font-medium hover:bg-gray-200">@lang('orders.reset')</a>
            </form>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 text-left text-sm text-gray-500">
                        <th class="px-6 py-3 font-medium">@lang('orders.return_number')</th>
                        <th class="px-6 py-3 font-medium">@lang('orders.order')</th>
                        <th class="px-6 py-3 font-medium">@lang('orders.return_type_label')</th>
                        <th class="px-6 py-3 font-medium">@lang('orders.items')</th>
                        <th class="px-6 py-3 font-medium text-right">@lang('orders.returned_amount')</th>
                        <th class="px-6 py-3 font-medium">@lang('orders.return_method')</th>
                        <th class="px-6 py-3 font-medium">@lang('orders.return_reason')</th>
                        <th class="px-6 py-3 font-medium">@lang('orders.date')</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($returns as $r)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $r->return_number }}</td>
                        <td class="px-6 py-4">
                            @if($r->order)
                                <a href="{{ route('orders.show', $r->order) }}" class="text-sm text-purple-600 hover:text-purple-700 font-medium">#{{ $r->order->order_number }}</a>
                            @else
                                <span class="text-sm text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($r->isExchange())
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">🔁 @lang('orders.exchange')</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">↩️ @lang('orders.return')</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $r->items->count() }} item(s)
                            @if($r->items->count())
                                <div class="text-xs text-gray-400 mt-0.5">
                                    @foreach($r->items->take(3) as $ri)
                                        {{ $ri->name }} × {{ $ri->quantity }}{{ $loop->last ? '' : ', ' }}
                                    @endforeach
                                    @if($r->items->count() > 3)
                                        <span>+{{ $r->items->count() - 3 }} more</span>
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm font-semibold text-red-600 text-right">-৳{{ number_format($r->amount, 2) }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600 capitalize">{{ $r->method }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500 max-w-[200px] truncate">{{ $r->reason ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $r->created_at->format('d M, Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                <p>@lang('orders.no_returns')</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            @if($returns->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $returns->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
