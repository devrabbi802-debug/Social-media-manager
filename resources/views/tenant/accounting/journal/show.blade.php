@extends('layouts.tenant')

@section('title', 'Journal Entry '.$entry->journal_number.' - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 py-10">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $entry->journal_number }}</h1>
                <p class="text-gray-600 text-sm">{{ $entry->entry_date->format('d M Y') }}</p>
            </div>
            <div class="flex items-center gap-2">
                @if(! $entry->isReversed())
                    <form method="POST" action="{{ route('accounting.journal.reverse', $entry) }}" onsubmit="return confirm('@lang('accounting.confirm_reverse')')">
                        @csrf
                        <button class="px-4 py-2 bg-red-600 text-white rounded-xl text-sm font-medium hover:bg-red-700">@lang('accounting.reverse')</button>
                    </form>
                @endif
                <a href="{{ route('accounting.journal.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-xl text-sm font-medium hover:bg-gray-50">@lang('accounting.back')</a>
            </div>
        </div>

                @if($entry->isReversed())
                    <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-700 mb-6">
                        @lang('accounting.reversed_notice')
                        @if($entry->reversedBy)
                            — @lang('accounting.new_entry_label'): <a href="{{ route('accounting.journal.show', $entry->reversedBy) }}" class="underline">{{ $entry->reversedBy->journal_number }}</a>
                        @endif
                    </div>
                @endif
        @if($entry->reverses)
            <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 text-sm text-amber-700 mb-6">
                @lang('accounting.reversing_notice') <a href="{{ route('accounting.journal.show', $entry->reverses) }}" class="underline">{{ $entry->reverses->journal_number }}</a>.
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm p-6 sm:p-8">
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-6">
                <div>
                    <p class="text-xs text-gray-500">@lang('accounting.description')</p>
                    <p class="text-sm font-medium text-gray-900">{{ $entry->narration }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">@lang('accounting.type')</p>
                    <p class="text-sm font-medium text-gray-900">{{ ucfirst($entry->reference_type ?? 'manual') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">@lang('accounting.by')</p>
                    <p class="text-sm font-medium text-gray-900">{{ $entry->creator?->name ?? '—' }}</p>
                </div>
            </div>

            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">@lang('accounting.account')</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">@lang('accounting.debit')</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">@lang('accounting.credit')</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($entry->lines as $line)
                        <tr>
                            <td class="px-4 py-3">
                                <p class="text-sm font-medium text-gray-900">{{ $line->account->name }}</p>
                                <p class="text-xs text-gray-400">{{ $line->account->code }} • {{ $line->account->typeLabel() }}</p>
                                @if($line->memo)
                                    <p class="text-xs text-gray-500">{{ $line->memo }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right text-sm text-gray-900">{{ $line->debit > 0 ? '৳'.number_format($line->debit, 2) : '—' }}</td>
                            <td class="px-4 py-3 text-right text-sm text-gray-900">{{ $line->credit > 0 ? '৳'.number_format($line->credit, 2) : '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td class="px-4 py-3 text-sm font-bold text-gray-900">@lang('accounting.total')</td>
                        <td class="px-4 py-3 text-right text-sm font-bold text-gray-900">৳{{ number_format($entry->totalDebit(), 2) }}</td>
                        <td class="px-4 py-3 text-right text-sm font-bold text-gray-900">৳{{ number_format($entry->totalCredit(), 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
