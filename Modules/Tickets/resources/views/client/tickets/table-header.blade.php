<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
    <div class="mb-4 sm:mb-0">
        <div class="flex flex-wrap gap-3">
            <a href="{{ tenant_route('tenant.tickets.create') }}"
                class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:bg-primary-700 active:bg-primary-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition ease-in-out duration-150 dark:focus:ring-offset-gray-800">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                {{ t('new_ticket') }}
            </a>
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
                {{ t(key: 'quick_filters') }}
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
                    <a href="#" onclick="applyFilter('tenant_viewed', '0')"
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



<script>
    function refreshTable() {
    // Refresh PowerGrid table
    if (window.Livewire) {
        const component = window.Livewire.find('client-tickets-table');
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
        const component = window.Livewire.find('client-tickets-table');
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
        const component = window.Livewire.find('client-tickets-table');
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