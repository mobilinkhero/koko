<x-app-layout>
        <x-breadcrumb :items="[
        ['label' => t('dashboard'), 'route' => tenant_route('tenant.dashboard')],
        ['label' => t('tickets'), 'route' => tenant_route('tenant.tickets.index')],
        ['label' => t('ticket_details')]
]" />
    <div class="flex sm:flex-row flex-col gap-4 item-start sm:items-center justify-between mb-4">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-300">
            {{ t('ticket') }}{{ $ticket->ticket_number }}
        </h2>
        <div class="flex justify-start mb-3 lg:px-0 items-center gap-2">
            <x-button.secondary href="{{ tenant_route('tenant.tickets.index') }}">
                <x-heroicon-o-arrow-small-left class="w-4 h-4 mr-1" />{{ t('back_to_tickets') }}
            </x-button.secondary>
            @if ($ticket->status !== 'closed')
            <form action="{{ tenant_route('tenant.tickets.close', [$ticket->id]) }}" method="POST">
                @csrf
                <x-button.green type="submit">
                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>{{ t('close_ticket') }}
                </x-button.green>
            </form>
            @else
            <form action="{{ tenant_route('tenant.tickets.reopen', [$ticket->id]) }}" method="POST">
                @csrf
                <x-button.primary type="submit">
                    <x-heroicon-s-arrow-path class="w-5 h-5 mr-1" />{{ t('reopen_ticket') }}
                </x-button.primary>
            </form>
            @endif
        </div>
    </div>

    <div>
        <!-- Enhanced Header -->
        <div class="mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    @livewire('tickets::client.ticket-details', ['ticket' => $ticket])
                </div>

                <!-- Enhanced Sidebar -->
                <div class="space-y-6">
                    <!-- Ticket Information -->
                    <x-card class="rounded-lg shadow-sm">
                        <x-slot:header>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 flex items-center">
                                <div class="bg-primary-100 dark:bg-primary-900/60 p-2 rounded-lg mr-3 shadow-sm">
                                    <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                {{ t('ticket_information') }}
                            </h3>
                        </x-slot:header>

                        <x-slot:content>
                            <div class="grid grid-cols-2 gap-4 mb-6">
                                <div>
                                    <div
                                        class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2 font-semibold">
                                        {{ t('status') }}</div>
                                    @php
                                    $statusColors = [
                                    'open' =>
                                    'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200',
                                    'answered' =>
                                    'bg-info-100 text-info-800 dark:bg-info-900 dark:text-info-200',
                                    'on_hold' =>
                                    'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
                                    'closed' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
                                    ];
                                    $color =
                                    $statusColors[$ticket->status] ??
                                    'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200';
                                    @endphp
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold {{ $color }}">
                                        {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                    </span>
                                </div>

                                <div>
                                    <div
                                        class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2 font-semibold">
                                        {{ t('priority') }}</div>
                                    @php
                                    $priorityColors = [
                                    'low' =>
                                    'bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-200',
                                    'medium' =>
                                    'bg-warning-100 text-warning-800 dark:bg-warning-900 dark:text-warning-200',
                                    'high' => 'bg-danger-100 text-danger-800 dark:bg-danger-900 dark:text-danger-200',
                                    ];
                                    $color =
                                    $priorityColors[$ticket->priority] ??
                                    'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200';
                                    @endphp
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold {{ $color }}">
                                        {{ ucfirst($ticket->priority) }}
                                    </span>
                                </div>

                                <div>
                                    <div
                                        class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2 font-semibold">
                                        {{ t('department') }}</div>
                                    <div class=" text-sm text-gray-900 dark:text-gray-100">
                                        {{ $ticket->department?->name ?? 'N/A' }}
                                    </div>
                                </div>

                                <div>
                                    <div
                                        class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2 font-semibold">
                                        {{ t('replies') }}</div>
                                    <div class="text-sm text-gray-900 dark:text-gray-100">
                                        {{ $ticket->replies->count() }}
                                    </div>
                                </div>

                                <div>
                                    <div
                                        class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2 font-semibold">
                                        {{ t('created') }}</div>
                                    <div class=" text-sm text-gray-900 dark:text-gray-100">
                                        {{ format_date_time($ticket->created_at)}}
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $ticket->created_at->diffForHumans() }}
                                    </div>
                                </div>

                                <div>
                                    <div
                                        class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2 font-semibold">
                                        {{ t('last_updatedd') }}</div>
                                    <div class="text-sm text-gray-900 dark:text-gray-100">
                                        {{ format_date_time($ticket->updated_at)}}

                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $ticket->updated_at->diffForHumans() }}
                                    </div>
                                </div>
                            </div>
                        </x-slot:content>
                    </x-card>

                    <!-- Ticket Attachments -->
                    @if ($ticket->attachments)
                    <x-card class="rounded-lg shadow-sm">
                        <x-slot:header>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 flex items-center">
                                <div class="bg-primary-100 dark:bg-primary-900/60 p-2 rounded-lg mr-3 shadow-sm">
                                    <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                {{ t('ticket_information') }}
                            </h3>
                        </x-slot:header>
                        <x-slot:content>
                            @if ($ticket->attachments && count($ticket->attachments) > 0)
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3 flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-primary-500 dark:text-primary-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                    </svg>
                                    {{ t('original_attachments') }}
                                </h4>
                                <ul class="space-y-2">
                                    @foreach ($ticket->attachments as $attachment)
                                    <li>
                                        @if (is_array($attachment))
                                        {{-- New format: array with filename, path, size --}}
                                        <a href="{{ tenant_route('tenant.tickets.download', [
                                                            'ticket' => $ticket->id,
                                                            'filename' => $attachment['filename'],
                                                        ]) }}"
                                            class="text-primary-600 hover:text-primary-500 dark:text-primary-400 text-sm">
                                            {{ $attachment['filename'] }}
                                        </a>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            ({{ number_format($attachment['size'] / 1024, 1) }} KB)
                                        </span>
                                        @else
                                        {{-- Legacy format: just filename string --}}
                                        <a href="{{ tenant_route('tenant.tickets.download', [
                                                            'ticket' => $ticket->id,
                                                            'filename' => $attachment,
                                                        ]) }}"
                                            class="text-primary-600 hover:text-primary-500 dark:text-primary-400 text-sm">
                                            {{ $attachment }}
                                        </a>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">(File)</span>
                                        @endif
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif
                        </x-slot:content>
                    </x-card>
                    @endif
                </div>
            </div>
        </div>
    </div>


    @push('scripts')
    <script>
        function printTicket() {
                window.print();
            }

            // Auto-scroll to reply form if hash is present
            document.addEventListener('DOMContentLoaded', function() {
                if (window.location.hash === '#reply-form') {
                    const replyForm = document.getElementById('reply-form');
                    if (replyForm) {
                        replyForm.scrollIntoView({
                            behavior: 'smooth'
                        });
                    }
                }
            });

            @php
                function formatBytes($size, $precision = 2)
                {
                    if ($size > 0) {
                        $size = (int) $size;
                        $base = log($size) / log(1024);
                        $suffixes = [' B', ' KB', ' MB', ' GB', ' TB'];
                        return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
                    }
                    return '0 B';
                }
            @endphp
    </script>
    @endpush
</x-app-layout>