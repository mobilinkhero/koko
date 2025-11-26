@props(['options', 'selected', 'userId'])

<!-- resources/views/components/status-modal-dropdown.blade.php -->
@php
// Convert selected to a string to ensure consistent comparison
$selectedKey = (string) $selected;

// Get color mapping from enum
$statusColorMapping = App\Enum\TenantStatus::colorMap();
$defaultColors = App\Enum\TenantStatus::defaultColors();

// Get status colors (fall back to default colors if needed)
$status = $statusColorMapping[$selectedKey] ?? $defaultColors;

// Ensure all required keys exist with fallbacks
$bgColor = $status['bg'] ?? $defaultColors['bg'];
$textColor = $status['text'] ?? $defaultColors['text'];
$dotColor = $status['dot'] ?? $defaultColors['dot'];
$borderColor = $status['border'] ?? $defaultColors['border'];
@endphp

<div class="inline-block text-left" x-data="{ isOpen: false }">
    <!-- Status Badge - Compact but readable -->
    <button type="button" @click="isOpen = true"
        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-md border {{ $borderColor }} {{ $bgColor }} {{ $textColor }} shadow-sm hover:shadow-md transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-opacity-30"
        style="touch-action: manipulation; -webkit-tap-highlight-color: transparent;">
        <span class="inline-block w-2 h-2 rounded-full mr-2 {{ $dotColor }}"></span>
        <span>{{ $options[$selectedKey] ?? 'Select' }}</span>
        <svg class="ml-1.5 h-3.5 w-3.5 opacity-70" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>

    <!-- Modal Dialog - Better balanced size -->
    <div x-show="isOpen" x-cloak class="fixed inset-0 z-50 overflow-hidden" style="display: none;">
        <!-- Overlay with indigo tint -->
        <div class="absolute inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="isOpen = false"></div>

        <!-- Modal panel centered -->
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center">
                <div class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 text-left shadow-xl ring-1 ring-black ring-opacity-5 transition-all sm:my-8 sm:w-full sm:max-w-sm"
                    @click.away="isOpen = false">
                    <!-- Modal header -->
                    <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-base font-medium text-gray-900 dark:text-white">Update Status</h3>
                    </div>

                    <!-- Status options - Improved spacing and readability -->
                    <div class="max-h-80 overflow-y-auto py-3 px-3">
                        <div class="space-y-2">
                            @foreach ($options as $id => $name)
                            @php
                            // Get status colors for this option
                            $optionStatus = $statusColorMapping[$id] ?? $defaultColors;

                            // Selected state classes
                            $isSelected = $selectedKey == $id;
                            $selectedClasses = $isSelected
                            ? 'ring-2 ring-primary-500 dark:ring-primary-400'
                            : 'hover:bg-gray-50 dark:hover:bg-gray-700';
                            @endphp
                            <button type="button" wire:click="statusChanged('{{ $id }}', {{ $userId }})"
                                @click="isOpen = false"
                                class="w-full flex items-center px-4 py-3 rounded-md text-base transition-all duration-150 {{ $selectedClasses }}">
                                <span class="inline-block w-3 h-3 rounded-full mr-3 {{ $optionStatus['dot'] }}"></span>
                                <span
                                    class="{{ $isSelected ? 'font-medium' : 'font-normal' }} {{ $optionStatus['text'] }}">
                                    {{ $name }}
                                </span>

                                @if($isSelected)

                                <svg class="ml-auto h-5 w-5 text-primary-600 dark:text-primary-400"
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                                @endif
                            </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- Modal footer - Better proportioned -->
                    <div
                        class="bg-gray-50 dark:bg-gray-700/50 px-5 py-3 border-t border-gray-200 dark:border-gray-700 flex justify-end">
                        <button type="button"
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 rounded-md border border-gray-300 dark:border-gray-600 transition-colors shadow-sm"
                            @click="isOpen = false">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    [x-cloak] {
        display: none !important;
    }

    /* Modal scrollbar styling */
    .max-h-80::-webkit-scrollbar {
        width: 4px;
    }

    .max-h-80::-webkit-scrollbar-track {
        background: transparent;
    }

    .max-h-80::-webkit-scrollbar-thumb {
        background-color: rgba(99, 102, 241, 0.3);
        border-radius: 4px;
    }

    /* Enhance tap targets for mobile */
    @media (max-width: 640px) {
        button {
            min-height: 42px;
        }
    }
</style>
