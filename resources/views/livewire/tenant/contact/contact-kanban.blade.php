<div x-data="{
    draggedRecord: null,
    loadMore() {
        $wire.$refresh();
    }
}">
    <x-card>
        <x-slot:header>
            <div class="flex items-center justify-between flex-wrap gap-4">
                <h1
                    class="text-lg font-semibold text-slate-700 dark:text-slate-300  border-slate-200 dark:border-slate-700">
                    {{ t('contact_kanban_board') ?? 'Contact Kanban Board' }}
                </h1>
                <div class="flex items-center space-x-2">
                    <x-button.primary @click="loadMore()" size="sm"
                        class="flex items-center gap-1 transition-all duration-200">
                        <x-heroicon-o-arrow-path class="w-4 h-4" />
                        {{ t('refresh') ?? 'Refresh' }}
                    </x-button.primary>
                </div>
            </div>

        </x-slot:header>
        {{-- <div class="border-b border-slate-300 px-4 py-5 sm:px-6 dark:border-slate-600">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">
                    {{ t('contact_kanban_board') ?? 'Contact Kanban Board' }}
                </h2>

                <div class="flex items-center space-x-2">
                    <x-button.primary @click="loadMore()" size="sm"
                        class="flex items-center gap-1 transition-all duration-200">
                        <x-heroicon-o-arrow-path class="w-4 h-4" />
                        {{ t('refresh') ?? 'Refresh' }}
                    </x-button.primary>
                </div>
            </div>
        </div> --}}


        <!-- Kanban Board Container -->
        <x-slot:content>

            <div class="kanban-board-container overflow-x-auto h-[calc(100vh-265px)]  scrollbar-visible">
                <div class="kanban-board flex gap-6 pb-6 min-w-max">

                    <!-- Status Columns -->
                    @foreach ($statuses as $status)
                        <div class="kanban-column flex-shrink-0 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-5 w-80 h-[32rem] transition-all duration-300"
                            data-status-id="{{ $status['id'] }}"
                            @dragover.prevent="$el.classList.add('bg-primary-50', 'dark:bg-primary-900/10')"
                            @dragleave="$el.classList.remove('bg-primary-50', 'dark:bg-primary-900/10')"
                            @drop.prevent="
                        $el.classList.remove('bg-primary-50', 'dark:bg-primary-900/10');
                        if (draggedRecord) {
                            $wire.moveRecord(draggedRecord.id, {{ $status['id'] }});
                            draggedRecord = null;
                        }
                     ">

                            <!-- Column Header -->
                            <div class="shadow-sm mb-4 px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-700">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <div class="w-3 h-3 rounded-full"
                                            style="background-color: {{ $status['color'] }}">
                                        </div>
                                        <h3 class="font-semibold text-gray-900 dark:text-gray-100">
                                            {{ $status['title'] }}
                                        </h3>
                                        <span
                                            class="bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400 text-xs px-2 py-0.5 rounded-full">
                                            {{ count($records[$status['id']] ?? []) }}
                                        </span>
                                    </div>
                                </div>

                            </div>

                            <!-- Cards Container -->
                            <div
                                class="kanban-cards space-y-3 overflow-y-auto max-h-[calc(100%-60px)] pr-1 scrollbar-visible">
                                @foreach ($records[$status['id']] ?? [] as $record)
                                    <div class="kanban-card bg-white dark:bg-gray-700 rounded-lg p-4 shadow-sm border border-slate-200 dark:border-slate-600 cursor-move hover:shadow-md hover:border-primary-300 dark:hover:border-primary-700 transition-all duration-200 "
                                        data-record-id="{{ $record['id'] }}" draggable="true"
                                        @dragstart="
                                    draggedRecord = {{ json_encode($record) }};
                                    $event.dataTransfer.effectAllowed = 'move';
                                    $event.target.style.opacity = '0.5';
                                 "
                                        @dragend="
                                    $event.target.style.opacity = '';
                                    draggedRecord = null;
                                 ">

                                        <!-- Card Header -->
                                        <div class="flex items-start justify-between">
                                            <div class="flex items-center gap-2 flex-1 pr-2">
                                                <h4
                                                    class="font-medium text-gray-900 dark:text-gray-100 text-sm truncate flex-1 w-20">
                                                    {{ $record['title'] }}
                                                </h4>

                                                @if ($record['content']['type'])
                                                    <span
                                                        class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-info-100 text-info-800 dark:bg-info-900/30 dark:text-info-300 capitalize flex-shrink-0">
                                                        {{ $record['content']['type'] }}
                                                    </span>
                                                @endif
                                            </div>

                                            <!-- Action Dropdown -->
                                            <div class="relative" x-data="{ open: false }">
                                                <button @click="open = !open"
                                                    class="text-slate-700 hover:text-slate-600 dark:hover:text-slate-300 p-0.5 rounded-full hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors duration-200">
                                                    <x-heroicon-o-ellipsis-horizontal class="w-5 h-5 stroke-2" />
                                                </button>

                                                <div x-show="open" @click.away="open = false"
                                                    x-transition:enter="transition ease-out duration-100"
                                                    x-transition:enter-start="transform opacity-0 scale-95"
                                                    x-transition:enter-end="transform opacity-100 scale-100"
                                                    x-transition:leave="transition ease-in duration-75"
                                                    x-transition:leave-start="transform opacity-100 scale-100"
                                                    x-transition:leave-end="transform opacity-0 scale-95"
                                                    class="absolute right-0 mt-2 w-44 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-slate-200 dark:border-slate-700 z-50">
                                                    <div class="py-1">
                                                        <button
                                                            @click="$wire.viewContact({{ $record['id'] }}); open = false"
                                                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors duration-150">
                                                            {{ t('view_details') ?? 'View Details' }}
                                                        </button>
                                                        @if (checkPermission('tenant.contact.edit'))
                                                            <button
                                                                @click="$wire.editContact({{ $record['id'] }}); open = false"
                                                                class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors duration-150">
                                                                {{ t('edit') ?? 'Edit' }}
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Card Content -->
                                        <div
                                            class="space-y-2 text-xs text-slate-600 dark:text-slate-400 pt-2 border-t border-slate-100 dark:border-slate-700">
                                            @if ($record['content']['company'])
                                                <div class="flex items-center gap-1.5">
                                                    <x-heroicon-o-building-office class="w-3.5 h-3.5 text-slate-500" />
                                                    <span class="truncate">{{ $record['content']['company'] }}</span>
                                                </div>
                                            @endif

                                            @if ($record['content']['phone'])
                                                <div class="flex items-center gap-1.5">
                                                    <x-heroicon-o-phone class="w-3.5 h-3.5 text-slate-500" />
                                                    <span>{{ $record['content']['phone'] }}</span>
                                                </div>
                                            @endif

                                            @if ($record['content']['email'])
                                                <div class="flex items-center gap-1.5">
                                                    <x-heroicon-o-envelope class="w-3.5 h-3.5 text-slate-500" />
                                                    <span class="truncate">{{ $record['content']['email'] }}</span>
                                                </div>
                                            @endif

                                            <button x-data
                                                @click="$wire.initiateChat({{ $record['id'] }}); open = false"
                                                class="inline-flex items-center gap-2 text-success-500 hover:text-success-700 "
                                                title="Initiate Chat">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                                    viewBox="0 0 32 32" class="h-5 w-5">
                                                    <path
                                                        d="M16.01 2.006a13.97 13.97 0 00-12.2 20.96L2 30l7.2-1.8A13.974 13.974 0 1016.01 2.006zm0 25.974c-2.08 0-4.07-.53-5.83-1.53l-.42-.24-4.28 1.07 1.1-4.16-.28-.43A11.96 11.96 0 1116.01 28zm6.41-8.94c-.34-.17-2.01-.99-2.33-1.1-.31-.11-.54-.17-.76.17-.23.34-.88 1.1-1.08 1.32-.2.23-.4.25-.75.08-.34-.17-1.44-.53-2.74-1.7a10.182 10.182 0 01-1.89-2.33c-.2-.34 0-.52.15-.69.15-.16.34-.4.5-.6.17-.2.23-.34.34-.56.12-.23.06-.43 0-.6-.07-.17-.76-1.84-1.04-2.52-.28-.68-.56-.59-.76-.6h-.65c-.22 0-.56.08-.85.4s-1.12 1.1-1.12 2.68 1.15 3.1 1.31 3.32c.17.23 2.27 3.45 5.5 4.83.77.33 1.37.53 1.83.68.77.24 1.46.2 2.01.12.61-.09 1.87-.76 2.13-1.5.27-.74.27-1.37.19-1.5-.07-.13-.3-.2-.63-.36z" />
                                                </svg>
                                                <span
                                                    class="text-xs inline-flex items-center px-2 py-0.5 rounded-md font-medium bg-success-100 text-success-900 dark:bg-success-900/30 dark:text-info-300">
                                                    {{ t('initiate_chat') }}</span>
                                            </button>

                                        </div>
                                    </div>
                                @endforeach

                                <!-- Load More Button -->
                                @if (count($records[$status['id']] ?? []) >= 20)
                                    <div class="text-center py-4">
                                        <button wire:click="loadMoreRecords({{ $status['id'] }})"
                                            class="inline-flex items-center justify-center px-3 py-1.5 text-sm font-medium text-info-600 dark:text-info-400 bg-info-50 dark:bg-info-900/20 hover:bg-info-100 dark:hover:bg-info-900/30 border border-info-200 dark:border-info-800 rounded-xl transition-all duration-200">
                                            <x-heroicon-o-chevron-down class="w-4 h-4 mr-2" />
                                            {{ t('load_more') ?? 'Load More' }}
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </x-slot:content>
    </x-card>
</div>
