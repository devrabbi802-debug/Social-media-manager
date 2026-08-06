@extends('layouts.tenant')

@section('title', 'All Transactions - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">@lang('accounting.report_transactions')</h1>
                    <p class="text-gray-600 text-sm">@lang('accounting.report_transactions_subtitle')</p>
                </div>
                <a href="{{ route('accounting.journal.create') }}" class="px-4 py-2 bg-purple-600 text-white rounded-xl text-sm font-medium hover:bg-purple-700">@lang('accounting.new_entry')</a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-2xl shadow-sm p-4 mb-6">
            <form method="GET" class="grid grid-cols-2 md:grid-cols-5 gap-3 items-end">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">@lang('accounting.from')</label>
                    <input type="date" name="from" value="{{ request('from') }}" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">@lang('accounting.to')</label>
                    <input type="date" name="to" value="{{ request('to') }}" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">@lang('accounting.type')</label>
                    <select name="reference_type" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
                        <option value="">@lang('accounting.all')</option>
                        @foreach(['pos' => __('accounting.entry_type_pos'), 'pos_refund' => __('accounting.entry_type_pos_refund'), 'order' => __('accounting.entry_type_order'), 'order_payment' => __('accounting.entry_type_order_payment'), 'manual' => __('accounting.entry_type_manual'), 'expense' => __('accounting.entry_type_expense'), 'income' => __('accounting.entry_type_income'), 'opening' => __('accounting.entry_type_opening')] as $value => $label)
                            <option value="{{ $value }}" {{ request('reference_type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">@lang('accounting.account')</label>
                    <select name="account_id" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
                        <option value="">@lang('accounting.all_accounts')</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}" {{ request('account_id') == $account->id ? 'selected' : '' }}>{{ $account->code }} — {{ $account->name }}</option>
                        @endforeach
                    </select>
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">@lang('accounting.account')</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">@lang('accounting.debit')</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">@lang('accounting.credit')</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($entries as $entry)
                            @foreach($entry->lines as $lineIndex => $line)
                                <tr class="{{ $lineIndex === 0 ? '' : 'bg-gray-50/50' }} hover:bg-gray-50">
                                    @if($lineIndex === 0)
                                        <td class="px-6 py-3 text-sm font-medium text-purple-600" rowspan="{{ $entry->lines->count() }}">
                                            <a href="{{ route('accounting.journal.show', $entry) }}">{{ $entry->journal_number }}</a>
                                        </td>
                                        <td class="px-6 py-3 text-sm text-gray-600" rowspan="{{ $entry->lines->count() }}">{{ $entry->entry_date->format('d M Y') }}</td>
                                        <td class="px-6 py-3 text-sm text-gray-900 max-w-xs truncate" rowspan="{{ $entry->lines->count() }}">{{ $entry->narration }}</td>
                                    @endif
                                    <td class="px-6 py-3 text-sm text-gray-900">{{ $line->account->name }}</td>
                                    <td class="px-6 py-3 text-sm text-right text-gray-900">{{ $line->debit > 0 ? '৳'.number_format($line->debit, 2) : '—' }}</td>
                                    <td class="px-6 py-3 text-sm text-right text-gray-900">{{ $line->credit > 0 ? '৳'.number_format($line->credit, 2) : '—' }}</td>
                                </tr>
                            @endforeach
                        @empty
                            <tr><td colspan="6" class="px-6 py-10 text-center text-gray-500">@lang('accounting.no_entries')</td></tr>
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
