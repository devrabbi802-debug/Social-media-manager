@extends('layouts.tenant')

@section('title', 'New Journal Entry - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 py-10">
        <div class="bg-white rounded-2xl shadow-sm p-6 sm:p-8">
            <h1 class="text-xl font-bold text-gray-900 mb-1">@lang('accounting.journal_create_title')</h1>
            <p class="text-sm text-gray-500 mb-6">
                @lang('accounting.journal_create_subtitle')
            </p>

            <form method="POST" action="{{ route('accounting.journal.store') }}" x-data="journalForm()" @submit="if(!balanced) { $event.preventDefault(); alert('@lang('accounting.not_balanced_alert')'); }">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">@lang('accounting.date') <span class="text-red-500">*</span></label>
                        <input type="date" name="entry_date" value="{{ old('entry_date', now()->toDateString()) }}" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">@lang('accounting.narration') <span class="text-red-500">*</span></label>
                        <input type="text" name="narration" value="{{ old('narration') }}" placeholder="@lang('accounting.narration_placeholder')" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm" required>
                    </div>
                </div>

                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-bold text-gray-900">@lang('accounting.account_chart')</h3>
                    <button type="button" @click="addRow()" class="text-sm text-purple-600 font-medium hover:underline">@lang('accounting.add_account')</button>
                </div>

                <div class="overflow-x-auto border border-gray-200 rounded-xl">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">@lang('accounting.account')</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">@lang('accounting.debit_amount')</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">@lang('accounting.credit_amount')</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">@lang('accounting.notes')</th>
                                <th class="px-2 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template x-for="(row, index) in rows" :key="index">
                                <tr>
                                    <td class="px-4 py-2">
                                        <select :name="'account_id['+index+']'" x-model="rows[index].account_id" class="w-48 sm:w-56 border border-gray-300 rounded-lg px-2 py-2 text-sm" required>
                                            <option value="">@lang('accounting.account_select')</option>
                                            @foreach($accounts as $type => $list)
                                                <optgroup label="{{ \App\Models\ChartOfAccount::TYPES[$type] }}">
                                                    @foreach($list as $account)
                                                        <option value="{{ $account->id }}">{{ $account->code }} — {{ $account->name }}</option>
                                                    @endforeach
                                                </optgroup>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-4 py-2">
                                        <input type="number" step="0.01" min="0" :name="'debit['+index+']'" x-model="rows[index].debit" @input="recalc()" placeholder="0.00" class="w-28 border border-gray-300 rounded-lg px-2 py-2 text-sm text-right">
                                    </td>
                                    <td class="px-4 py-2">
                                        <input type="number" step="0.01" min="0" :name="'credit['+index+']'" x-model="rows[index].credit" @input="recalc()" placeholder="0.00" class="w-28 border border-gray-300 rounded-lg px-2 py-2 text-sm text-right">
                                    </td>
                                    <td class="px-4 py-2">
                                        <input type="text" :name="'memo['+index+']'" x-model="rows[index].memo" class="w-40 border border-gray-300 rounded-lg px-2 py-2 text-sm" placeholder="(@lang('accounting.optional'))">
                                    </td>
                                    <td class="px-2 py-2 text-center">
                                        <button type="button" @click="removeRow(index)" x-show="rows.length > 1" class="text-red-500 hover:text-red-700">✕</button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td class="px-4 py-3 text-sm font-semibold text-gray-700">@lang('accounting.total')</td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-sm font-bold text-gray-900" x-text="'৳ ' + totalDebit.toFixed(2)"></span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-sm font-bold text-gray-900" x-text="'৳ ' + totalCredit.toFixed(2)"></span>
                                </td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="mt-4 px-4 py-3 rounded-xl text-sm font-medium text-center"
                     :class="balanced ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-600'">
                    <span x-show="balanced">@lang('accounting.balanced_ok')</span>
                    <span x-show="!balanced" data-notbalanced="@lang('accounting.not_balanced_prefix')" x-text="notBalancedText($el)"></span>
                </div>

                <div class="flex items-center justify-between pt-5">
                    <a href="{{ route('accounting.journal.index') }}" class="text-sm text-gray-500 hover:text-gray-700">@lang('accounting.cancel')</a>
                    <button type="submit" class="px-5 py-2.5 bg-purple-600 text-white rounded-xl text-sm font-medium hover:bg-purple-700">@lang('accounting.post_entry')</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function journalForm() {
        return {
            rows: [{ account_id: '', debit: '', credit: '', memo: '' }, { account_id: '', debit: '', credit: '', memo: '' }],
            totalDebit: 0,
            totalCredit: 0,
            balanced: false,
            addRow() {
                this.rows.push({ account_id: '', debit: '', credit: '', memo: '' });
            },
            removeRow(index) {
                this.rows.splice(index, 1);
                this.recalc();
            },
            recalc() {
                let debit = 0, credit = 0;
                this.rows.forEach(r => {
                    debit += parseFloat(r.debit) || 0;
                    credit += parseFloat(r.credit) || 0;
                });
                this.totalDebit = debit;
                this.totalCredit = credit;
                this.balanced = debit > 0 && Math.abs(debit - credit) < 0.01;
            },
            notBalancedText(el) {
                const diff = '৳ ' + Math.abs(this.totalDebit - this.totalCredit).toFixed(2);
                return (el.dataset.notbalanced || '') + diff + ')';
            }
        };
    }
</script>
@endpush
@endsection
