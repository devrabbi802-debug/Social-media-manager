@extends('layouts.tenant')

@section('title', __('sidebar.accounting_reports').' - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">@lang('sidebar.accounting_reports')</h1>
                    <p class="text-gray-600">প্রতিটি হিসাবের টাকা কোথা থেকে এসেছে, কোথায় গেছে — সোজা বাংলায় debit/credit সহ</p>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @include('tenant.reports.partials._nav', ['current' => 'accounting'])

        <form method="GET" class="bg-white rounded-2xl p-4 shadow-sm mb-6 flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs text-gray-500 mb-1">থেকে</label>
                <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="border border-gray-300 rounded-xl px-4 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">পর্যন্ত</label>
                <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="border border-gray-300 rounded-xl px-4 py-2 text-sm">
            </div>
            <div class="min-w-[220px]">
                <label class="block text-xs text-gray-500 mb-1">হিসাব</label>
                <select name="account_id" class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm">
                    <option value="">সব হিসাব</option>
                    @foreach($accounts as $acct)
                        <option value="{{ $acct->id }}" {{ request('account_id') == $acct->id ? 'selected' : '' }}>{{ $acct->code }} — {{ $acct->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-5 py-2 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition text-sm">রিপোর্ট দেখুন</button>
            @if(request()->filled('from') || request()->filled('to') || request()->filled('account_id'))
                <a href="{{ route('reports.accounting') }}" class="px-5 py-2 border border-gray-300 text-gray-700 rounded-xl font-medium hover:bg-gray-50 transition text-sm">রিসেট</a>
            @endif
        </form>

        {{-- Balance summary --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-8">
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">হাতে ক্যাশ</p>
                <p class="text-xl font-bold text-gray-900">৳{{ number_format($summary['cash'], 2) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">ব্যাংক</p>
                <p class="text-xl font-bold text-gray-900">৳{{ number_format($summary['bank'], 2) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">মোবাইল ওয়ালেট</p>
                <p class="text-xl font-bold text-gray-900">৳{{ number_format($summary['wallet'], 2) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">কাস্টমারের কাছে পাওনা</p>
                <p class="text-xl font-bold {{ $summary['receivable'] > 0 ? 'text-orange-600' : 'text-gray-900' }}">৳{{ number_format($summary['receivable'], 2) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">সাপ্লায়ারের পাওনা</p>
                <p class="text-xl font-bold {{ $summary['payable'] > 0 ? 'text-orange-600' : 'text-gray-900' }}">৳{{ number_format($summary['payable'], 2) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">ইনভেন্টরি (স্টক মূল্য)</p>
                <p class="text-xl font-bold text-gray-900">৳{{ number_format($summary['inventory'], 2) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">এই সময়ের আয়</p>
                <p class="text-xl font-bold text-green-600">৳{{ number_format($summary['income'], 2) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">এই সময়ের খরচ</p>
                <p class="text-xl font-bold text-red-600">-৳{{ number_format($summary['expense'], 2) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm border-2 border-purple-200">
                <p class="text-xs text-gray-500">নিট লাভ</p>
                <p class="text-xl font-bold {{ $summary['net_profit'] >= 0 ? 'text-green-600' : 'text-red-600' }}">৳{{ number_format($summary['net_profit'], 2) }}</p>
            </div>
        </div>

        {{-- Money in/out per account --}}
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="font-bold text-gray-900">কোন হিসাবে টাকা এসে গেছে / যাচ্ছে <span class="text-xs font-normal text-gray-400">(এই সময়ের মধ্যে)</span></h3>
                <p class="text-xs text-gray-500 mt-1">লেকার মধ্যে কোন অ্যাকাউন্টে কত টাকা ঢুকেছে (টাকা আসলে/জমা) আর কত বেরিয়েছে (খরচ/পাঠিয়েছি) — সবার ব্যালেন্স ডান পাশে।</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">হিসাব (কোথায়)</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">টাকা আসল / জমা (In)</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">টাকা বেরল (Out)</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">বর্তমান ব্যালেন্স</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($inOut as $row)
                            @php $a = $row['account']; @endphp
                            <tr>
                                <td class="px-6 py-3 text-sm text-gray-900">{{ $a->code }} — {{ $a->name }}
                                    <span class="text-xs text-gray-400">({{ $a->typeLabel() }})</span>
                                </td>
                                <td class="px-6 py-3 text-sm text-right font-medium text-green-600">{{ $row['in'] > 0 ? '৳'.number_format($row['in'], 2) : '—' }}</td>
                                <td class="px-6 py-3 text-sm text-right font-medium text-red-600">{{ $row['out'] > 0 ? '৳'.number_format($row['out'], 2) : '—' }}</td>
                                <td class="px-6 py-3 text-sm text-right font-bold text-gray-900">৳{{ number_format($row['balance'], 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-6 py-10 text-center text-gray-500">এই সময়ে কোনো লেনদেন হয়নি</td></tr>
                        @endforelse
                    </tbody>
                    @if($inOut->isNotEmpty())
                    <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-bold text-gray-900">সর্বমোট (Total)</th>
                            <th class="px-6 py-3 text-right text-sm font-bold text-green-600">৳{{ number_format($inOut->sum('in'), 2) }}</th>
                            <th class="px-6 py-3 text-right text-sm font-bold text-red-600">৳{{ number_format($inOut->sum('out'), 2) }}</th>
                            <th class="px-6 py-3 text-right text-sm font-bold text-gray-900">৳{{ number_format($inOut->sum('balance'), 2) }}</th>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>

        {{-- Detailed transactions --}}
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="font-bold text-gray-900">সব লেনদেনের হিসাব (নং/তারিখ → কোন অ্যাকাউন্ট → debit-credit)</h3>
                <p class="text-xs text-gray-500 mt-1">Debit = যে হিসাবে টাকা জমা/খরচ গেছে। Credit = টাকা কোথা থেকে এসেছে বা কারensions পাওনা। দুটোই সমান হলে বইয়ের হিসাব ব্যালেন্স।</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">নং / তারিখ</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">বিবরণ (Narration)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">হিসাব</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Debit (টাকা জমা/আগে)</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Credit (টাকা থেকে এসেছে)</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($entries as $entry)
                            @foreach($entry->lines as $liIndex => $li)
                                @php
                                    $acc = $li->account;
                                    $t = $acc?->account_type ?? '';
                                    if ($li->debit > 0 && (float) $li->credit == 0) {
                                        $dir = $t === 'income' ? 'আয় (বিক্রি)'
                                            : ($t === 'expense' ? 'খরচ হয়েছে'
                                            : ($t === 'asset' ? 'টাকা জমা (এসেছে)'
                                            : ($t === 'liability' ? 'পাওনা বেড়েছে'
                                            : 'ডেবিট')));
                                    } elseif ((float) $li->credit > 0 && $li->debit == 0) {
                                        $dir = $t === 'income' ? 'আয় এ খাতা'
                                            : ($t === 'expense' ? 'খরচের সাথে'
                                            : ($t === 'asset' ? 'টাকা বের হয়েছে (গিয়েছে)'
                                            : ($t === 'liability' ? 'পাওনা কমেছে/জমা'
                                            : 'ক্রেডিট')));
                                    } else {
                                        $dir = '';
                                    }
                                @endphp
                                <tr class="{{ $liIndex === 0 ? '' : 'bg-gray-50/50' }}">
                                    @if($liIndex === 0)
                                        <td class="px-6 py-3 text-sm text-purple-600 font-medium" rowspan="{{ $entry->lines->count() }}">
                                            {{ $entry->journal_number }}<br>
                                            <span class="text-xs text-gray-500">{{ $entry->entry_date->format('d M Y') }}</span>
                                        </td>
                                        <td class="px-6 py-3 text-sm text-gray-900" rowspan="{{ $entry->lines->count() }}">{{ $entry->narration }}</td>
                                    @endif
                                    <td class="px-6 py-3 text-sm text-gray-900">
                                        @if($acc) {{ $acc->code }} — {{ $acc->name }} @else — @endif
                                        @if($dir) <span class="block text-xs {{ $li->debit > 0 ? 'text-green-600' : 'text-red-600' }}">{{ $dir }}</span> @endif
                                    </td>
                                    <td class="px-6 py-3 text-sm text-right text-gray-900">{{ $li->debit > 0 ? '৳'.number_format($li->debit, 2) : '—' }}</td>
                                    <td class="px-6 py-3 text-sm text-right text-gray-900">{{ $li->credit > 0 ? '৳'.number_format($li->credit, 2) : '—' }}</td>
                                </tr>
                            @endforeach
                        @empty
                            <tr><td colspan="5" class="px-6 py-10 text-center text-gray-500">এই সময়ে কোনো লেনদেন পাওয়া যায়নি</td></tr>
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