@extends('layouts.tenant')

@section('title', __('orders.order_details').' - #'.$order->order_number.' - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="{{ route('orders.index') }}" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">@lang('orders.order_details')</h1>
                        <p class="text-gray-600">#{{ $order->order_number }}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('orders.print', $order) }}" target="_blank" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-xl font-medium hover:bg-gray-50 transition">@lang('orders.print')</a>
                    @if(auth()->user()?->hasPermission('orders', 'create') !== false)
                    <a href="{{ route('orders.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-xl font-medium hover:bg-blue-700 transition">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                            @lang('orders.new_order')
                        </span>
                    </a>
                    @endif
                    <a href="{{ route('orders.edit', $order) }}" class="px-4 py-2 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition">@lang('orders.edit_order')</a>
                </div>
            </div>
        </div>
    </div>

    @php
        $qrData = url('/supermaster/orders/' . $order->id);
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @include('tenant.partials._nav-tabs', ['activePage' => 'orders'])

        {{-- Customer & Shipping Info (Top) --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <h3 class="font-bold text-gray-900 mb-4">@lang('orders.customer_info')</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">@lang('orders.name')</dt><dd class="font-medium text-gray-900">{{ $order->customer_name }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">@lang('orders.phone')</dt><dd class="font-medium text-gray-900">{{ $order->customer_phone }}</dd></div>
                    @if($order->customer && $order->customer->email)
                    <div class="flex justify-between"><dt class="text-gray-500">Email</dt><dd class="font-medium text-gray-900">{{ $order->customer->email }}</dd></div>
                    @endif
                </dl>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <h3 class="font-bold text-gray-900 mb-4">@lang('orders.shipping_info')</h3>
                <dl class="space-y-2 text-sm">
                    @if($order->shippingAddress)
                        <div><dt class="text-gray-500">Address</dt><dd class="font-medium text-gray-900">{{ $order->shippingAddress->address }}, {{ $order->shippingAddress->city }}, {{ $order->shippingAddress->district }}</dd></div>
                    @endif
                    <div class="flex justify-between"><dt class="text-gray-500">@lang('orders.carrier')</dt><dd class="font-medium text-gray-900">{{ $order->carrier ?? '-' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">@lang('orders.tracking_id')</dt><dd class="font-medium text-gray-900">{{ $order->tracking_id ?? '-' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">@lang('orders.estimated_delivery')</dt><dd class="font-medium text-gray-900">{{ $order->estimated_delivery ? $order->estimated_delivery->format('d M, Y') : '-' }}</dd></div>
                </dl>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm flex flex-col items-center justify-center">
                <h3 class="font-bold text-gray-900 mb-3">Order QR</h3>
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ urlencode($qrData) }}"
                     alt="QR for #{{ $order->order_number }}"
                     class="rounded-lg"
                     style="image-rendering: pixelated;">
                <p class="text-xs text-gray-400 mt-2">#{{ $order->order_number }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left Column --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Order Items --}}
                <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b">
                        <h2 class="text-lg font-bold text-gray-900">@lang('orders.order_items')</h2>
                    </div>
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50 text-left text-sm text-gray-500">
                                <th class="px-6 py-3 font-medium">@lang('orders.product')</th>
                                <th class="px-6 py-3 font-medium">SKU</th>
                                <th class="px-6 py-3 font-medium text-center">@lang('orders.qty')</th>
                                <th class="px-6 py-3 font-medium text-right">@lang('orders.unit_price')</th>
                                <th class="px-6 py-3 font-medium text-right">@lang('orders.total_price')</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach($order->items as $item)
                            @php $returnedQty = $order->returnedQuantity($item); @endphp
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        @if($item->product && $item->product->primaryImage)
                                            <img src="{{ asset('storage/' . $item->product->primaryImage->image_path) }}" alt="" class="w-12 h-12 rounded-lg object-cover flex-shrink-0">
                                        @else
                                            <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            </div>
                                        @endif
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $item->name }}</p>
                                            @if($item->variant)
                                                <p class="text-xs text-gray-500">{{ $item->variant->display ?? $item->variant->name }}</p>
                                            @endif
                                            @if($returnedQty > 0)
                                                <span class="inline-flex items-center px-2 py-0.5 mt-1 rounded-full bg-gray-100 text-gray-600 text-xs">{{ __('orders.already_returned') }} {{ $returnedQty }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $item->sku }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700 text-center">{{ $item->quantity }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700 text-right">৳{{ number_format($item->unit_price, 2) }}</td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900 text-right">৳{{ number_format($item->total_price, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr><td colspan="4" class="px-6 py-2 text-sm text-gray-600 text-right">@lang('orders.subtotal')</td><td class="px-6 py-2 text-sm font-semibold text-right">৳{{ number_format($order->subtotal, 2) }}</td></tr>
                            @if($order->shipping_cost > 0)<tr><td colspan="4" class="px-6 py-2 text-sm text-gray-600 text-right">@lang('orders.shipping_cost')</td><td class="px-6 py-2 text-sm text-right">৳{{ number_format($order->shipping_cost, 2) }}</td></tr>@endif
                            @if($order->tax > 0)<tr><td colspan="4" class="px-6 py-2 text-sm text-gray-600 text-right">@lang('orders.tax')</td><td class="px-6 py-2 text-sm text-right">৳{{ number_format($order->tax, 2) }}</td></tr>@endif
                            @if($order->discount > 0)<tr><td colspan="4" class="px-6 py-2 text-sm text-gray-600 text-right">@lang('orders.discount')</td><td class="px-6 py-2 text-sm text-red-600 text-right">-৳{{ number_format($order->discount, 2) }}</td></tr>@endif
                            <tr class="border-t-2 border-gray-300"><td colspan="4" class="px-6 py-3 text-sm font-bold text-gray-900 text-right">@lang('orders.total')</td><td class="px-6 py-3 text-lg font-bold text-gray-900 text-right">৳{{ number_format($order->total, 2) }}</td></tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Notes --}}
                @if($order->notes)
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h3 class="font-bold text-gray-900 mb-2">@lang('orders.notes')</h3>
                    <p class="text-gray-700 whitespace-pre-wrap">{{ $order->notes }}</p>
                </div>
                @endif
            </div>

            {{-- Right Column --}}
            <div class="space-y-6">
                {{-- Order Status --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h3 class="font-bold text-gray-900 mb-4">@lang('orders.order_status_history')</h3>
                    <div class="space-y-3">
                        @php
                            $statusFlow = ['pending', 'processing', 'shipped', 'delivered'];
                            $currentIdx = array_search($order->status, $statusFlow);
                        @endphp
                        @foreach($statusFlow as $i => $s)
                            <div class="flex items-center gap-3">
                                @if($currentIdx === false || $i < $currentIdx)
                                    <div class="w-6 h-6 rounded-full bg-green-500 flex items-center justify-center"><svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/></svg></div>
                                @elseif($i === $currentIdx)
                                    <div class="w-6 h-6 rounded-full bg-purple-500 flex items-center justify-center"><div class="w-2 h-2 bg-white rounded-full"></div></div>
                                @else
                                    <div class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center"></div>
                                @endif
                                <span class="text-sm {{ $i <= ($currentIdx ?? -1) ? 'text-gray-900 font-medium' : 'text-gray-400' }}">{{ __("orders.{$s}") }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Payment Info --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h3 class="font-bold text-gray-900 mb-4">@lang('orders.payment_info')</h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between"><dt class="text-gray-500">@lang('orders.payment_method')</dt><dd class="font-medium text-gray-900">{{ $order->payment_method ?? '-' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">@lang('orders.payment_status')</dt>
                            <dd>
                                @php $badgeColors = ['paid' => 'bg-green-100 text-green-800', 'pending' => 'bg-yellow-100 text-yellow-800', 'failed' => 'bg-red-100 text-red-800', 'refunded' => 'bg-gray-100 text-gray-800']; @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $badgeColors[$order->payment_status] ?? 'bg-gray-100 text-gray-800' }}">{{ __("orders.{$order->payment_status}") }}</span>
                            </dd>
                        </div>
                    </dl>

                    {{-- Receive payment (posts to accounting ledger) --}}
                    @if(! in_array($order->payment_status, ['paid', 'refunded'], true))
                    <div class="mt-4 border-t border-gray-100 pt-4" x-data="{ paymentOpen: false }">
                        <button @click="paymentOpen = !paymentOpen" type="button" class="w-full px-4 py-2 bg-green-600 text-white rounded-xl text-sm font-medium hover:bg-green-700">@lang('orders.receive_payment')</button>
                        <form x-show="paymentOpen" x-cloak method="POST" action="{{ route('orders.receive-payment', $order) }}" class="mt-3 space-y-2">
                            @csrf
                            <input type="number" step="0.01" min="0.01" name="amount" placeholder="৳" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm" required>
                            <select name="payment_method" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm" required>
                                @foreach($paymentMethods as $m)
                                    <option value="{{ $m }}" {{ $order->payment_method === $m ? 'selected' : '' }}>{{ __("orders.method_{$m}") }}</option>
                                @endforeach
                            </select>
                            <input type="text" name="reference" placeholder="Reference (optional)" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
                            <button type="submit" class="w-full px-4 py-2 bg-purple-600 text-white rounded-xl text-sm font-medium hover:bg-purple-700">@lang('common.submit')</button>
                        </form>
                    </div>
                    @endif
                </div>

                {{-- Return & Exchange --}}
                @if(! in_array($order->status, ['cancelled', 'refunded'], true))
                <div class="bg-white rounded-2xl p-6 shadow-sm" x-data="{
                    returnOpen: false,
                    exchangeOpen: false,
                    exchangeCart: [],
                    searchQ: '',
                    searchResults: [],
                    async searchProducts() {
                        if (! this.searchQ.trim()) { this.searchResults = []; return; }
                        const params = new URLSearchParams({ search: this.searchQ, take: 10 });
                        const res = await fetch('{{ route('orders.products') }}?' + params.toString(), { headers: { 'Accept': 'application/json' } });
                        const json = await res.json();
                        this.searchResults = json.data ?? [];
                    },
                    addExchange(p) {
                        const key = p.id + '_' + (p.variants?.length ? p.variants[0].id : '');
                        if (! this.exchangeCart.some(x => x.key === key)) {
                            this.exchangeCart.push({
                                key,
                                product_id: p.id,
                                variant_id: p.variants?.length ? p.variants[0].id : null,
                                name: p.name,
                                price: parseFloat(p.variants?.length ? p.variants[0].price : p.price),
                                qty: 1,
                            });
                        }
                        this.searchQ = '';
                        this.searchResults = [];
                    },
                    removeExchange(i) { this.exchangeCart.splice(i, 1); },
                    recalcReturned() {
                        this.returnedValue = Array.from(document.querySelectorAll('.exchange-ret-qty')).reduce((s, el) => s + (Number(el.value || 0) * Number(el.dataset.price || 0)), 0);
                    },
                    get returnedValue() {
                        return this._returnedValue ?? 0;
                    },
                    set returnedValue(v) { this._returnedValue = v; },
                    get newValue() {
                        return this.exchangeCart.reduce((s, x) => s + (x.price * x.qty), 0);
                    },
                    get difference() {
                        return this.newValue - this.returnedValue;
                    },
                    syncExchangeJson() {
                        document.querySelector('#exchange-items-json').value = JSON.stringify(this.exchangeCart.map(x => ({ product_id: x.product_id, variant_id: x.variant_id, quantity: x.qty, name: x.name, unit_price: x.price })));
                    },
                    formatMoney(n) {
                        return '৳' + Number(n).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    },
                }">
                        <h3 class="font-bold text-gray-900 mb-4">@lang('orders.return') / @lang('orders.exchange')</h3>
                        <div class="grid grid-cols-2 gap-2">
                            <button @click="returnOpen = !returnOpen; exchangeOpen = false" type="button" class="px-4 py-2.5 bg-amber-500 text-white rounded-xl text-sm font-medium hover:bg-amber-600">
                                ↩️ @lang('orders.return')
                            </button>
                            <button @click="exchangeOpen = !exchangeOpen; returnOpen = false" type="button" class="px-4 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700">
                                🔁 @lang('orders.exchange')
                            </button>
                        </div>

                        {{-- Return form --}}
                        <form x-show="returnOpen" x-cloak method="POST" action="{{ route('orders.return', $order) }}" class="mt-4 space-y-3">
                            @csrf
                            <p class="text-sm text-gray-600">@lang('orders.return_title')</p>

                            <div class="space-y-2 max-h-56 overflow-y-auto border border-gray-100 rounded-xl p-3">
                                @foreach($order->items as $item)
                                @php $maxQty = $item->quantity - $order->returnedQuantity($item); @endphp
                                @if($maxQty > 0)
                                <div class="flex items-center gap-3 bg-gray-50 rounded-lg p-2">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-800 truncate">{{ $item->name }}</p>
                                        <p class="text-xs text-gray-500">৳{{ number_format($item->unit_price, 2) }} × max {{ $maxQty }}</p>
                                    </div>
                                    <input type="hidden" name="items[{{ $loop->index }}][order_item_id]" value="{{ $item->id }}">
                                    <input type="number" name="items[{{ $loop->index }}][quantity]" min="0" max="{{ $maxQty }}" value="0" class="w-20 border border-gray-300 rounded-lg px-2 py-1.5 text-sm">
                                </div>
                                @endif
                                @endforeach
                            </div>

                            <div>
                                <label class="block text-xs text-gray-500 mb-1">@lang('orders.refund_method')</label>
                                <select name="method" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
                                    @foreach($paymentMethods as $m)
                                        <option value="{{ $m }}" {{ $order->payment_method === $m ? 'selected' : '' }}>{{ __("orders.method_{$m}") }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">@lang('orders.return_reason')</label>
                                <textarea name="reason" rows="2" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm" placeholder="@lang('orders.reason_placeholder')"></textarea>
                            </div>
                            <button type="submit" class="w-full px-4 py-2.5 bg-amber-600 text-white rounded-xl text-sm font-semibold hover:bg-amber-700">↩️ @lang('orders.process_return')</button>
                        </form>

{{-- Exchange form --}}
                    <form x-show="exchangeOpen" x-cloak method="POST" action="{{ route('orders.exchange', $order) }}" @submit="syncExchangeJson()" class="mt-4 space-y-3">
                        @csrf
                        <input type="hidden" name="exchange_items_json" id="exchange-items-json">
                        <p class="text-sm text-gray-600">@lang('orders.exchange_title')</p>

                            {{-- Items to return --}}
                            <p class="text-xs font-medium text-gray-500 uppercase">1. @lang('orders.return')</p>
                            <div class="space-y-2 max-h-40 overflow-y-auto border border-gray-100 rounded-xl p-3">
                                @foreach($order->items as $item)
                                @php $maxQty = $item->quantity - $order->returnedQuantity($item); @endphp
                                @if($maxQty > 0)
                                <div class="flex items-center gap-3 bg-gray-50 rounded-lg p-2">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-800 truncate">{{ $item->name }}</p>
                                        <p class="text-xs text-gray-500">৳{{ number_format($item->unit_price, 2) }} × max {{ $maxQty }}</p>
                                    </div>
                                    <input type="hidden" name="return_items[{{ $loop->index }}][order_item_id]" value="{{ $item->id }}">
                                    <input type="number" x-on:input="recalcReturned()" data-price="{{ $item->unit_price }}" name="return_items[{{ $loop->index }}][quantity]" min="0" max="{{ $maxQty }}" value="0" class="exchange-ret-qty w-20 border border-gray-300 rounded-lg px-2 py-1.5 text-sm">
                                </div>
                                @endif
                                @endforeach
                            </div>

                            {{-- Replacement items picker --}}
                            <p class="text-xs font-medium text-gray-500 uppercase pt-2">2. @lang('orders.exchange_items_label')</p>
                            <div class="border border-gray-200 rounded-xl p-3 space-y-2">
                                <div class="flex gap-2">
                                    <input x-model="searchQ" @input.debounce.400ms="searchProducts()" type="text" placeholder="@lang('orders.exchange_search_hint')" class="flex-1 border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
                                    <button type="button" @click="searchProducts()" class="px-3 py-1.5 bg-purple-600 text-white rounded-lg text-sm">@lang('orders.search')</button>
                                </div>
                                <div x-show="searchResults.length" class="space-y-1.5 max-h-40 overflow-y-auto">
                                    <template x-for="p in searchResults" :key="p.id">
                                        <button type="button" @click="addExchange(p)" class="w-full flex items-center justify-between bg-gray-50 hover:bg-purple-50 rounded-lg px-3 py-2 text-sm">
                                            <span class="font-medium truncate" x-text="p.name"></span>
                                            <span class="text-purple-700 font-semibold shrink-0 ml-2" x-text="formatMoney(p.price)"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            {{-- Selected replacement items (Alpine) --}}
                            <div class="space-y-2">
                                <template x-for="(x, i) in exchangeCart" :key="x.key">
                                    <div class="flex items-center gap-2 bg-blue-50 rounded-lg p-2">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-800 truncate" x-text="x.name"></p>
                                            <p class="text-xs text-gray-500" x-text="'৳' + formatMoney(x.price)"></p>
                                        </div>
                                        <input type="number" min="1" x-model="x.qty" class="w-16 border border-gray-300 rounded-lg px-2 py-1 text-sm">
                                        <button type="button" @click="exchangeCart.splice(i, 1)" class="text-red-500 text-sm">✕</button>
                                    </div>
                                </template>
                            </div>

                            {{-- Exchange summary --}}
                            <div class="bg-gray-50 rounded-xl p-3 space-y-1 text-sm">
                                <div class="flex justify-between"><span class="text-gray-600">@lang('orders.exchange_returned_value')</span><span class="font-semibold" x-text="formatMoney(returnedValue)"></span></div>
                                <div class="flex justify-between"><span class="text-gray-600">@lang('orders.exchange_new_value')</span><span class="font-semibold" x-text="formatMoney(newValue)"></span></div>
                                <div class="flex justify-between border-t border-gray-200 pt-1 font-bold">
                                    <span>@lang('orders.exchange_difference')</span>
                                    <span :class="difference >= 0 ? 'text-green-600' : 'text-red-600'" x-text="(difference >= 0 ? '+' : '-') + formatMoney(Math.abs(difference))"></span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs text-gray-500 mb-1">@lang('orders.refund_method')</label>
                                <select name="method" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
                                    @foreach($paymentMethods as $m)
                                        <option value="{{ $m }}" {{ $order->payment_method === $m ? 'selected' : '' }}>{{ __("orders.method_{$m}") }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">@lang('orders.return_reason')</label>
                                <textarea name="reason" rows="2" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm" placeholder="@lang('orders.reason_placeholder')"></textarea>
                            </div>
                            <button type="submit" class="w-full px-4 py-2.5 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700">🔁 @lang('orders.process_exchange')</button>
                        </form>
                    </div>
                    @endif

                    {{-- Return history --}}
                    @if($order->returns->count())
                    <div class="bg-white rounded-2xl p-6 shadow-sm">
                        <h3 class="font-bold text-gray-900 mb-4">@lang('orders.return_history')</h3>
                        <div class="space-y-3">
                            @foreach($order->returns as $r)
                            <div class="border {{ $r->isExchange() ? 'border-blue-200 bg-blue-50' : 'border-amber-200 bg-amber-50' }} rounded-xl p-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-bold text-gray-900">#{{ $r->return_number }}</span>
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $r->isExchange() ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700' }}">{{ $r->type_label }}</span>
                                    </div>
                                    <span class="text-sm font-bold text-red-600">-৳{{ number_format($r->amount, 2) }}</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">{{ $r->created_at->format('d M, Y H:i') }} · {{ ucfirst($r->method) }}</p>
                                @if($r->reason)<p class="text-xs text-gray-600 mt-1">{{ $r->reason }}</p>@endif
                                @if($r->items->count())
                                <div class="mt-2 space-y-1">
                                    @foreach($r->items as $ri)
                                    <p class="text-xs text-gray-600">• {{ $ri->name }} × {{ $ri->quantity }}</p>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
