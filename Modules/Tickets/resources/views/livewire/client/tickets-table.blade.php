<div>
    @include('tickets::client.tickets.table-header')

    <!-- PowerGrid Table -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
        <div id="pg-table-container">
            @if($this->hasData())
            {{ $this->table }}
            @else
            <div class="text-center py-12">
                <div class="mb-4">
                    <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                            d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">{{ t('no_tickets_found') }}</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-6">{{ t('you_havent_submit_tickets') }}</p>
                <a href="{{ tenant_route('tenant.tickets.create') }}"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-info-600 hover:bg-info-700 focus:outline-none focus:ring-2 focus:ring-info-500 focus:ring-offset-2">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    {{ t('create_your_first_ticket') }}
                </a>
            </div>
            @endif
        </div>
    </div>

    <!-- Table Footer Info -->
    @if($this->hasData())
    <div
        class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mt-4 px-4 py-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
        <div class="text-sm text-gray-500 dark:text-gray-400">
            {{ t('showing') }} {{ $this->getCurrentPageResults() }} of {{ $this->getTotalResults() }} {{ t('results') }}
        </div>
        <div class="text-sm text-gray-500 dark:text-gray-400">
            {{ t('last_updated') }} <span id="last-updated">{{ now()->format('M j, Y g:i A') }}</span>
        </div>
    </div>
    @endif
</div>

@push('styles')
<style>
    /* PowerGrid Custom Styles with Tailwind-compatible values */
    .pg-table {
        margin-bottom: 0;
        @apply bg-white dark: bg-gray-800;
    }

    .pg-table th {
        @apply bg-gray-50 dark: bg-gray-700 border-gray-200 dark:border-gray-600 font-semibold text-sm text-gray-700 dark:text-gray-300;
        white-space: nowrap;
    }

    .pg-table td {
        @apply border-gray-200 dark: border-gray-600 text-sm text-gray-900 dark:text-gray-100;
        vertical-align: middle;
    }

    .pg-table tbody tr:hover {
        @apply bg-gray-50 dark: bg-gray-700;
    }

    /* Search and Filter Styles */
    .pg-search input {
        @apply rounded-md border-gray-300 dark: border-gray-600 focus:border-info-500 focus:ring-info-500 dark:bg-gray-700 dark:text-gray-100;
    }

    .pg-filters select,
    .pg-filters input {
        @apply rounded-md border-gray-300 dark: border-gray-600 text-sm focus:border-info-500 focus:ring-info-500 dark:bg-gray-700 dark:text-gray-100;
    }

    /* Loading Overlay */
    .pg-loading {
        position: relative;
    }

    .pg-loading::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        @apply bg-white/80 dark: bg-gray-800/80 flex items-center justify-center;
        z-index: 999;
    }

    /* Animation for table updates */
    .pg-table tbody tr {
        @apply transition-colors duration-300;
    }

    /* Responsive table */
    @media (max-width: 768px) {
        .pg-table {
            @apply text-xs;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-refresh table every 30 seconds
        let autoRefreshInterval;

        function startAutoRefresh() {
            autoRefreshInterval = setInterval(function() {
                if (typeof window.Livewire !== 'undefined') {
                    // Update last updated time
                    const lastUpdatedEl = document.getElementById('last-updated');
                    if (lastUpdatedEl) {
                        lastUpdatedEl.textContent = new Date().toLocaleDateString('en-US', {
                            month: 'short',
                            day: 'numeric',
                            year: 'numeric',
                            hour: 'numeric',
                            minute: '2-digit',
                            hour12: true
                        });
                    }

                    // Refresh the table
                    window.Livewire.emit('pg:refresh');
                }
            }, 30000); // 30 seconds
        }

        function stopAutoRefresh() {
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
            }
        }

        // Start auto-refresh
        startAutoRefresh();

        // Stop auto-refresh when page is not visible
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                stopAutoRefresh();
            } else {
                startAutoRefresh();
            }
        });

        // Handle table row clicks for navigation
        document.addEventListener('click', function(e) {
            const row = e.target.closest('tr[data-ticket-id]');
            if (row && !e.target.closest('button, a, .btn')) {
                const ticketId = row.getAttribute('data-ticket-id');
                if (ticketId) {
                    window.location.href = "{{ tenant_route('tenant.tickets.show', ['ticket' => ':id']) }}".replace(':id', ticketId);
                }
            }
        });

        // Handle filter changes
        document.addEventListener('change', function(e) {
            if (e.target.matches('.pg-filter')) {
                // Add loading state
                const table = document.querySelector('#pg-table-container');
                if (table) {
                    table.classList.add('pg-loading');

                    setTimeout(function() {
                        table.classList.remove('pg-loading');
                    }, 1000);
                }
            }
        });

        // Close ticket confirmation
        window.closeTicketConfirm = function(ticketId, ticketNumber) {
            return confirm(`t('close_ticket_confirmation') #${ticketNumber}?`);
        };

        // Success/Error message handling
        window.addEventListener('ticket-closed', function(e) {
            showToast('success', t('ticket_closed_successfully'));
            // Refresh the table after a short delay
            setTimeout(function() {
                if (typeof window.Livewire !== 'undefined') {
                    window.Livewire.emit('pg:refresh');
                }
            }, 1000);
        });

        window.addEventListener('ticket-error', function(e) {
            showToast('error', e.detail.message || t('an_error_occurred'));
        });

        // Tailwind-based toast notification function
        function showToast(type, message) {
            const isSuccess = type === 'success';
            const iconPath = isSuccess
                ? 'M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z'
                : 'M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z';

            const bgColor = isSuccess ? 'bg-success-500' : 'bg-danger-500';

            const toastHtml = `
                <div class="flex items-center w-full max-w-xs p-4 text-white ${bgColor} rounded-lg shadow-lg transform transition-transform duration-300 ease-in-out" role="alert">
                    <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 rounded-lg">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="${iconPath}" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3 text-sm font-normal">${message}</div>
                    <button type="button" class="ml-auto -mx-1.5 -my-1.5 text-white hover:text-gray-200 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-white/20 inline-flex h-8 w-8" onclick="this.parentElement.remove()">
                        <span class="sr-only">Close</span>
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </div>
            `;

            // Create or get toast container
            let toastContainer = document.querySelector('.toast-container');
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.className = 'fixed top-4 right-4 z-50 space-y-2';
                document.body.appendChild(toastContainer);
            }

            // Add toast
            toastContainer.insertAdjacentHTML('beforeend', toastHtml);

            // Auto-remove toast after 5 seconds
            const toastElement = toastContainer.lastElementChild;
            setTimeout(function() {
                if (toastElement && toastElement.parentNode) {
                    toastElement.style.transform = 'translateX(100%)';
                    setTimeout(function() {
                        if (toastElement && toastElement.parentNode) {
                            toastElement.remove();
                        }
                    }, 300);
                }
            }, 5000);
        }
    });
</script>
@endpush