@extends('layouts.tenant')

@section('title', 'POS - SocialBoost AI')

@push('styles')
<style>
    .pos-scroll::-webkit-scrollbar { width: 6px; height: 6px; }
    .pos-scroll::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 3px; }
    .pos-btn-active { @apply bg-purple-600 text-white border-purple-600; }
</style>
@endpush

@section('content')
<div
    x-data="posApp()"
    x-init="init()"
    class="min-h-screen bg-gray-50"
>
    {{-- Register banner --}}
    <div x-show="!openSession" class="bg-amber-50 border-b border-amber-200">
        <div class="max-w-full mx-auto px-4 py-2 flex items-center justify-between">
            <p class="text-sm text-amber-800 font-medium">ℹ️ কোনো রেজিস্টার সেশন খোলা নেই — সেল করা যাবে, তবে ক্যাশ কার্যক্রম ট্র্যাক করতে সেশন খোলার পরামর্শ দেওয়া হয়।</p>
            <button @click="openSessionModal = true" class="text-sm px-3 py-1 bg-amber-600 text-white rounded-lg hover:bg-amber-700">রেজিস্টার খুলুন</button>
        </div>
    </div>
    <div x-show="openSession" class="bg-green-50 border-b border-green-200">
        <div class="max-w-full mx-auto px-4 py-2 flex items-center justify-between">
            <p class="text-sm text-green-800 font-medium">
                ✅ রেজিস্টার চালু — সেশন #<span x-text="openSession ? openSession.id : ''"></span>
                <span class="text-gray-600">· গুদাম: <span x-text="openSession && openSession.warehouse_name ? openSession.warehouse_name : 'All/Default'"></span></span>
                <span class="text-gray-600">(খোলা: <span x-text="openSession ? openSession.opened_at_formatted : ''"></span>)</span>
            </p>
            <div class="flex items-center gap-2">
                <a href="{{ route('pos.sessions.index') }}" class="text-sm text-green-700 underline">সেশন ম্যানেজ করুন</a>
                <button @click="openCloseSessionModal = true" x-show="canCloseSession" class="text-sm px-3 py-1 bg-red-600 text-white rounded-lg hover:bg-red-700">সেশন বন্ধ করুন</button>
            </div>
        </div>
    </div>

    {{-- Hold flash --}}
    <template x-if="flash.resume_items">
        <div class="bg-blue-50 border-b border-blue-200 px-4 py-2 text-sm text-blue-800">
            💾 হোল্ড করা অর্ডার পুনরায় লোড হয়েছে — চেকআউট সম্পন্ন করলে হোল্ড অর্ডার মুছে যাবে।
        </div>
    </template>

    <div class="flex flex-col lg:flex-row gap-4 p-4">

        {{-- ============ LEFT: Products ============ --}}
        <div class="flex-1 flex flex-col bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            {{-- Search + categories --}}
            <div class="p-4 border-b border-gray-200 space-y-3">
                <div class="flex items-center justify-between gap-3">
                    <label class="text-xs font-semibold text-gray-500 uppercase">ওয়ারহাউস</label>
                    <div class="relative" x-cloak>
                        <select
                            x-model="activeWarehouseId"
                            @change="loadProducts(true)"
                            :disabled="openSession ? true : false"
                            class="w-56 border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 disabled:bg-gray-100 disabled:text-gray-500"
                        >
                            <option value="">সব ওয়ারহাউজ / ডিফল্ট</option>
                            <template x-for="wh in warehouseOptions" :key="wh.id">
                                <option :value="wh.id" x-text="wh.name"></option>
                            </template>
                        </select>
                        <p x-show="openSession" class="text-[10px] text-gray-400 mt-0.5">সেশন খোলার সময় গুদাম সিলেক্ট করা হয়েছে</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <div class="relative flex-1">
                        <input
                            type="text"
                            :value="search"
                            @input="search = $event.target.value; onSearchInput()"
                            @keydown.enter.prevent="searchNow()"
                            placeholder="পণ্য খুঁজুন (নাম / SKU / বারকোড)..."
                            class="w-full border border-gray-300 rounded-xl pl-10 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                        >
                        <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <button @click="searchNow()" class="px-4 py-2.5 bg-purple-600 text-white rounded-xl text-sm font-medium hover:bg-purple-700">খুঁজুন</button>
                </div>
                <div class="flex gap-2 overflow-x-auto pos-scroll pb-1">
                    <button
                        @click="setCategory('')"
                        class="shrink-0 px-3 py-1.5 rounded-full text-sm font-medium border transition"
                        :class="categoryId === '' ? 'bg-purple-600 text-white border-purple-600' : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-50'"
                    >সব</button>
                    @foreach($categories as $category)
                        <button
                            @click="setCategory('{{ $category->id }}')"
                            class="shrink-0 px-3 py-1.5 rounded-full text-sm font-medium border transition"
                            :class="categoryId === '{{ $category->id }}' ? 'bg-purple-600 text-white border-purple-600' : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-50'"
                        >{{ $category->name }}</button>
                    @endforeach
                </div>
            </div>

            {{-- Product grid --}}
            <div class="flex-1 p-4 overflow-y-auto pos-scroll max-h-[calc(100vh-300px)] lg:max-h-none">
                <div x-show="loading" class="text-center py-10 text-gray-500">লোড হচ্ছে...</div>
                <div x-show="!loading && products.length === 0" class="text-center py-10 text-gray-500">কোনো পণ্য পাওয়া যায়নি</div>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-3">
                    <template x-for="p in products" :key="p.id">
                        <button
                            @click="addProduct(p)"
                            class="text-left bg-white border border-gray-200 rounded-xl p-3 hover:border-purple-400 hover:shadow-md transition"
                            :class="p.stock <= 0 ? 'opacity-50' : ''"
                        >
                            <div class="h-24 flex items-center justify-center bg-gray-100 rounded-lg overflow-hidden mb-2">
                                <template x-if="p.image">
                                    <img :src="p.image" :alt="p.name" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!p.image">
                                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </template>
                            </div>
                            <div class="text-sm font-semibold text-gray-800 line-clamp-2 leading-snug min-h-[2.5rem]" x-text="p.name"></div>
                            <div class="mt-1 flex items-center justify-between">
                                <span class="text-purple-700 font-bold" x-text="formatMoney(p.price)"></span>
                            </div>
                            <div class="mt-1 text-xs">
                                <span class="text-gray-500" x-text="p.has_variants ? (p.variants.length + ' variants') : ('স্টক: ' + p.stock)"></span>
                            </div>
                            <div x-show="p.stock <= 0" class="mt-1 text-xs font-medium text-red-600">স্টক শেষ</div>
                        </button>
                    </template>
                </div>

                <div class="text-center mt-4">
                    <button
                        x-show="nextPage"
                        @click="loadProducts(false)"
                        :disabled="loading"
                        class="px-4 py-2 text-sm border border-gray-300 rounded-xl text-gray-600 hover:bg-gray-50 disabled:opacity-50"
                    >আরও লোড করুন</button>
                </div>
            </div>
        </div>

        {{-- ============ RIGHT: Cart ============ --}}
        <div class="w-full lg:w-[560px] flex flex-col bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden lg:sticky lg:top-16 lg:self-start lg:max-h-[calc(100vh-4rem)]">
            <div class="p-4 border-b border-gray-200 flex items-center justify-between bg-purple-50 shrink-0">
                <h2 class="text-lg font-bold text-gray-900">🧾 বিক্রয় কার্ট</h2>
                <span class="text-sm text-gray-500" x-text="cart.length + ' item'"></span>
            </div>

            {{-- Middle: cart + customer + discount + totals + payments (all scrollable) --}}
            <div class="flex-1 overflow-y-auto pos-scroll">
                <div x-ref="cartScroll" class="p-4 space-y-3 min-h-[120px]">
                <div x-show="!cart.length && !holds.length" class="flex items-center justify-center text-gray-400 text-sm py-10">
                    কার্টে কোনো পণ্য নেই — বাম পাশ থেকে পণ্য যুক্ত করুন
                </div>
                <template x-for="(item, index) in cart" :key="item.key">
                    <div class="flex items-center gap-3 bg-gray-50 rounded-xl p-3">
                        <div class="w-10 h-10 shrink-0 rounded-lg overflow-hidden bg-white border border-gray-200">
                            <template x-if="item.image">
                                <img :src="item.image" :alt="item.name" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!item.image">
                                <div class="w-full h-full flex items-center justify-center text-gray-300">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                            </template>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-800 truncate" x-text="item.name"></p>
                            <p class="text-xs text-gray-500" x-text="formatMoney(item.price)"></p>
                            <p class="text-xs text-gray-400" x-text="'স্টক: ' + item.stock"></p>
                        </div>
                        <div class="flex items-center gap-1">
                            <button @click="changeQty(index, -1)" class="w-7 h-7 rounded-lg bg-white border border-gray-300 text-gray-600 font-bold hover:bg-gray-100">−</button>
                            <span class="w-8 text-center text-sm font-semibold" x-text="item.quantity"></span>
                            <button @click="changeQty(index, 1)" class="w-7 h-7 rounded-lg bg-white border border-gray-300 text-gray-600 font-bold hover:bg-gray-100" :disabled="item.quantity >= item.stock">+</button>
                        </div>
                        <div class="text-right w-20">
                            <p class="text-sm font-bold text-gray-900" x-text="formatMoney(item.price * item.quantity)"></p>
                            <button @click="removeItem(index)" class="text-xs text-red-500 hover:text-red-700">remove</button>
                        </div>
                    </div>
                </template>

                {{-- Holds --}}
                <div x-show="holds.length" class="pt-3 border-t border-gray-200">
                    <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Held Orders</p>
                    <div class="space-y-2">
                        <template x-for="h in holds" :key="h.id">
                            <div class="flex items-center justify-between bg-gray-50 rounded-lg px-3 py-2">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-800" x-text="h.order_number"></p>
                                    <p class="text-xs text-gray-500" x-text="h.items_count + ' items · ' + h.customer_phone"></p>
                                </div>
                                <div class="flex gap-2 shrink-0">
                                    <a :href="'{{ route('pos.resume', ['order' => '__ID__']) }}'.replace('__ID__', h.id)" class="text-xs px-3 py-1 bg-blue-600 text-white rounded-lg">Resume</a>
                                    <form method="POST" :action="'{{ route('pos.hold.cancel', ['order' => '__ID__']) }}'.replace('__ID__', h.id)" onsubmit="return confirm('হোল্ড অর্ডার বাতিল করবেন?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-xs px-2 py-1 text-red-500">✕</button>
                                    </form>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Customer --}}
            <div class="px-4 pb-2 shrink-0">
                <select x-model="customerId" @change="onCustomerChange()" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
                    <option value="">+ ওয়াক-ইন কাস্টমার</option>
                    <template x-for="c in customers" :key="c.id">
                        <option :value="c.id" x-text="c.name + ' (' + (c.phone || 'no phone') + ')'"></option>
                    </template>
                </select>
                <template x-if="customerId === ''">
                    <div class="grid grid-cols-2 gap-2 mt-2">
                        <input x-model="customerName" type="text" placeholder="কাস্টমারের নাম" class="border border-gray-300 rounded-xl px-3 py-1.5 text-sm">
                        <input x-model="customerPhone" type="text" placeholder="ফোন নম্বর" class="border border-gray-300 rounded-xl px-3 py-1.5 text-sm">
                    </div>
                </template>
            </div>

            {{-- Discount --}}
            <div class="px-4 pb-2 shrink-0">
                <div class="flex gap-2">
                    <select x-model="discountType" class="border border-gray-300 rounded-xl px-3 py-1.5 text-sm w-28">
                        <option value="">ডিসকাউন্ট</option>
                        <option value="fixed">৳ fixed</option>
                        <option value="percent">% percent</option>
                    </select>
                    <input x-model="discountValue" type="number" min="0" step="0.01" placeholder="0.00" :disabled="!discountType" class="flex-1 border border-gray-300 rounded-xl px-3 py-1.5 text-sm disabled:bg-gray-100">
                </div>
            </div>

            {{-- Totals --}}
            <div class="px-4 py-3 border-t border-gray-200 space-y-1.5 text-sm shrink-0">
                <div class="flex justify-between text-gray-600"><span>Subtotal</span><span x-text="formatMoney(subtotal)"></span></div>
                <div class="flex justify-between text-gray-600"><span>Discount</span><span class="text-red-500" x-text="'- ' + formatMoney(discountAmount)"></span></div>
                <div class="flex justify-between text-gray-600"><span>Tax (<span x-text="taxRateText"></span>)</span><span x-text="formatMoney(taxAmount)"></span></div>
                <div class="flex justify-between text-lg font-bold text-gray-900 pt-1 border-t border-gray-200"><span>Total</span><span x-text="formatMoney(total)"></span></div>
            </div>

            {{-- Payments --}}
            <div class="px-4 pb-3 space-y-2 shrink-0">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-gray-500 uppercase">পেমেন্ট</span>
                    <button @click="addPaymentRow('')" class="text-xs px-3 py-1.5 rounded-lg border border-purple-300 text-purple-600 hover:bg-purple-50">+ আরো পদ্ধতি</button>
                </div>
                <div class="flex flex-wrap gap-2">
                    <template x-for="m in enabledMethods" :key="m.code">
                        <button
                            @click="addPaymentRow(m.code)"
                            class="text-xs px-3 py-1.5 rounded-lg border font-medium transition"
                            :class="methodActive(m.code) ? 'border-purple-600 text-purple-700 bg-purple-50' : 'border-gray-300 text-gray-600 hover:border-purple-400 hover:text-purple-600'"
                            x-text="m.name"
                        ></button>
                    </template>
                </div>

                <div class="max-h-28 overflow-y-auto space-y-2 pr-1">
                    <template x-for="(pay, idx) in payments" :key="idx">
                        <div class="flex gap-2 items-center">
                            <select x-model="pay.method" class="w-28 border border-gray-300 rounded-xl px-2 py-1.5 text-sm">
                                @foreach($paymentAccounts as $method)
                                    <option value="{{ $method->code }}">{{ $method->name }}</option>
                                @endforeach
                            </select>
                            <input x-model="pay.amount" type="number" min="0" step="0.01" placeholder="0.00" class="flex-1 border border-gray-300 rounded-xl px-3 py-1.5 text-sm">
                            <input x-model="pay.reference" type="text" placeholder="ref" class="w-20 border border-gray-300 rounded-xl px-2 py-1.5 text-sm">
                            <button @click="removePayment(idx)" x-show="payments.length > 1" class="text-red-500 text-sm">✕</button>
                        </div>
                    </template>
                </div>

                <div x-show="paidDiff > 0.01" class="flex items-center justify-between text-sm text-amber-600">
                    <span>বাকি: <span x-text="formatMoney(paidDiff)"></span></span>
                    <label class="flex items-center gap-1 text-xs">
                        <input type="checkbox" x-model="allowCredit" class="rounded"> Credit (partial)
                    </label>
                </div>

                <template x-if="cashTotal > 0">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600">Change (cash)</span>
                        <span class="font-bold text-green-600" x-text="formatMoney(changeDue)"></span>
                    </div>
                </template>
            </div>
            </div>

            {{-- Actions --}}
            <div class="p-4 border-t border-gray-200 grid grid-cols-2 gap-2 shrink-0">
                <button
                    @click="holdCart()"
                    :disabled="!cart.length"
                    class="px-4 py-3 rounded-xl border border-amber-400 text-amber-600 font-semibold text-sm hover:bg-amber-50 disabled:opacity-50"
                >💾 Hold</button>
                <button
                    @click="checkout()"
                    :disabled="!cart.length || !canCheckout"
                    class="px-4 py-3 rounded-xl bg-green-600 text-white font-bold hover:bg-green-700 disabled:opacity-50"
                >✅ <span x-text="openSession ? 'Complete Sale' : 'Sale'"></span></button>
            </div>
        </div>
    </div>

    {{-- Open Session Modal --}}
    <div x-show="openSessionModal" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 p-4">
        <div class="bg-white rounded-2xl p-6 w-full max-w-sm shadow-xl">
            <h3 class="text-lg font-bold text-gray-900 mb-4">রেজিস্টার সেশন খুলুন</h3>
            <form method="POST" action="{{ route('pos.sessions.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">ওয়ারহাউস</label>
                    <select name="warehouse_id" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
                        <option value="">ডিফল্ট / প্রথম সক্রিয় গুদাম</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ $defaultWarehouse && $wh->id === $defaultWarehouse->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                        @endforeach
                    </select>
                    <p class="text-[10px] text-gray-400 mt-1">এই সেশনে শুধু এই গুদামের স্টক দেখানো হবে এবং স্টক আউট এখান থেকে হবে।</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">প্রারম্ভিক ক্যাশ (opening cash)</label>
                    <input type="number" name="opening_cash" min="0" step="0.01" value="0" required class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">নোট</label>
                    <input type="text" name="notes" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 px-4 py-2 bg-purple-600 text-white rounded-xl text-sm font-semibold hover:bg-purple-700">খুলুন</button>
                    <button type="button" @click="openSessionModal = false" class="px-4 py-2 border border-gray-300 rounded-xl text-sm text-gray-600">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Close Session Modal --}}
    <div x-show="openCloseSessionModal" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 p-4">
        <div class="bg-white rounded-2xl p-6 w-full max-w-sm shadow-xl">
            <h3 class="text-lg font-bold text-gray-900 mb-1">সেশন বন্ধ করুন</h3>
            <p class="text-sm text-gray-500 mb-4">সেশন #<span x-text="openSession ? openSession.id : ''"></span> — ঘোষিত ক্লোজিং ক্যাশ দিন।</p>
            <form method="POST" :action="'{{ route('pos.sessions.close', ['session' => '__ID__']) }}'.replace('__ID__', openSession ? openSession.id : '')" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">ক্লোজিং ক্যাশ (closing cash)</label>
                    <input type="number" name="closing_cash" min="0" step="0.01" required class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-xl text-sm font-semibold hover:bg-red-700">সেশন বন্ধ করুন</button>
                    <button type="button" @click="openCloseSessionModal = false" class="px-4 py-2 border border-gray-300 rounded-xl text-sm text-gray-600">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Variant Selection Modal --}}
    <div x-show="variantModalOpen" x-cloak x-transition.opacity class="fixed inset-0 z-[70] flex items-center justify-center bg-black/50 p-4" @keydown.escape.window="variantModalOpen = false">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-xl overflow-hidden" @click.outside="variantModalOpen = false">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <h3 class="text-lg font-bold text-gray-900 truncate" x-text="variantProduct ? variantProduct.name : ''"></h3>
                    <p class="text-xs text-gray-500 mt-0.5">ভেরিয়েন্ট নির্বাচন করুন — একাধিক যোগ করা যাবে</p>
                </div>
                <button @click="variantModalOpen = false" class="shrink-0 w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:text-gray-600 hover:bg-gray-100">✕</button>
            </div>
            <div class="p-4 space-y-2 max-h-[50vh] overflow-y-auto pos-scroll">
                <template x-for="(s, i) in variantSelections" :key="s.variant.id">
                    <div class="flex items-center gap-3 bg-gray-50 rounded-xl p-3" :class="s.qty > 0 ? 'ring-2 ring-purple-400' : ''">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-800 truncate" x-text="s.variant.name"></p>
                            <p class="text-xs text-gray-500 mt-0.5" x-text="formatMoney(s.variant.price) + ' • স্টক: ' + s.variant.stock"></p>
                            <p x-show="s.variant.stock <= 0" class="text-xs font-medium text-red-600 mt-0.5">স্টক শেষ</p>
                        </div>
                        <div class="flex items-center gap-1" :class="s.variant.stock <= 0 ? 'opacity-40 pointer-events-none' : ''">
                            <button @click="changeVariantQty(i, -1)" class="w-8 h-8 rounded-lg bg-white border border-gray-300 text-gray-600 font-bold hover:bg-gray-100">−</button>
                            <span class="w-8 text-center text-sm font-semibold" x-text="s.qty"></span>
                            <button @click="changeVariantQty(i, 1)" class="w-8 h-8 rounded-lg bg-white border border-gray-300 text-gray-600 font-bold hover:bg-gray-100" :disabled="s.qty >= s.variant.stock">+</button>
                        </div>
                        <span class="w-20 text-right text-sm font-bold text-gray-900" x-text="s.qty > 0 ? formatMoney(s.variant.price * s.qty) : ''"></span>
                    </div>
                </template>
                <p x-show="!variantSelections.length" class="text-center text-sm text-gray-400 py-6">এই পণ্যের কোনো ভেরিয়েন্ট নেই</p>
            </div>
            <div class="px-5 py-4 border-t border-gray-100 flex items-center justify-between gap-3">
                <div class="text-sm">
                    <span class="text-gray-500">মোট:</span>
                    <span class="font-bold text-gray-900 ml-1" x-text="formatMoney(variantSelectionTotal)"></span>
                    <span class="ml-2 text-xs text-gray-400" x-text="variantSelectionCount > 0 ? (variantSelectionCount + ' pcs') : ''"></span>
                </div>
                <div class="flex gap-2">
                    <button @click="variantModalOpen = false" class="px-4 py-2 border border-gray-300 rounded-xl text-sm text-gray-600 hover:bg-gray-50">Cancel</button>
                    <button @click="addVariantSelections()" :disabled="variantSelectionCount === 0" class="px-5 py-2 bg-purple-600 text-white rounded-xl text-sm font-semibold hover:bg-purple-700 disabled:opacity-40">কার্টে যোগ করুন</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Checkout form (hidden submit) --}}
    <form id="pos-checkout-form" method="POST" action="{{ route('pos.checkout') }}">
        @csrf
        <input type="hidden" name="items_json" id="pos-items-json">
        <input type="hidden" name="discount_type" id="pos-discount-type">
        <input type="hidden" name="discount_value" id="pos-discount-value">
        <input type="hidden" name="customer_id" id="pos-customer-id">
        <input type="hidden" name="customer_name" id="pos-customer-name">
        <input type="hidden" name="customer_phone" id="pos-customer-phone">
        <input type="hidden" name="payments_json" id="pos-payments-json">
        <input type="hidden" name="tendered_amount" id="pos-tendered">
        <input type="hidden" name="change_due" id="pos-change">
        <input type="hidden" name="warehouse_id" id="pos-warehouse" value="{{ $defaultWarehouse->id ?? '' }}">
        <input type="hidden" name="notes" id="pos-notes">
        <input type="hidden" name="resume_order_id" id="pos-resume-order">
    </form>
</div>
@endsection

@push('scripts')
<script>
function posApp() {
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
        customers: {!! $customers->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'phone' => $c->phone])->toJson() !!},
        customerName: '',
        customerPhone: '',
        discountType: '',
        discountValue: 0,
        payments: [],
        allowCredit: false,
        openSession: {!! $openSession ? json_encode([
            'id' => $openSession->id,
            'opened_at' => $openSession->opened_at,
            'warehouse_id' => $openSession->warehouse_id,
            'warehouse_name' => $openSession->warehouse?->name,
        ]) : 'null' !!},
        openSessionModal: false,
        openCloseSessionModal: false,
        canCloseSession: {{ auth()->user()?->hasPermission('pos_sessions', 'close') ? 'true' : 'false' }},
        warehouseOptions: {!! json_encode($warehouses->map(fn ($wh) => ['id' => $wh->id, 'name' => $wh->name])->values()) !!},
        activeWarehouseId: {!! $openSession
            ? json_encode($openSession->warehouse_id)
            : json_encode($defaultWarehouse ? $defaultWarehouse->id : '') !!},
        holds: {!! $holds->map(fn($h) => ['id' => $h->id, 'order_number' => $h->order_number, 'items_count' => $h->items->count(), 'customer_phone' => $h->customer_phone])->toJson() !!},
        enabledMethods: {!! json_encode($paymentAccounts->map(fn ($a) => ['code' => $a->code, 'name' => $a->name])->values()) !!},
        cashCode: '1010',
        defaultMethod: {!! json_encode($settings->defaultMethod()) !!},
        symbol: {!! json_encode($settings->currency_symbol ?? '৳') !!},
        taxRate: {{ $settings->tax_rate }},
        taxType: {!! json_encode($settings->tax_type) !!},
        flash: {!! json_encode([
            'resume_items' => session('resume_items'),
            'resume_order_id' => session('resume_order_id'),
            'resume_customer_name' => session('resume_customer_name'),
            'resume_customer_phone' => session('resume_customer_phone'),
        ]) !!},
        resumeOrderId: null,

        get subtotal() {
            return this.cart.reduce((sum, i) => sum + (i.price * i.quantity), 0);
        },
        get discountAmount() {
            if (!this.discountType) return 0;
            if (this.discountType === 'percent') {
                return Math.min(this.subtotal * (Number(this.discountValue) / 100), this.subtotal);
            }
            return Math.min(Number(this.discountValue) || 0, this.subtotal);
        },
        get taxable() {
            return Math.max(this.subtotal - this.discountAmount, 0);
        },
        get taxAmount() {
            if (this.taxRate <= 0) return 0;
            if (this.taxType === 'inclusive') {
                return Math.round((this.taxable - (this.taxable / (1 + this.taxRate / 100))) * 100) / 100;
            }
            return Math.round((this.taxable * (this.taxRate / 100)) * 100) / 100;
        },
        get total() {
            if (this.taxRate <= 0 || this.taxType === 'inclusive') {
                return Math.round(this.taxable * 100) / 100;
            }
            return Math.round((this.taxable + this.taxAmount) * 100) / 100;
        },
        get taxRateText() {
            return this.taxType === 'inclusive' ? (this.taxRate + '% incl') : (this.taxRate + '% excl');
        },
        get totalPaid() {
            return this.payments.reduce((s, p) => s + (Number(p.amount) || 0), 0);
        },
        get paidDiff() {
            return Math.round((this.total - this.totalPaid) * 100) / 100;
        },
        get cashTotal() {
            return this.payments.filter(p => p.method === this.cashCode).reduce((s, p) => s + (Number(p.amount) || 0), 0);
        },
        get nonCashTotal() {
            return this.payments.filter(p => p.method !== this.cashCode).reduce((s, p) => s + (Number(p.amount) || 0), 0);
        },
        get changeDue() {
            const payable = this.total - this.nonCashTotal;
            return Math.max(0, Math.round((this.cashTotal - payable) * 100) / 100);
        },
        get canCheckout() {
            if (!this.cart.length) return false;
            if (this.paidDiff > 0.01 && !this.allowCredit) return false;
            return true;
        },

        init() {
            // Resume held order into cart
            if (this.flash.resume_items) {
                const items = JSON.parse(this.flash.resume_items);
                this.cart = items.map((it, i) => ({
                    key: i,
                    product_id: it.product_id,
                    variant_id: it.variant_id,
                    name: it.name || ('Item ' + (i + 1)),
                    sku: it.sku,
                    price: Number(it.unit_price),
                    quantity: Number(it.quantity),
                    stock: 9999,
                }));
                this.resumeOrderId = this.flash.resume_order_id;
                this.customerName = this.flash.resume_customer_name || '';
                this.customerPhone = this.flash.resume_customer_phone || '';
                document.getElementById('pos-resume-order').value = this.resumeOrderId || '';
                this.scrollCartBottom();
            }
            if (this.openSession) {
                this.openSession.opened_at_formatted = new Date(this.openSession.opened_at).toLocaleTimeString();
            }
            this.resetPayments();
            this.loadProducts(true);
        },

        formatMoney(v) {
            return this.symbol + Number(v || 0).toFixed(2);
        },

        scrollCartBottom() {
            this.$nextTick(() => {
                const el = this.$refs.cartScroll;
                if (el) el.scrollTo({ top: el.scrollHeight, behavior: 'smooth' });
            });
        },

        resetPayments() {
            this.payments = [{ method: this.defaultMethod || this.enabledMethods[0]?.code || this.cashCode, amount: this.total, reference: '' }];
        },

        async loadProducts(reset) {
            if (reset) {
                this.products = [];
                this.nextPage = 1;
            }
            if (!this.nextPage) return;
            this.loading = true;
            const params = new URLSearchParams({ page: this.nextPage });
            if (this.search) params.set('search', this.search);
            if (this.categoryId) params.set('category_id', this.categoryId);
            if (this.activeWarehouseId) params.set('warehouse_id', this.activeWarehouseId);
            try {
                const res = await fetch('{{ route('pos.products') }}?' + params.toString(), { headers: { 'Accept': 'application/json' } });
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

        addProduct(p) {
            if (p.stock <= 0) return;
            if (p.has_variants && p.variants.length) {
                this.openVariantModal(p);
                return;
            }
            this.addSimpleToCart(p);
            this.resetPayments();
        },

        openVariantModal(p) {
            this.variantProduct = p;
            this.variantSelections = p.variants.map(v => ({ variant: v, qty: 0 }));
            this.variantModalOpen = true;
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
                if (s.qty > 0) this.addVariantToCart(p, s.variant, s.qty);
            });
            this.variantModalOpen = false;
            this.resetPayments();
        },

        get variantSelectionTotal() {
            return this.variantSelections.reduce((sum, s) => sum + (s.qty > 0 ? s.qty * s.variant.price : 0), 0);
        },

        get variantSelectionCount() {
            return this.variantSelections.reduce((sum, s) => sum + s.qty, 0);
        },

        addSimpleToCart(p) {
            const existing = this.cart.find(i => i.product_id === p.id && !i.variant_id);
            if (existing) {
                if (existing.quantity < p.stock) existing.quantity++;
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
                this.scrollCartBottom();
            }
        },

        addVariantToCart(p, v, qty = 1) {
            const existing = this.cart.find(i => i.variant_id === v.id);
            if (existing) {
                existing.quantity = Math.min(existing.quantity + qty, v.stock);
            } else {
                this.cart.push({
                    key: Date.now() + Math.random(),
                    product_id: p.id,
                    variant_id: v.id,
                    name: p.name + ' - ' + v.name,
                    sku: v.sku,
                    price: Number(v.price),
                    quantity: qty,
                    stock: v.stock,
                    image: p.image,
                });
                this.scrollCartBottom();
            }
        },

        onSearchInput() {
            clearTimeout(this.searchTimer);
            this.searchTimer = setTimeout(() => this.searchNow(), 350);
        },

        async searchNow() {
            clearTimeout(this.searchTimer);
            await this.loadProducts(true);
            this.tryAutoAdd();
        },

        tryAutoAdd() {
            const q = this.search.trim();
            if (!q || this.products.length !== 1) return;
            const p = this.products[0];
            const lq = q.toLowerCase();
            const exact = (p.name && p.name.toLowerCase() === lq) ||
                (p.sku && p.sku.toLowerCase() === lq) ||
                (p.barcode && p.barcode.toLowerCase() === lq);
            if (exact) this.autoAdd(p);
        },

        autoAdd(p) {
            if (p.stock <= 0) { alert('এই পণ্যে স্টক নেই'); return; }
            if (p.has_variants && p.variants.length) {
                this.openVariantModal(p);
            } else {
                this.addSimpleToCart(p);
            }
            this.resetPayments();
            this.search = '';
            this.loadProducts(true);
        },

        changeQty(index, delta) {
            const item = this.cart[index];
            const next = item.quantity + delta;
            if (next < 1) { this.cart.splice(index, 1); return; }
            if (next <= item.stock) item.quantity = next;
            this.syncDefaultPayment();
        },

        removeItem(index) {
            this.cart.splice(index, 1);
            this.resetPayments();
        },

        onCustomerChange() {
            const c = this.customers.find(c => String(c.id) === String(this.customerId));
            if (c) { this.customerName = c.name; this.customerPhone = c.phone; }
        },

        addPaymentRow(method) {
            this.payments.push({ method: method || this.enabledMethods[0]?.code || this.cashCode, amount: this.paidDiff > 0 ? this.paidDiff : 0, reference: '' });
        },

        methodActive(m) {
            return this.payments.some(p => p.method === m);
        },

        removePayment(idx) {
            this.payments.splice(idx, 1);
            if (this.payments.length === 1) this.payments[0].amount = this.total;
        },

        syncDefaultPayment() {
            if (this.payments.length === 1) {
                this.payments[0].amount = this.total;
            }
        },

        holdCart() {
            if (!this.cart.length) return;
            const items = this.cart.map(i => ({
                product_id: i.product_id, variant_id: i.variant_id, quantity: i.quantity, unit_price: i.price,
            }));
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('pos.hold') }}';
            form.innerHTML = '<input type="hidden" name="_token" value="' + csrf + '">' +
                '<input type="hidden" name="items_json" value="' + encodeURIComponent(JSON.stringify(items)) + '">' +
                '<input type="hidden" name="discount_type" value="' + (this.discountType || '') + '">' +
                '<input type="hidden" name="discount_value" value="' + this.discountValue + '">' +
                '<input type="hidden" name="customer_name" value="' + (this.customerName || '') + '">' +
                '<input type="hidden" name="customer_phone" value="' + (this.customerPhone || '') + '">';
            document.body.appendChild(form);
            form.submit();
        },

        checkout() {
            if (!this.cart.length) return;
            document.getElementById('pos-items-json').value = JSON.stringify(this.cart.map(i => ({
                product_id: i.product_id, variant_id: i.variant_id, quantity: i.quantity, unit_price: i.price,
            })));
            document.getElementById('pos-discount-type').value = this.discountType || '';
            document.getElementById('pos-discount-value').value = this.discountValue;
            document.getElementById('pos-customer-id').value = this.customerId;
            document.getElementById('pos-customer-name').value = this.customerName;
            document.getElementById('pos-customer-phone').value = this.customerPhone;
            document.getElementById('pos-payments-json').value = JSON.stringify(this.payments.map(p => ({
                method: p.method, amount: Number(p.amount) || 0, reference: p.reference || '',
            })));
            document.getElementById('pos-tendered').value = this.cashTotal;
            document.getElementById('pos-change').value = this.changeDue;
            document.getElementById('pos-warehouse').value = this.activeWarehouseId || '';
            document.getElementById('pos-resume-order').value = this.resumeOrderId || '';
            document.getElementById('pos-checkout-form').submit();
        },
    };
}
</script>
@endpush
