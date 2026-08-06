@extends('layouts.tenant')

@section('title', 'Income Statement - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">@lang('accounting.report_income')</h1>
                    <p class="text-gray-600 text-sm">@lang('accounting.report_income_subtitle')</p>
                </div>
                <form method="GET" class="flex gap-2 items-end">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">@lang('accounting.from')</label>
                        <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="border border-gray-300 rounded-xl px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">@lang('accounting.to')</label>
                        <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="border border-gray-300 rounded-xl px-3 py-2 text-sm">
                    </div>
                    <button class="px-4 py-2 bg-purple-600 text-white rounded-xl text-sm font-medium">@lang('accounting.view')</button>
                </form>
            </div>
        </div>
    </div>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="font-bold text-gray-900">@lang('accounting.income_title')</h3>
            </div>
            <table class="min-w-full divide-y divide-gray-200">
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($data['income'] as $row)
                        <tr>
                            <td class="px-6 py-3 text-sm text-gray-900">{{ $row['account']->name }}</td>
                            <td class="px-6 py-3 text-sm font-semibold text-right text-green-600">৳{{ number_format($row['balance'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td class="px-6 py-3 text-sm font-bold text-gray-900">@lang('accounting.total_income')</td>
                        <td class="px-6 py-3 text-sm font-bold text-right text-green-600">৳{{ number_format($data['total_income'], 2) }}</td>
                    </tr>
                </tfoot>
            </table>

            <div class="px-6 py-4 border-t border-gray-200 border-b">
                <h3 class="font-bold text-gray-900">@lang('accounting.expense_title')</h3>
            </div>
            <table class="min-w-full divide-y divide-gray-200">
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($data['expenses'] as $row)
                        <tr>
                            <td class="px-6 py-3 text-sm text-gray-900">{{ $row['account']->name }}</td>
                            <td class="px-6 py-3 text-sm font-semibold text-right text-red-600">৳{{ number_format($row['balance'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td class="px-6 py-3 text-sm font-bold text-gray-900">@lang('accounting.total_expense')</td>
                        <td class="px-6 py-3 text-sm font-bold text-right text-red-600">৳{{ number_format($data['total_expense'], 2) }}</td>
                    </tr>
                </tfoot>
            </table>

            <div class="px-6 py-5 flex items-center justify-between border-t-2 border-gray-200">
                <span class="font-bold text-gray-900">@lang('accounting.net_profit_loss')</span>
                @php $np = $data['net_profit']; @endphp
                <span class="text-2xl font-bold {{ $np >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    {{ $np >= 0 ? '' : '-' }}৳{{ number_format(abs($np), 2) }}
                </span>
            </div>
        </div>
    </div>
</div>
@endsection
