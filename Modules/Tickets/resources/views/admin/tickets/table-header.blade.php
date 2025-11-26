<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
    <div class="mb-4 sm:mb-0">
        <div class="flex flex-wrap gap-3">
            <button type="button" onclick="window.location='{{ route('admin.tickets.create') }}'"
                class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:bg-primary-700 active:bg-primary-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition ease-in-out duration-150 dark:focus:ring-offset-gray-800">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                {{ t('new_ticket') }}
            </button>
        </div>
    </div>
    <div class="flex flex-wrap gap-3">
        <button type="button" onclick="refreshTable()"
            class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                </path>
            </svg>
            {{ t('refresh') }}
        </button>
        <div class="relative inline-block text-left">
            <button type="button" onclick="toggleQuickFilters()"
                class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                    </path>
                </svg>
                {{ t('quick_filters') }}
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div id="quick-filters-menu"
                class="hidden absolute right-0 z-10 mt-2 w-56 origin-top-right rounded-md bg-white dark:bg-gray-700 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                <div class="py-1">
                    <a href="#" onclick="applyFilter('status', 'open')"
                        class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">{{
                        t('open_tickets') }}</a>
                    <a href="#" onclick="applyFilter('status', 'pending')"
                        class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">{{
                        t('pending_tickets') }}</a>
                    <a href="#" onclick="applyFilter('priority', 'high')"
                        class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">{{
                        t('high_priority') }}</a>
                    <a href="#" onclick="applyFilter('admin_viewed', '0')"
                        class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">{{
                        t('unread_tickets') }}</a>
                    <hr class="border-gray-200 dark:border-gray-600">
                    <a href="#" onclick="clearFilters()"
                        class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">{{
                        t('clear_all_filters') }}</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Livewire Bulk Actions Modal -->
@if($showBulkActions)
<div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeBulkModal"></div>
        <div
            class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="flex items-start justify-between mb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="modal-title">
                        {{ $getBulkActionTitle() }}
                    </h3>
                    <button type="button" wire:click="closeBulkModal"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="space-y-4">
                    @if($bulkActionType === 'delete')
                    <div
                        class="bg-danger-50 dark:bg-danger-900/20 border border-danger-200 dark:border-danger-800 rounded-md p-4">
                        <div class="flex">
                            <svg class="w-5 h-5 text-danger-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                    clip-rule="evenodd" />
                            </svg>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-danger-800 dark:text-danger-200">
                                    {{ t('confirm_deletion') }}
                                </h3>
                                <div class="mt-2 text-sm text-danger-700 dark:text-danger-300">
                                    {{ t('are_you_sure_you_want_to_delete') }} {{ count($selectedTickets) }} {{
                                    t('ticket_s') }}
                                    {{ t('this_action_cannot_be_undone') }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @elseif($bulkActionType === 'status')
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{
                            t('new_status') }}</label>
                        <select wire:model="bulkActionValue"
                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                            <option value="">{{ t('select_status') }}</option>
                            <option value="open">{{ t('open') }}</option>
                            <option value="pending">{{ t('pending') }}</option>
                            <option value="answered">{{ t('answered') }}</option>
                            <option value="on_hold">{{ t('on_hold') }}</option>
                            <option value="closed">{{ t('closed') }}</option>
                        </select>
                    </div>
                    @elseif($bulkActionType === 'priority')
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{
                            t('new_priority') }}</label>
                        <select wire:model="bulkActionValue"
                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                            <option value="">{{ t('select_priority') }}</option>
                            <option value="low">{{ t('low') }}</option>
                            <option value="medium">{{ t('medium') }}</option>
                            <option value="high">{{ t('high') }}</option>
                            <option value="urgent">{{ t('urgent') }}</option>
                        </select>
                    </div>
                    @elseif($bulkActionType === 'assign')
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{
                            t('assign_tickets') }}</label>
                        <div class="space-y-2">
                            @foreach($adminUsers as $admin)
                            <label class="flex items-center space-x-3">
                                <input type="checkbox" wire:model="selectedAssignees" value="{{ $admin->id }}"
                                    class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                                <span class="text-sm text-gray-900 dark:text-gray-100">{{ $admin->name }} ({{
                                    $admin->email }})</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-4 space-x-3">
                        <button type="button" wire:click="addAssignees(@json($selectedAssignees))"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                            {{ t('add_assignees') }}
                        </button>
                        <button type="button" wire:click="removeAssignees(@json($selectedAssignees))"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-danger-600 hover:bg-danger-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-danger-500">
                            {{ t('remove_assignees') }}
                        </button>
                    </div>
                    @endif

                    <div
                        class="bg-info-50 dark:bg-info-900/20 border border-info-200 dark:border-info-800 rounded-md p-3">
                        <div class="flex">
                            <svg class="w-5 h-5 text-info-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd" />
                            </svg>
                            <div class="ml-3">
                                <div class="text-sm text-info-700 dark:text-info-300">
                                    {{ count($selectedTickets) }} {{ t('ticket_selected') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" wire:click="executeBulkAction"
                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 {{ $bulkActionType === 'delete' ? 'bg-danger-600 hover:bg-danger-700 focus:ring-danger-500' : 'bg-primary-600 hover:bg-primary-700 focus:ring-primary-500' }} text-base font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm">
                    @if($bulkActionType === 'delete')
                    {{ t('delete_tickets') }}
                    @else
                    {{ t('apply_changes') }}
                    @endif
                </button>
                <button type="button" wire:click="closeBulkModal"
                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-700 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    {{ t('cancel') }}
                </button>
            </div>
        </div>
    </div>
</div>
@endif

<script>
    function refreshTable() {
    // Refresh PowerGrid table
    if (window.Livewire) {
        const component = window.Livewire.find('admin-tickets-table');
        if (component) {
            component.call('$refresh');
        } else {
            // Fallback for newer Livewire versions
            window.Livewire.dispatch('$refresh');
        }
    }
}

function toggleQuickFilters() {
    const menu = document.getElementById('quick-filters-menu');
    menu.classList.toggle('hidden');
}

function applyFilter(field, value) {
    // Apply filters via PowerGrid using modern Livewire patterns
    if (window.Livewire) {
        const component = window.Livewire.find('admin-tickets-table');
        if (component) {
            // Set the filter value directly on the component
            component.set('filters.' + field, value);
        } else {
            // Fallback dispatch method
            window.Livewire.dispatch('applyFilter', { field: field, value: value });
        }
    }
    // Close dropdown
    document.getElementById('quick-filters-menu').classList.add('hidden');
}

function clearFilters() {
    // Clear filters via PowerGrid
    if (window.Livewire) {
        const component = window.Livewire.find('admin-tickets-table');
        if (component) {
            // Clear all filters
            component.call('clearFilters');
        } else {
            // Fallback dispatch method
            window.Livewire.dispatch('clearFilters');
        }
    }
    // Close dropdown
    document.getElementById('quick-filters-menu').classList.add('hidden');
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const menu = document.getElementById('quick-filters-menu');
    const button = event.target.closest('button');

    if (!button || button.onclick.toString().indexOf('toggleQuickFilters') === -1) {
        menu.classList.add('hidden');
    }
});
</script>