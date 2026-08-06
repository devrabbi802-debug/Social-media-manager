@extends('layouts.tenant')

@section('title', 'Accounting Dashboard - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">@lang('accounting.dashboard_title')</h1>
                    <p class="text-gray-600 text-sm">@lang('accounting.dashboard_subtitle')</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('accounting.money.create', ['type' => 'expense']) }}" class="px-4 py-2 bg-red-600 text-white rounded-xl text-sm font-medium hover:bg-red-700">@lang('accounting.add_expense')</a>
                    <a href="{{ route('accounting.money.create', ['type' => 'income']) }}" class="px-4 py-2 bg-green-600 text-white rounded-xl text-sm font-medium hover:bg-green-700">@lang('accounting.add_income')</a>
                    <a href="{{ route('accounting.journal.create') }}" class="px-4 py-2 bg-purple-600 text-white rounded-xl text-sm font-medium hover:bg-purple-700">@lang('accounting.add_journal')</a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        {{-- Onboarding checklist --}}
        @php $pendingSteps = collect($onboarding)->filter(fn($s) => !$s['done']); @endphp
        @if($pendingSteps->isNotEmpty())
            <div class="bg-indigo-50 border border-indigo-200 rounded-2xl p-5">
                <div class="flex items-center justify-between flex-wrap gap-2 mb-3">
                    <h2 class="font-bold text-indigo-900">@lang('accounting.setup_pending')</h2>
                    <span class="text-xs text-indigo-600">{{ $pendingSteps->count() }} / {{ count($onboarding) }}</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    @foreach($onboarding as $step)
                        <a href="{{ $step['route'] }}" class="flex items-center gap-3 bg-white rounded-xl p-3 border {{ $step['done'] ? 'border-green-200' : 'border-indigo-100 hover:border-indigo-300' }}">
                            <span class="flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold {{ $step['done'] ? 'bg-green-500 text-white' : 'bg-indigo-100 text-indigo-600' }}">
                                {{ $step['done'] ? '✓' : ($loop->index + 1) }}
                            </span>
                            <span class="flex-1 text-sm {{ $step['done'] ? 'text-gray-400 line-through' : 'text-gray-800' }}">
                                @lang('accounting.'.$step['label_key'])
                            </span>
                            @if(! $step['done'])
                                <span class="text-xs text-indigo-600 font-medium whitespace-nowrap">@lang('accounting.setup_take') →</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Cash cards --}}
        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4">
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">@lang('accounting.cash_on_hand')</p>
                <p class="text-lg font-bold text-green-600">৳{{ number_format($cash, 2) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">@lang('accounting.bank_accounts')</p>
                <p class="text-lg font-bold text-blue-600">৳{{ number_format($bank, 2) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">@lang('accounting.mobile_wallet')</p>
                <p class="text-lg font-bold text-pink-600">৳{{ number_format($mobile, 2) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">@lang('accounting.accounts_receivable')</p>
                <p class="text-lg font-bold text-amber-600">৳{{ number_format($receivable, 2) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">@lang('accounting.accounts_payable')</p>
                <p class="text-lg font-bold text-red-600">৳{{ number_format($payable, 2) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">@lang('accounting.inventory_value')</p>
                <p class="text-lg font-bold text-gray-800">৳{{ number_format($inventory, 2) }}</p>
            </div>
        </div>

        {{-- Month summary + balance sheet --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- This month --}}
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="font-bold text-gray-900">@lang('accounting.this_month')</h3>
                    <a href="{{ route('accounting.reports.income-statement') }}" class="text-xs text-purple-600 hover:underline">@lang('accounting.view_more')</a>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">@lang('accounting.total_income')</span>
                        <span class="text-lg font-bold text-green-600">৳{{ number_format($incomeStatement['total_income'], 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">@lang('accounting.total_expense')</span>
                        <span class="text-lg font-bold text-red-600">৳{{ number_format($incomeStatement['total_expense'], 2) }}</span>
                    </div>
                    <div class="border-t border-dashed pt-4 flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-700">@lang('accounting.profit_loss')</span>
                        @php $np = $incomeStatement['net_profit']; @endphp
                        <span class="text-xl font-bold {{ $np >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $np >= 0 ? '' : '-' }}৳{{ number_format(abs($np), 2) }}
                        </span>
                    </div>
                    @if ($np >= 0)
                        <p class="text-xs text-green-600">@lang('accounting.profit_positive')</p>
                    @else
                        <p class="text-xs text-red-500">@lang('accounting.profit_negative')</p>
                    @endif
                </div>
            </div>

            {{-- Balance sheet summary --}}
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="font-bold text-gray-900">@lang('accounting.overall_position')</h3>
                    <a href="{{ route('accounting.reports.balance-sheet') }}" class="text-xs text-purple-600 hover:underline">@lang('accounting.view_more')</a>
                </div>
                <div class="p-6 space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">@lang('accounting.total_assets')</span>
                        <span class="font-bold text-gray-900">৳{{ number_format($balanceSheet['total_assets'], 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">@lang('accounting.total_liabilities')</span>
                        <span class="font-bold text-gray-900">৳{{ number_format($balanceSheet['total_liabilities'], 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">@lang('accounting.owner_capital')</span>
                        <span class="font-bold text-gray-900">৳{{ number_format($balanceSheet['total_equity'], 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">@lang('accounting.total_profit')</span>
                        <span class="font-bold text-blue-600">৳{{ number_format($balanceSheet['net_profit'], 2) }}</span>
                    </div>
                    <div class="border-t border-dashed pt-3 flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-700">@lang('accounting.assets_eq')</span>
                        <span class="text-sm font-bold {{ abs($balanceSheet['difference']) < 0.01 ? 'text-green-600' : 'text-red-600' }}">
                            {{ abs($balanceSheet['difference']) < 0.01 ? __('accounting.balance_ok') : __('accounting.balance_diff_warning', ['amt' => '৳'.number_format($balanceSheet['difference'], 2)]) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent entries --}}
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="font-bold text-gray-900">@lang('accounting.recent_transactions')</h3>
                <a href="{{ route('accounting.journal.index') }}" class="text-xs text-purple-600 hover:underline">@lang('accounting.view_more')</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">@lang('accounting.number')</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">@lang('accounting.date')</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">@lang('accounting.description')</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">@lang('accounting.debit')</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">@lang('accounting.credit')</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">@lang('accounting.status')</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($recentEntries as $entry)
                            <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('accounting.journal.show', $entry) }}'">
                                <td class="px-6 py-3 text-sm font-medium text-purple-600">{{ $entry->journal_number }}</td>
                                <td class="px-6 py-3 text-sm text-gray-600">{{ $entry->entry_date->format('d M Y') }}</td>
                                <td class="px-6 py-3 text-sm text-gray-900">{{ $entry->narration }}</td>
                                <td class="px-6 py-3 text-sm text-right text-gray-900">৳{{ number_format($entry->totalDebit(), 2) }}</td>
                                <td class="px-6 py-3 text-sm text-right text-gray-900">৳{{ number_format($entry->totalCredit(), 2) }}</td>
                                <td class="px-6 py-3 text-center">
                                    @if ($entry->isReversed())
                                        <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-700">@lang('accounting.reversed')</span>
                                    @else
                                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700">@lang('accounting.posted')</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-10 text-center text-gray-500">@lang('accounting.no_transactions')</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
