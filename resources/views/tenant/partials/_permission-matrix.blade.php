@php
    $selected = $selectedPermissions ?? [];
@endphp

<div x-data="{ roleIsSuper: @js($superAdmin ?? false) }">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="text-base font-bold text-gray-900">Menu Permissions</h2>
            <p class="text-xs text-gray-500 mt-0.5">Select the modules and actions this role is allowed to access.</p>
        </div>
        <label class="flex items-center space-x-2 cursor-pointer">
            <input type="checkbox" @click="
                let checkboxes = document.querySelectorAll('.perm-check');
                checkboxes.forEach(cb => cb.checked = $event.target.checked);
                checkboxes.forEach(cb => cb.dispatchEvent(new Event('change')));
            " class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
            <span class="text-sm text-gray-600">Select All</span>
        </label>
    </div>

    <div class="space-y-3">
        @foreach($groups as $group)
            <div class="border border-gray-200 rounded-xl p-4 hover:border-purple-200 transition-colors">
                <div class="flex items-center mb-3">
                    <input type="checkbox" class="perm-group w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                    <h3 class="font-semibold text-gray-800 ml-2 text-sm">{{ $group['title'] }}</h3>
                </div>
                @foreach($group['items'] as $item)
                    <div class="ml-5 mb-3">
                        <div class="flex items-center mb-2">
                            <input type="checkbox" id="perm_{{ $item['slug'] }}" class="perm-check perm-module w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                            <label for="perm_{{ $item['slug'] }}" class="ml-2 text-sm font-medium text-gray-700">{{ $item['label'] }}</label>
                        </div>
                        <div class="ml-6 flex flex-wrap gap-3">
                            @foreach($item['permissions'] as $perm)
                                @php
                                    $checked = in_array($item['slug'] . '.' . $perm, $selected, true);
                                @endphp
                                <label class="flex items-center space-x-1.5 cursor-pointer">
                                    <input type="checkbox"
                                           name="permissions[{{ $item['slug'] }}][{{ $perm }}]"
                                           value="1"
                                           class="perm-check w-3.5 h-3.5 text-purple-600 border-gray-300 rounded focus:ring-purple-500"
                                           @checked($checked)>
                                    <span class="text-xs text-gray-600">{{ $permissionMap[$perm] ?? $perm }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
</div>

@push('scripts')
<script>
    (function () {
        function getSlug(cb) {
            return cb.name?.match(/permissions\[([^\]]+)\]/)?.[1];
        }
        document.querySelectorAll('.perm-check').forEach(cb => {
            cb.addEventListener('change', function () {
                let slug = getSlug(this);
                if (slug) {
                    let moduleChecks = document.querySelectorAll(`input[name^="permissions[${slug}]"]`);
                    let anyChecked = [...moduleChecks].some(c => c.checked);
                    let moduleCb = document.getElementById(`perm_${slug}`);
                    if (moduleCb) moduleCb.checked = anyChecked;
                    // parent group
                    let group = moduleCb ? moduleCb.closest('.border') : null;
                    let groupCb = group ? group.querySelector('.perm-group') : null;
                    if (groupCb) {
                        let groupChecks = group.querySelectorAll('.perm-check');
                        groupCb.checked = [...groupChecks].some(c => c.checked);
                    }
                }
            });
        });
        // module checkbox toggles its children
        document.querySelectorAll('.perm-module').forEach(moduleCb => {
            moduleCb.addEventListener('change', function () {
                let slug = this.id.replace(/^perm_/, '');
                document.querySelectorAll(`input[name^="permissions[${slug}]"]`).forEach(c => {
                    c.checked = this.checked;
                });
            });
        });
        // init group-checked state from existing selection
        document.querySelectorAll('.border').forEach(group => {
            let groupCb = group.querySelector('.perm-group');
            if (groupCb) {
                let checks = group.querySelectorAll('.perm-check');
                groupCb.checked = [...checks].some(c => c.checked);
            }
        });
    })();
</script>
@endpush