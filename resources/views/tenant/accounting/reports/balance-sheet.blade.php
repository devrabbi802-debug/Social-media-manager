@extends('layouts.tenant')

@section('title', 'Balance Sheet - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">@lang('accounting.report_balance')</h1>
                    <p class="text-gray-600 text-sm">@lang('accounting.report_balance_subtitle')</p>
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
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="font-bold text-gray-900">@lang('accounting.assets_title')</h3>
                </div>
                <table class="min-w-full divide-y divide-gray-200">
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($data['assets'] as $row)
                            <tr>
                                <td class="px-6 py-3 text-sm text-gray-900">{{ $row['account']->name }}</td>
                                <td class="px-6 py-3 text-sm font-semibold text-right text-gray-900">৳{{ number_format($row['balance'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td class="px-6 py-3 text-sm font-bold text-gray-900">@lang('accounting.total_assets')</td>
                            <td class="px-6 py-3 text-sm font-bold text-right text-green-600">৳{{ number_format($data['total_assets'], 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="font-bold text-gray-900">@lang('accounting.liabilities_title')</h3>
                    </div>
                    <table class="min-w-full divide-y divide-gray-200">
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($data['liabilities'] as $row)
                                <tr>
                                    <td class="px-6 py-3 text-sm text-gray-900">{{ $row['account']->name }}</td>
                                    <td class="px-6 py-3 text-sm font-semibold text-right text-red-600">৳{{ number_format($row['balance'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td class="px-6 py-3 text-sm font-bold text-gray-900">@lang('accounting.total_liabilities')</td>
                                <td class="px-6 py-3 text-sm font-bold text-right text-red-600">৳{{ number_format($data['total_liabilities'], 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="font-bold text-gray-900">@lang('accounting.equity_title')</h3>
                    </div>
                    <table class="min-w-full divide-y divide-gray-200">
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($data['equity'] as $row)
                                <tr>
                                    <td class="px-6 py-3 text-sm text-gray-900">{{ $row['account']->name }}</td>
                                    <td class="px-6 py-3 text-sm font-semibold text-right text-gray-900">৳{{ number_format($row['balance'], 2) }}</td>
                                </tr>
                            @endforeach
                            <tr>
                                <td class="px-6 py-3 text-sm text-gray-900">@lang('accounting.total_profit')</td>
                                <td class="px-6 py-3 text-sm font-semibold text-right text-blue-600">৳{{ number_format($data['net_profit'], 2) }}</td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td class="px-6 py-3 text-sm font-bold text-gray-900">@lang('accounting.total_liabilities_equity')</td>
                                <td class="px-6 py-3 text-sm font-bold text-right text-green-600">৳{{ number_format($data['total_liabilities_equity'], 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-6 px-6 py-4 rounded-2xl bg-white shadow-sm flex items-center justify-between">
            <span class="text-sm font-medium text-gray-700">@lang('accounting.assets_eq_q')</span>
            <span class="text-sm font-bold {{ abs($data['difference']) < 0.01 ? 'text-green-600' : 'text-red-600' }}">
                {{ abs($data['difference']) < 0.01 ? __('accounting.assets_eq_ok') : __('accounting.assets_eq_diff', ['amt' => '৳'.number_format($data['difference'], 2)]) }}
            </span>
        </div>
    </div>
</div>
@endsection
