@extends('layouts.tenant')

@section('title', 'Account Ledger - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <h1 class="text-2xl font-bold text-gray-900">@lang('accounting.report_ledger')</h1>
            <p class="text-gray-600 text-sm">@lang('accounting.report_ledger_subtitle')</p>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <form method="GET" class="bg-white rounded-2xl shadow-sm p-4 mb-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 items-end">
                <div class="col-span-2 md:col-span-1">
                    <label class="block text-xs text-gray-500 mb-1">@lang('accounting.account')</label>
                    <select name="account_id" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm" required>
                        <option value="">@lang('accounting.account_select')</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}" {{ request('account_id') == $account->id ? 'selected' : '' }}>
                                {{ $account->code }} — {{ $account->name }}
                            </option>
                        @endforeach
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
                <button class="px-4 py-2 bg-purple-600 text-white rounded-xl text-sm font-medium">@lang('accounting.view')</button>
            </div>
        </form>

        @if($account && $data)
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-gray-900">{{ $account->name }} <span class="text-xs text-gray-400 font-normal">({{ $account->code }})</span></h3>
                        <p class="text-xs text-gray-500">{{ $account->typeLabel() }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-500">@lang('accounting.opening_balance_c')</p>
                        <p class="text-lg font-bold text-gray-900">৳{{ number_format($data['opening'], 2) }}</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">@lang('accounting.date')</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">@lang('accounting.description')</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">@lang('accounting.debit')</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">@lang('accounting.credit')</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">@lang('accounting.balance')</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($data['rows'] as $row)
                                <tr>
                                    <td class="px-6 py-3 text-sm text-gray-600">{{ $row['line']->entry->entry_date->format('d M Y') }}</td>
                                    <td class="px-6 py-3">
                                        <a href="{{ route('accounting.journal.show', $row['line']->entry) }}" class="text-sm text-gray-900 hover:text-purple-600">{{ $row['line']->entry->narration }}</a>
                                        <p class="text-xs text-gray-400">{{ $row['line']->entry->journal_number }}</p>
                                    </td>
                                    <td class="px-6 py-3 text-sm text-right text-gray-900">{{ $row['debit'] > 0 ? '৳'.number_format($row['debit'], 2) : '—' }}</td>
                                    <td class="px-6 py-3 text-sm text-right text-gray-900">{{ $row['credit'] > 0 ? '৳'.number_format($row['credit'], 2) : '—' }}</td>
                                    <td class="px-6 py-3 text-sm font-semibold text-right text-gray-900">৳{{ number_format($row['balance'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="4" class="px-6 py-3 text-sm font-bold text-gray-900">@lang('accounting.closing_balance')</td>
                                <td class="px-6 py-3 text-sm font-bold text-right text-purple-600">৳{{ number_format($data['closing'], 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @if(empty($data['rows']))
                    <div class="px-6 py-10 text-center text-gray-500">@lang('accounting.no_transactions_period')</div>
                @endif
            </div>
        @else
            <div class="bg-white rounded-2xl shadow-sm p-12 text-center text-gray-500">
                @lang('accounting.select_account_hint')
            </div>
        @endif
    </div>
</div>
@endsection
