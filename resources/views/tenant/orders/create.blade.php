@extends('layouts.tenant')

@section('title', __('orders.new_order').' - SocialBoost AI')

@push('styles')
<style>
    .pos-scroll::-webkit-scrollbar { width: 6px; height: 6px; }
    .pos-scroll::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 3px; }
</style>
@endpush

@section('content')
<div x-data="createOrderApp()" x-init="init()" class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="{{ route('orders.index') }}" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">@lang('orders.new_title')</h1>
                        <p class="text-gray-600">@lang('orders.new_subtitle')</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-purple-50 text-purple-700 text-sm font-medium">🛍️ @lang('orders.manual_create')</span>
                </div>
            </div>
        </div>
    </div>

    @if(session('error'))
        <div class="max-w-7xl mx-auto mt-4 px-4">
            <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">{{ session('error') }}</div>
        </div>
    @endif

    @if($errors->any())
        <div class="max-w-7xl mx-auto mt-4 px-4">
            <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col lg:flex-row gap-4">

        {{-- ============ LEFT: Products ============ --}}
        <div class="flex-1 flex flex-col bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-4 border-b border-gray-200 space-y-3">
                <div class="flex gap-2">
                    <div class="relative flex-1">
                        <input type="text" :value="search" @input="search = $event.target.value; onSearchInput()"
                               @keydown.enter.prevent="searchNow()"
                               placeholder="@lang('orders.product_search_hint')"
                               class="w-full border border-gray-300 rounded-xl pl-10 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <button @click="searchNow()" class="px-4 py-2.5 bg-purple-600 text-white rounded-xl text-sm font-medium hover:bg-purple-700">@lang('orders.search')</button>
                </div>
                <div class="flex gap-2 overflow-x-auto pos-scroll pb-1">
                    <button @click="setCategory('')" class="shrink-0 px-3 py-1.5 rounded-full text-sm font-medium border transition"
                            :class="categoryId === '' ? 'bg-purple-600 text-white border-purple-600' : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-50'">@lang('orders.all')</button>
                    @foreach($categories as $category)
                        <button @click="setCategory('{{ $category->id }}')" class="shrink-0 px-3 py-1.5 rounded-full text-sm font-medium border transition"
                                :class="categoryId === '{{ $category->id }}' ? 'bg-purple-600 text-white border-purple-600' : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-50'">{{ $category->name }}</button>
                    @endforeach
                </div>
            </div>

            <div class="flex-1 p-4 overflow-y-auto pos-scroll max-h-[calc(100vh-300px)] lg:max-h-none">
                <div x-show="loading" class="text-center py-10 text-gray-500">@lang('orders.loading')</div>
                <div x-show="!loading && products.length === 0" class="text-center py-10 text-gray-500">@lang('orders.no_products_found')</div>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-3">
                    <template x-for="p in products" :key="p.id">
                        <button @click="addProduct(p)" class="text-left bg-white border border-gray-200 rounded-xl p-3 hover:border-purple-400 hover:shadow-md transition"
                                :class="p.stock <= 0 ? 'opacity-50' : ''">
                            <div class="h-24 flex items-center justify-center bg-gray-100 rounded-lg overflow-hidden mb-2">
                                <template x-if="p.image"><img :src="p.image" :alt="p.name" class="w-full h-full object-cover"></template>
                                <template x-if="!p.image">
                                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </template>
                            </div>
                            <div class="text-sm font-semibold text-gray-800 line-clamp-2 leading-snug min-h-[2.5rem]" x-text="p.name"></div>
                            <div class="mt-1 flex items-center justify-between">
                                <span class="text-purple-700 font-bold" x-text="formatMoney(p.price)"></span>
                            </div>
                            <div class="mt-1 text-xs">
                                <span class="text-gray-500" x-text="p.has_variants ? (p.variants.length + ' @lang('orders.variants')') : ('@lang('orders.stock') ' + p.stock)"></span>
                            </div>
                        </button>
                    </template>
                </div>

                <div class="text-center mt-4">
                    <button x-show="nextPage" @click="loadProducts(false)" :disabled="loading"
                            class="px-4 py-2 text-sm border border-gray-300 rounded-xl text-gray-600 hover:bg-gray-50 disabled:opacity-50">@lang('orders.load_more')</button>
                </div>
            </div>
        </div>

        {{-- ============ RIGHT: Cart + details ============ --}}
        <div class="w-full lg:w-[600px] flex flex-col bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden lg:sticky lg:top-4 lg:self-start lg:max-h-[calc(100vh-2rem)]">
            <div class="p-4 border-b border-gray-200 flex items-center justify-between bg-purple-50 shrink-0">
                <h2 class="text-lg font-bold text-gray-900">📦 @lang('orders.new_order')</h2>
                <span class="text-sm text-gray-500" x-text="cart.length + ' @lang('orders.items')'"></span>
            </div>

            <form method="POST" action="{{ route('orders.store') }}" id="create-order-form" class="flex-1 flex flex-col overflow-hidden">
                @csrf
                <input type="hidden" name="items_json" id="items-json">
                <input type="hidden" name="customer_id" :value="customerId">
                <input type="hidden" name="customer_name" :value="customerName">
                <input type="hidden" name="customer_phone" :value="customerPhone">

                <div class="flex-1 overflow-y-auto pos-scroll">
                    {{-- Cart --}}
                    <div class="p-4 space-y-3 min-h-[120px]">
                        <div x-show="!cart.length" class="flex items-center justify-center text-gray-400 text-sm py-8">
                            @lang('orders.empty_cart_hint')
                        </div>
                        <template x-for="(item, index) in cart" :key="item.key">
                            <div class="flex items-center gap-3 bg-gray-50 rounded-xl p-3">
                                <div class="w-10 h-10 shrink-0 rounded-lg overflow-hidden bg-white border border-gray-200">
                                    <template x-if="item.image"><img :src="item.image" :alt="item.name" class="w-full h-full object-cover"></template>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-800 truncate" x-text="item.name"></p>
                                    <p class="text-xs text-gray-500">@lang('orders.unit_price'): <input type="number" step="0.01" min="0" x-model="item.price" class="w-20 border border-gray-300 rounded-lg px-1.5 py-0.5 text-xs"> </p>
                                </div>
                                <div class="flex items-center gap-1">
                                    <button type="button" @click="changeQty(index, -1)" class="w-7 h-7 rounded-lg bg-white border border-gray-300 text-gray-600 font-bold hover:bg-gray-100">−</button>
                                    <span class="w-8 text-center text-sm font-semibold" x-text="item.quantity"></span>
                                    <button type="button" @click="changeQty(index, 1)" class="w-7 h-7 rounded-lg bg-white border border-gray-300 text-gray-600 font-bold hover:bg-gray-100">+</button>
                                </div>
                                <div class="text-right w-20">
                                    <p class="text-sm font-bold text-gray-900" x-text="formatMoney(item.price * item.quantity)"></p>
                                    <button type="button" @click="removeItem(index)" class="text-xs text-red-500 hover:text-red-700">✕</button>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Warehouse --}}
                    <div class="px-4 pb-3 border-t border-gray-200 pt-3 space-y-2 shrink-0">
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-1">🏭 @lang('orders.warehouse')</p>
                        <select x-model="warehouseId" name="warehouse_id" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
                            <option value="">@lang('orders.auto_warehouse')</option>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                            @endforeach
                        </select>
                        <p x-show="warehouseId" class="text-[10px] text-gray-400 mt-1">📦 @lang('orders.stock_from_warehouse')</p>
                    </div>

                    {{-- Customer --}}
                    <div class="px-4 pb-3 space-y-2 border-t border-gray-200 pt-3 shrink-0">
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-1">👤 @lang('orders.customer')</p>
                        <div class="grid grid-cols-1 gap-2">
                            <select x-model="customerId" @change="onCustomerChange()" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
                                <option value="">@lang('orders.new_customer')</option>
                                <template x-for="c in customers" :key="c.id">
                                    <option :value="c.id" x-text="c.name + (c.phone ? ' (' + c.phone + ')' : '')"></option>
                                </template>
                            </select>
                            <template x-if="customerId === ''">
                                <div class="grid grid-cols-2 gap-2">
                                    <input x-model="customerName" type="text" placeholder="@lang('orders.name') *" class="border border-gray-300 rounded-xl px-3 py-2 text-sm">
                                    <input x-model="customerPhone" type="text" placeholder="@lang('orders.phone') *" class="border border-gray-300 rounded-xl px-3 py-2 text-sm">
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Shipping --}}
                    <div class="px-4 pb-3 border-t border-gray-200 pt-3 space-y-2 shrink-0">
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-1">🚚 @lang('orders.shipping_info')</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <input type="text" name="address" x-model="shippingAddress" placeholder="@lang('orders.address')" class="border border-gray-300 rounded-xl px-3 py-2 text-sm sm:col-span-2">
                            <input type="text" name="city" x-model="shippingCity" placeholder="@lang('orders.city')" class="border border-gray-300 rounded-xl px-3 py-2 text-sm">
                            <input type="text" name="district" x-model="shippingDistrict" placeholder="@lang('orders.district')" class="border border-gray-300 rounded-xl px-3 py-2 text-sm">
                            <input type="text" name="carrier" placeholder="@lang('orders.carrier')" class="border border-gray-300 rounded-xl px-3 py-2 text-sm">
                            <input type="date" name="estimated_delivery" class="border border-gray-300 rounded-xl px-3 py-2 text-sm">
                        </div>
                    </div>

                    {{-- Financials --}}
                    <div class="px-4 pb-3 border-t border-gray-200 pt-3 space-y-2 shrink-0">
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-1">💰 @lang('orders.payment_info')</p>

                        {{-- Quick Payment Mode (auto-detected from payment rows) --}}
                        <div class="flex gap-1.5 mb-2">
                            <div class="flex-1 text-center text-xs px-2 py-1.5 rounded-lg border font-medium transition"
                                :class="paymentMode === 'paid' ? 'border-green-600 text-green-700 bg-green-50' : paymentMode === 'partial' ? 'border-yellow-600 text-yellow-700 bg-yellow-50' : 'border-red-600 text-red-700 bg-red-50'">
                                <span x-show="paymentMode === 'paid'">✅ @lang('orders.mode_paid')</span>
                                <span x-show="paymentMode === 'partial'">⚡ @lang('orders.mode_partial')</span>
                                <span x-show="paymentMode === 'due'">🔴 @lang('orders.mode_due')</span>
                            </div>
                        </div>

                        {{-- Live Calculation --}}
                        <div class="bg-gray-50 rounded-xl p-2.5 space-y-1 text-xs">
                            <div class="flex justify-between text-gray-600">
                                <span>@lang('orders.subtotal')</span>
                                <span x-text="formatMoney(subtotal)"></span>
                            </div>
                            <div x-show="shippingCost > 0" class="flex justify-between text-gray-600">
                                <span>@lang('orders.shipping_cost')</span>
                                <span x-text="'+ ' + formatMoney(shippingCost)"></span>
                            </div>
                            <div x-show="taxRate > 0" class="flex justify-between text-gray-600">
                                <span>@lang('orders.tax') (<span x-text="taxRate"></span>%)</span>
                                <span x-text="'+ ' + formatMoney(taxAmount)"></span>
                            </div>
                            <div x-show="discountAmount > 0" class="flex justify-between text-green-600">
                                <span>@lang('orders.discount')</span>
                                <span x-text="'- ' + formatMoney(discountAmount)"></span>
                            </div>
                            <div x-show="shippingCost > 0 || taxRate > 0 || discountAmount > 0" class="flex justify-between text-gray-800 font-bold border-t border-gray-200 pt-1">
                                <span>@lang('orders.grand_total')</span>
                                <span x-text="formatMoney(grandTotal)"></span>
                            </div>
                            <div x-show="totalPaid > 0" class="flex justify-between text-green-600 font-medium">
                                <span>@lang('orders.paid_amount')</span>
                                <span x-text="'-' + formatMoney(totalPaid)"></span>
                            </div>
                            <div x-show="totalPaid > 0 && paymentMode !== 'paid'" class="flex justify-between text-red-600 font-bold border-t border-gray-200 pt-1">
                                <span>@lang('orders.due_amount')</span>
                                <span x-text="formatMoney(Math.max(0, grandTotal - totalPaid))"></span>
                            </div>
                            <div x-show="totalPaid <= 0" class="flex justify-between text-red-600 font-bold border-t border-gray-200 pt-1">
                                <span>@lang('orders.due_amount')</span>
                                <span x-text="formatMoney(grandTotal)"></span>
                            </div>
                            <div x-show="paymentMode === 'paid'" class="flex justify-between text-green-600 font-bold border-t border-gray-200 pt-1">
                                <span>@lang('orders.status')</span>
                                <span>✅ @lang('orders.mode_paid')</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">@lang('orders.shipping_cost')</label>
                                <input type="number" x-model.number="shippingCost" name="shipping_cost" min="0" step="0.01" value="0" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">@lang('orders.discount')</label>
                                <input type="number" x-model.number="discountAmount" name="discount" min="0" step="0.01" value="0" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">@lang('orders.tax') (%)</label>
                                <input type="number" x-model.number="taxRate" name="tax" min="0" max="100" step="0.01" value="0" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">@lang('orders.status')</label>
                                <select name="status" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
                                    <option value="pending">@lang('orders.pending')</option>
                                    <option value="processing">@lang('orders.processing')</option>
                                    <option value="shipped">@lang('orders.shipped')</option>
                                    <option value="delivered">@lang('orders.delivered')</option>
                                </select>
                            </div>
                        </div>

                        {{-- Auto payment_status + payment_method (hidden) --}}
                        <input type="hidden" name="payment_status" :value="paymentMode === 'due' ? 'pending' : paymentMode">
                        <input type="hidden" name="payment_method" :value="payments.length > 0 ? payments[0].method : ''">
                        <input type="hidden" name="received_amount" :value="totalPaid">

                        {{-- Payment rows (optional) --}}
                        <div class="mt-3 pt-3 border-t border-gray-200">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-semibold text-gray-500 uppercase">@lang('orders.payment_method')</span>
                                <button type="button" @click="addPaymentRow()" class="text-xs px-2 py-1 rounded-lg border border-purple-300 text-purple-600 hover:bg-purple-50">+ @lang('orders.add_payment')</button>
                            </div>
                            <div class="flex flex-wrap gap-1.5 mb-2">
                                @foreach($paymentAccounts as $m)
                                    <button type="button" @click="addPaymentRow('{{ $m->code }}')"
                                        class="text-xs px-2.5 py-1 rounded-lg border font-medium transition"
                                        :class="paymentMethodActive('{{ $m->code }}') ? 'border-purple-600 text-purple-700 bg-purple-50' : 'border-gray-300 text-gray-600 hover:border-purple-400'">{{ $m['name'] }}</button>
                                @endforeach
                            </div>
                            <div class="space-y-1.5 max-h-28 overflow-y-auto">
                                <template x-for="(pay, idx) in payments" :key="idx">
                                    <div class="flex gap-2 items-center">
                                        <select x-model="pay.method" class="w-28 border border-gray-300 rounded-lg px-2 py-1.5 text-xs">
                                            @foreach($paymentAccounts as $m)
                                                <option value="{{ $m['code'] }}">{{ $m['name'] }}</option>
                                            @endforeach
                                        </select>
                                        <input type="number" x-model.number="pay.amount" min="0" step="0.01" placeholder="৳" class="flex-1 border border-gray-300 rounded-lg px-2 py-1.5 text-xs">
                                        <button type="button" @click="payments.splice(idx, 1)" class="text-red-400 hover:text-red-600 text-xs">✕</button>
                                    </div>
                                </template>
                                <div x-show="payments.length === 0" class="text-xs text-gray-400 py-1">@lang('orders.no_payment_hint')</div>
                            </div>
                            <input type="hidden" name="payments_json" id="payments-json">
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div class="px-4 pb-4 border-t border-gray-200 pt-3 shrink-0">
                        <label class="block text-xs text-gray-500 mb-1">@lang('orders.notes')</label>
                        <textarea name="notes" rows="2" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm" placeholder="@lang('orders.notes_hint')"></textarea>
                    </div>
                </div>

                {{-- Totals + submit --}}
                <div class="p-4 border-t border-gray-200 space-y-2 bg-gray-50 shrink-0">
                    <div class="flex justify-between text-sm text-gray-600"><span>@lang('orders.subtotal')</span><span x-text="formatMoney(subtotal)"></span></div>
                    <div x-show="shippingCost > 0" class="flex justify-between text-sm text-gray-600"><span>@lang('orders.shipping_cost')</span><span x-text="formatMoney(shippingCost)"></span></div>
                    <div x-show="taxRate > 0" class="flex justify-between text-sm text-gray-600"><span>@lang('orders.tax') (<span x-text="taxRate"></span>%)</span><span x-text="formatMoney(taxAmount)"></span></div>
                    <div x-show="discountAmount > 0" class="flex justify-between text-sm text-green-600"><span>@lang('orders.discount')</span><span x-text="'-' + formatMoney(discountAmount)"></span></div>
                    <div class="flex justify-between text-lg font-bold text-gray-900"><span>@lang('orders.grand_total')</span><span x-text="formatMoney(grandTotal)"></span></div>
                    <div x-show="paymentMode === 'due'" class="flex justify-between text-sm text-red-600 font-medium">
                        <span>@lang('orders.due_amount')</span>
                        <span x-text="formatMoney(grandTotal)"></span>
                    </div>
                    <div x-show="paymentMode === 'partial'" class="flex justify-between text-sm text-yellow-600 font-medium">
                        <span>@lang('orders.due_amount')</span>
                        <span x-text="formatMoney(Math.max(0, grandTotal - totalPaid))"></span>
                    </div>
                    <button type="submit" @click="prepareSubmit()" :disabled="!cart.length"
                            class="w-full px-4 py-3 rounded-xl text-white font-semibold disabled:opacity-50 transition"
                            :class="paymentMode === 'due' ? 'bg-red-600 hover:bg-red-700' : paymentMode === 'partial' ? 'bg-yellow-600 hover:bg-yellow-700' : 'bg-green-600 hover:bg-green-700'">
                        <span x-show="paymentMode === 'due'">🔴 @lang('orders.create_due_order')</span>
                        <span x-show="paymentMode === 'partial'">⚡ @lang('orders.create_partial_order')</span>
                        <span x-show="paymentMode === 'paid'">✅ @lang('orders.create_order')</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Variant modal --}}
    <div x-show="variantModalOpen" x-cloak x-transition.opacity class="fixed inset-0 z-[70] flex items-center justify-center bg-black/50 p-4" @keydown.escape.window="variantModalOpen = false">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-xl overflow-hidden" @click.outside="variantModalOpen = false">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <h3 class="text-lg font-bold text-gray-900 truncate" x-text="variantProduct ? variantProduct.name : ''"></h3>
                    <p class="text-xs text-gray-500 mt-0.5">@lang('orders.variant_hint')</p>
                </div>
                <button @click="variantModalOpen = false" class="shrink-0 w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:text-gray-600 hover:bg-gray-100">✕</button>
            </div>
            <div class="p-4 space-y-2 max-h-[50vh] overflow-y-auto pos-scroll">
                <template x-for="(s, i) in variantSelections" :key="s.variant.id">
                    <div class="flex items-center gap-3 bg-gray-50 rounded-xl p-3" :class="s.qty > 0 ? 'ring-2 ring-purple-400' : ''">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-800 truncate" x-text="s.variant.name"></p>
                            <p class="text-xs text-gray-500 mt-0.5" x-text="formatMoney(s.variant.price) + ' • @lang('orders.stock') ' + s.variant.stock"></p>
                        </div>
                        <div class="flex items-center gap-1">
                            <button @click="changeVariantQty(i, -1)" class="w-8 h-8 rounded-lg bg-white border border-gray-300 text-gray-600 font-bold hover:bg-gray-100">−</button>
                            <span class="w-8 text-center text-sm font-semibold" x-text="s.qty"></span>
                            <button @click="changeVariantQty(i, 1)" class="w-8 h-8 rounded-lg bg-white border border-gray-300 text-gray-600 font-bold hover:bg-gray-100" :disabled="s.qty >= s.variant.stock">+</button>
                        </div>
                        <span class="w-20 text-right text-sm font-bold text-gray-900" x-text="s.qty > 0 ? formatMoney(s.variant.price * s.qty) : ''"></span>
                    </div>
                </template>
            </div>
            <div class="px-5 py-4 border-t border-gray-100 flex items-center justify-between gap-3">
                <div class="text-sm">
                    <span class="text-gray-500">@lang('orders.total'):</span>
                    <span class="font-bold text-gray-900 ml-1" x-text="formatMoney(variantSelectionTotal)"></span>
                </div>
                <div class="flex gap-2">
                    <button @click="variantModalOpen = false" class="px-4 py-2 border border-gray-300 rounded-xl text-sm text-gray-600 hover:bg-gray-50">@lang('orders.cancel')</button>
                    <button @click="addVariantSelections()" :disabled="variantSelectionCount === 0" class="px-5 py-2 bg-purple-600 text-white rounded-xl text-sm font-semibold hover:bg-purple-700 disabled:opacity-40">@lang('orders.add_to_cart')</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function createOrderApp() {
    return {
        products: [],
        nextPage: null,
        search: '',
        searchTimer: null,
        categoryId: '',
        loading: false,
        cart: [],
        variantModalOpen: false,
        variantProduct: null,
        variantSelections: [],
        customerId: '',
        customerName: '',
        customerPhone: '',
        shippingAddress: '',
        shippingCity: '',
        shippingDistrict: '',
        warehouseId: '',
        shippingCost: 0,
        discountAmount: 0,
        taxRate: 0,
        payments: [],
        customers: {!! $customers->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'phone' => $c->phone, 'addresses' => $c->addresses->take(1)->map(fn($a) => ['address' => $a->address, 'city' => $a->city, 'district' => $a->district])->values()])->toJson() !!},
        symbol: '৳',

        get subtotal() {
            return this.cart.reduce((s, i) => s + (Number(i.price) || 0) * (i.quantity || 0), 0);
        },
        get grandTotal() {
            return Math.max(0, this.subtotal + (this.shippingCost || 0) + this.taxAmount - (this.discountAmount || 0));
        },
        get taxAmount() {
            return Math.round((this.subtotal * (this.taxRate || 0) / 100) * 100) / 100;
        },
        get totalPaid() {
            return this.payments.reduce((s, p) => s + (Number(p.amount) || 0), 0);
        },
        get paymentMode() {
            if (this.totalPaid <= 0) return 'due';
            if (this.totalPaid >= this.subtotal) return 'paid';
            return 'partial';
        },

        get variantSelectionTotal() {
            return this.variantSelections.reduce((sum, s) => sum + (s.qty > 0 ? s.qty * s.variant.price : 0), 0);
        },
        get variantSelectionCount() {
            return this.variantSelections.reduce((sum, s) => sum + s.qty, 0);
        },

        init() {
            this.warehouseId = {!! json_encode(\App\Models\PosSetting::current()->default_warehouse_id) !!} || '';
            this.loadProducts(true);
            this.$watch('warehouseId', () => this.loadProducts(true));
        },

        formatMoney(v) {
            return this.symbol + Number(v || 0).toFixed(2);
        },

        async loadProducts(reset) {
            if (reset) { this.products = []; this.nextPage = 1; }
            if (!this.nextPage) return;
            this.loading = true;
            const params = new URLSearchParams({ page: this.nextPage });
            if (this.search) params.set('search', this.search);
            if (this.categoryId) params.set('category_id', this.categoryId);
            if (this.warehouseId) params.set('warehouse_id', this.warehouseId);
            try {
                const res = await fetch('{{ route('orders.products') }}?' + params.toString(), { headers: { 'Accept': 'application/json' } });
                const json = await res.json();
                this.products = reset ? json.data : this.products.concat(json.data);
                this.nextPage = json.next_page;
            } catch (e) {
                console.error(e);
            } finally {
                this.loading = false;
            }
        },

        setCategory(id) {
            this.categoryId = id;
            this.loadProducts(true);
        },

        onSearchInput() {
            clearTimeout(this.searchTimer);
            this.searchTimer = setTimeout(() => this.searchNow(), 350);
        },

        async searchNow() {
            clearTimeout(this.searchTimer);
            await this.loadProducts(true);
        },

        addProduct(p) {
            if (p.stock <= 0) { alert('@lang('orders.stock_empty')'); return; }
            if (p.has_variants && p.variants.length) {
                this.variantProduct = p;
                this.variantSelections = p.variants.map(v => ({ variant: v, qty: 0 }));
                this.variantModalOpen = true;
                return;
            }
            this.addSimpleToCart(p);
        },

        addSimpleToCart(p) {
            const existing = this.cart.find(i => i.product_id === p.id && !i.variant_id);
            if (existing) {
                if (existing.quantity < p.stock) existing.quantity++;
                else alert('@lang('orders.stock_empty')');
            } else {
                this.cart.push({
                    key: Date.now() + Math.random(),
                    product_id: p.id,
                    variant_id: null,
                    name: p.name,
                    sku: p.sku,
                    price: Number(p.price),
                    quantity: 1,
                    stock: p.stock,
                    image: p.image,
                });
            }
        },

        changeVariantQty(index, delta) {
            const s = this.variantSelections[index];
            const next = s.qty + delta;
            if (next < 0) return;
            if (next <= s.variant.stock) s.qty = next;
        },

        addVariantSelections() {
            const p = this.variantProduct;
            if (!p) return;
            this.variantSelections.forEach(s => {
                if (s.qty > 0) {
                    const existing = this.cart.find(i => i.variant_id === s.variant.id);
                    if (existing) {
                        existing.quantity = Math.min(existing.quantity + s.qty, s.variant.stock);
                    } else {
                        this.cart.push({
                            key: Date.now() + Math.random(),
                            product_id: p.id,
                            variant_id: s.variant.id,
                            name: p.name + ' - ' + s.variant.name,
                            sku: s.variant.sku,
                            price: Number(s.variant.price),
                            quantity: s.qty,
                            stock: s.variant.stock,
                            image: p.image,
                        });
                    }
                }
            });
            this.variantModalOpen = false;
        },

        changeQty(index, delta) {
            const item = this.cart[index];
            const next = item.quantity + delta;
            if (next < 1) { this.cart.splice(index, 1); return; }
            if (next <= item.stock) item.quantity = next;
        },

        removeItem(index) {
            this.cart.splice(index, 1);
        },

        paymentMethodActive(code) {
            return this.payments.some(p => p.method === code);
        },

        addPaymentRow(method) {
            const m = method || (this.payments.length > 0 ? this.payments[0].method : 'cash');
            this.payments.push({ method: m, amount: 0 });
        },

        onCustomerChange() {
            const c = this.customers.find(c => String(c.id) === String(this.customerId));
            if (c) {
                this.customerName = c.name;
                this.customerPhone = c.phone;
                if (c.addresses && c.addresses.length > 0) {
                    const a = c.addresses[0];
                    this.shippingAddress = a.address || '';
                    this.shippingCity = a.city || '';
                    this.shippingDistrict = a.district || '';
                }
            } else {
                this.customerName = '';
                this.customerPhone = '';
                this.shippingAddress = '';
                this.shippingCity = '';
                this.shippingDistrict = '';
            }
        },

        prepareSubmit() {
            document.getElementById('items-json').value = JSON.stringify(this.cart.map(i => ({
                product_id: i.product_id,
                variant_id: i.variant_id || null,
                name: i.name,
                quantity: i.quantity,
                unit_price: Number(i.price) || 0,
            })));
            document.getElementById('payments-json').value = JSON.stringify(this.payments.filter(p => p.amount > 0));
        },
    };
}
</script>
@endpush