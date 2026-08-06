@extends('layouts.tenant')

@section('title', 'Income & Expense - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">@lang('accounting.money_title')</h1>
                    <p class="text-gray-600 text-sm">@lang('accounting.money_subtitle')</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('accounting.money.create', ['type' => 'expense']) }}" class="px-4 py-2 bg-red-600 text-white rounded-xl text-sm font-medium hover:bg-red-700">@lang('accounting.record_expense')</a>
                    <a href="{{ route('accounting.money.create', ['type' => 'income']) }}" class="px-4 py-2 bg-green-600 text-white rounded-xl text-sm font-medium hover:bg-green-700">@lang('accounting.record_income')</a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">@lang('accounting.total_income_s')</p>
                <p class="text-2xl font-bold text-green-600">৳{{ number_format($incomeTotal, 2) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">@lang('accounting.total_expense_s')</p>
                <p class="text-2xl font-bold text-red-600">৳{{ number_format($expenseTotal, 2) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">@lang('accounting.net_result')</p>
                @php $net = $incomeTotal - $expenseTotal; @endphp
                <p class="text-2xl font-bold {{ $net >= 0 ? 'text-blue-600' : 'text-red-600' }}">৳{{ number_format($net, 2) }}</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm p-4 mb-6">
            <form method="GET" class="grid grid-cols-2 md:grid-cols-4 gap-3 items-end">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">@lang('accounting.type')</label>
                    <select name="type" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
                        <option value="">@lang('accounting.all')</option>
                        <option value="income" {{ request('type') === 'income' ? 'selected' : '' }}>@lang('accounting.entry_type_income')</option>
                        <option value="expense" {{ request('type') === 'expense' ? 'selected' : '' }}>@lang('accounting.entry_type_expense')</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">@lang('accounting.from')</label>
                    <input type="date" name="from" value="{{ request('from') }}" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">@lang('accounting.to')</label>
                    <input type="date" name="to" value="{{ request('to') }}" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
                </div>
                <button class="px-4 py-2 bg-purple-600 text-white rounded-xl text-sm font-medium">@lang('accounting.search')</button>
            </form>
        </div>

        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">@lang('accounting.number')</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">@lang('accounting.date')</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">@lang('accounting.description')</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">@lang('accounting.type')</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">@lang('accounting.amount')</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($entries as $entry)
                            <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('accounting.journal.show', $entry) }}'">
                                <td class="px-6 py-3 text-sm font-medium text-purple-600">{{ $entry->journal_number }}</td>
                                <td class="px-6 py-3 text-sm text-gray-600">{{ $entry->entry_date->format('d M Y') }}</td>
                                <td class="px-6 py-3 text-sm text-gray-900">{{ $entry->narration }}</td>
                                <td class="px-6 py-3 text-center">
                                    @if($entry->reference_type === 'income')
                                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700">@lang('accounting.entry_type_income')</span>
                                    @else
                                        <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-700">@lang('accounting.entry_type_expense')</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-sm font-semibold text-right {{ $entry->reference_type === 'income' ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $entry->reference_type === 'income' ? '' : '-' }}৳{{ number_format($entry->amount, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-10 text-center text-gray-500">@lang('accounting.no_entries')</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4">
                {{ $entries->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
