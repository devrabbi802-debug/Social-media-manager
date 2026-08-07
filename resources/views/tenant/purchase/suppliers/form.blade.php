@extends('layouts.tenant')

@section('title', ($supplier ? __('common.edit') : 'নতুন সাপ্লায়ার').' - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $supplier ? 'সাপ্লায়ার সম্পাদনা' : 'নতুন সাপ্লায়ার' }}</h1>
                    <p class="text-gray-600">সাপ্লায়ারের তথ্য ও পেমেন্ট শর্ত দিন</p>
                </div>
                <a href="{{ route('purchase.suppliers.index') }}" class="text-gray-600 hover:text-purple-600 font-medium">← @lang('common.back')</a>
            </div>
        </div>
    </div>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-2xl p-4 mb-6">
            <ul class="text-red-800 text-sm space-y-1">
                @foreach($errors->all() as $error)<li>• {{ $error }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form action="{{ $supplier ? route('purchase.suppliers.update', $supplier) : route('purchase.suppliers.store') }}" method="POST" class="bg-white rounded-2xl p-6 shadow-sm">
            @csrf
            @if($supplier) @method('PUT') @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">সাপ্লায়ারের নাম *</label>
                    <input type="text" name="name" value="{{ old('name', $supplier->name ?? '') }}" required class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">যোগাযোগ ব্যক্তি</label>
                    <input type="text" name="contact_person" value="{{ old('contact_person', $supplier->contact_person ?? '') }}" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">কোম্পানি</label>
                    <input type="text" name="company" value="{{ old('company', $supplier->company ?? '') }}" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ফোন</label>
                    <input type="text" name="phone" value="{{ old('phone', $supplier->phone ?? '') }}" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ইমেইল</label>
                    <input type="email" name="email" value="{{ old('email', $supplier->email ?? '') }}" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ঠিকানা</label>
                    <input type="text" name="address" value="{{ old('address', $supplier->address ?? '') }}" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ট্যাক্স নম্বর (TIN/BIN)</label>
                    <input type="text" name="tax_number" value="{{ old('tax_number', $supplier->tax_number ?? '') }}" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">পেমেন্ট টার্ম</label>
                    <input type="text" name="payment_terms" value="{{ old('payment_terms', $supplier->payment_terms ?? '') }}" placeholder="যেমন: Net 30" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ডিফল্ট পেমেন্ট সময় (দিন)</label>
                    <input type="number" name="payment_term_days" min="0" max="3650" value="{{ old('payment_term_days', $supplier->payment_term_days ?? 30) }}" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">শুরুর ব্যালেন্স (ঋণ)</label>
                    <input type="number" step="0.01" name="opening_balance" value="{{ old('opening_balance', $supplier->opening_balance ?? 0) }}" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                    <p class="text-xs text-gray-500 mt-1">আগে থেকে এই সাপ্লায়ারের কাছে আমাদের যে টাকা বাকি আছে</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">স্ট্যাটাস</label>
                    <select name="status" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                        <option value="active" {{ ($supplier->status ?? 'active') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ ($supplier->status ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">নোট</label>
                    <textarea name="notes" rows="3" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">{{ old('notes', $supplier->notes ?? '') }}</textarea>
                </div>
            </div>

            <div class="mt-6 flex items-center gap-3">
                <button type="submit" class="px-6 py-2.5 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition">সেভ করুন</button>
                <a href="{{ route('purchase.suppliers.index') }}" class="px-6 py-2.5 border rounded-xl text-gray-600 hover:bg-gray-50 transition">বাতিল</a>
            </div>
        </form>
    </div>
</div>
@endsection
