@props([
    'title' => null,
    'icon' => null,
    'collapsed' => false,
    'expandable' => true,
    'defaultExpanded' => true,
    'sectionId' => null,
])

@php
    $sectionId = $sectionId ?? Str::slug($title ?? 'section');
@endphp

@if ($expandable && $title)
    <div x-data="{
        expanded: {{ $defaultExpanded ? 'true' : 'false' }},
        toggleSection() {
            this.expanded = !this.expanded;
            // Store state in localStorage
            localStorage.setItem('sidebar_section_{{ $sectionId }}', this.expanded ? '1' : '0');
        },
        init() {
            // Restore state from localStorage
            const stored = localStorage.getItem('sidebar_section_{{ $sectionId }}');
            if (stored !== null) {
                this.expanded = stored === '1';
            }
        }
    }" class="mb-2">

        <!-- Expandable Section Header -->
        <button
            @click="toggleSection()"
            x-show="!isCollapsed"
            class="w-full flex items-center justify-between px-4 py-3 text-sm font-semibold text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg transition-colors duration-200 group">

            <div class="flex items-center space-x-3">
                @if ($icon && !empty($icon))
                    <x-dynamic-component
                        component="{{ $icon }}"
                        class="h-5 w-5 text-gray-500 dark:text-slate-400 group-hover:text-gray-700 dark:group-hover:text-slate-300" />
                @endif
                <span class="whitespace-nowrap">{{ $title }}</span>
            </div>

            <!-- Expand/Collapse Icon -->
            <x-heroicon-o-chevron-down
                class="h-4 w-4 text-gray-400 transition-transform duration-200"
                x-bind:class="{ 'rotate-180': expanded }" />
        </button>

        <!-- Expandable Content -->
        <div
            x-show="expanded"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 max-h-0"
            x-transition:enter-end="opacity-100 max-h-screen"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 max-h-screen"
            x-transition:leave-end="opacity-0 max-h-0"
            class="overflow-hidden"
            style="display: none;">

            <div class="ml-2 border-l-2 border-gray-200 dark:border-slate-600 pl-4 space-y-1">
                {{ $slot }}
            </div>
        </div>

        <!-- Collapsed state: Show icon only with tooltip -->
        <div x-show="isCollapsed" class="px-4 py-2">
            @if ($icon && !empty($icon))
                <x-dynamic-component
                    component="{{ $icon }}"
                    class="h-6 w-6 text-gray-500 dark:text-slate-400 mx-auto"
                    data-tippy-content="{{ $title }}"
                    data-tippy-placement="right" />
            @endif
        </div>
    </div>
@elseif ($title)
    <!-- Non-expandable section (original behavior) -->
    <p
        x-show="!isCollapsed"
        x-transition:enter.duration.700ms
        class="text-sm text-gray-500 dark:text-slate-400 font-medium px-5 py-4 whitespace-nowrap">
        {{ $title }}
    </p>
    {{ $slot }}
@else
    <!-- No title, just content -->
    {{ $slot }}
@endif
