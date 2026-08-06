@extends('layouts.tenant')

@section('title', 'Chart of Accounts - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">@lang('accounting.chart_title')</h1>
                    <p class="text-gray-600 text-sm">@lang('accounting.chart_subtitle')</p>
                </div>
                <a href="{{ route('accounting.chart-of-accounts.create') }}" class="px-4 py-2 bg-purple-600 text-white rounded-xl text-sm font-medium hover:bg-purple-700">@lang('accounting.new_account')</a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @foreach($groups as $type => $group)
                <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h3 class="font-bold text-gray-900">
                            {{ $group['label'] }}
                            <span class="text-xs text-gray-400 font-normal ml-2">{{ $group['accounts']->count() }}</span>
                        </h3>
                        @if ($type === 'asset')
                            <span class="text-xs font-semibold text-green-600">৳{{ number_format($group['total'], 2) }}</span>
                        @elseif ($type === 'liability' || $type === 'equity')
                            <span class="text-xs font-semibold text-red-600">৳{{ number_format($group['total'], 2) }}</span>
                        @endif
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($group['accounts'] as $account)
                                    @php $canDelete = !$account->is_system && $account->lines_count === 0; @endphp
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-3 w-16">
                                            <span class="text-xs font-mono text-gray-400">{{ $account->code }}</span>
                                        </td>
                                        <td class="px-2 py-3">
                                            <p class="text-sm font-medium text-gray-900">{{ $account->name }}</p>
                                            @if($account->description)
                                                <p class="text-xs text-gray-500">{{ $account->description }}</p>
                                            @endif
                                            @if(! $account->is_active)
                                                <span class="text-xs text-gray-400">(@lang('accounting.inactive'))</span>
                                            @endif
                                            @if($account->is_system)
                                                <span class="text-xs text-gray-400">· @lang('accounting.system_account')</span>
                                            @elseif($account->lines_count > 0)
                                                <span class="text-xs text-gray-400">· @lang('accounting.has_transactions')</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-3 text-right">
                                            <span class="text-sm font-semibold text-gray-700">৳{{ number_format($account->balance, 2) }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-right whitespace-nowrap">
                                            <div class="flex justify-end gap-1">
                                                <a href="{{ route('accounting.chart-of-accounts.edit', $account) }}" class="p-1.5 rounded-lg text-purple-600 hover:bg-purple-50" title="@lang('accounting.edit')">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                </a>
                                                <form method="POST" action="{{ route('accounting.chart-of-accounts.destroy', $account) }}" onsubmit="return confirm('@lang('accounting.confirm_delete')')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-1.5 rounded-lg {{ $canDelete ? 'text-red-500 hover:bg-red-50' : 'text-gray-300 cursor-not-allowed' }}"
                                                            title="{{ $canDelete ? __('accounting.delete') : ($account->is_system ? __('accounting.cannot_delete_system') : __('accounting.cannot_delete_used')) }}"
                                                            {{ $canDelete ? '' : 'disabled' }}>
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-6 py-8 text-center text-gray-500">@lang('accounting.no_accounts')</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
