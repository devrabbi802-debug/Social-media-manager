@extends('layouts.tenant')

@section('title', 'Trial Balance - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">@lang('accounting.report_trial')</h1>
                    <p class="text-gray-600 text-sm">@lang('accounting.report_trial_subtitle')</p>
                </div>
                <form method="GET" class="flex gap-2 items-end">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">@lang('accounting.as_of')</label>
                        <input type="date" name="as_of" value="{{ $asOf->format('Y-m-d') }}" class="border border-gray-300 rounded-xl px-3 py-2 text-sm">
                    </div>
                    <button class="px-4 py-2 bg-purple-600 text-white rounded-xl text-sm font-medium">@lang('accounting.view')</button>
                </form>
            </div>
        </div>
    </div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @php $balanced = abs($data['debit_total'] - $data['credit_total']) < 0.01; @endphp
        <div class="mb-6 px-4 py-3 rounded-xl text-sm font-medium {{ $balanced ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-600' }}">
            @if($balanced)
                @lang('accounting.balanced_true')
            @else
                @lang('accounting.not_balanced', ['amt' => '৳'.number_format($data['debit_total'] - $data['credit_total'], 2)])
            @endif
        </div>

        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">@lang('accounting.code')</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">@lang('accounting.account')</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">@lang('accounting.type')</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">@lang('accounting.debit')</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">@lang('accounting.credit')</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($data['rows'] as $row)
                        <tr>
                            <td class="px-6 py-3 text-sm font-mono text-gray-400">{{ $row['account']->code }}</td>
                            <td class="px-6 py-3 text-sm font-medium text-gray-900">{{ $row['account']->name }}</td>
                            <td class="px-6 py-3 text-xs text-gray-500">{{ $row['account']->typeLabel() }}</td>
                            <td class="px-6 py-3 text-sm text-right text-gray-900">{{ $row['debit'] > 0 ? '৳'.number_format($row['debit'], 2) : '—' }}</td>
                            <td class="px-6 py-3 text-sm text-right text-gray-900">{{ $row['credit'] > 0 ? '৳'.number_format($row['credit'], 2) : '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="3" class="px-6 py-3 text-sm font-bold text-gray-900">@lang('accounting.total')</td>
                        <td class="px-6 py-3 text-sm font-bold text-right text-gray-900">৳{{ number_format($data['debit_total'], 2) }}</td>
                        <td class="px-6 py-3 text-sm font-bold text-right text-gray-900">৳{{ number_format($data['credit_total'], 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
