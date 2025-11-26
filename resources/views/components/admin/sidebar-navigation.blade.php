@props(['collapsed' => false, 'menuitems' => [], 'setupMenuitems' => []])
<x-admin.sidebar-layout :collapsed="$collapsed">
    {{-- Main Menu Items --}}
    @foreach($menuitems as $menuItem)
    @if($menuItem->type === 'item')
    <x-admin.sidebar-navigation-item :route="$menuItem->route" :route-names="$menuItem->active_routes"
        :icon="$menuItem->icon" class="w-5 h-5 mr-2" :label="t($menuItem->label)" :tooltip="t($menuItem->label)"
        :badge="$menuItem->badge" :permission="$menuItem->permission" :collapsed="$collapsed" />
    @elseif($menuItem->type === 'section')
    <x-admin.sidebar-expandable-section :title="t($menuItem->label)" :icon="$menuItem->icon" :collapsed="$collapsed"
        :section-id="$menuItem->section_id" :default-expanded="$menuItem->default_expanded">

        @foreach($menuItem->children as $childItem)
        <x-admin.sidebar-navigation-item :route="$childItem->route" :route-names="$childItem->active_routes"
            :icon="$childItem->icon" class="w-5 h-5 mr-2" :label="t($childItem->label)" :tooltip="t($childItem->label)"
            :badge="$childItem->badge" :permission="$childItem->permission" :collapsed="$collapsed" />
        @endforeach
    </x-admin.sidebar-expandable-section>
    @endif
    @endforeach

    {{-- Setup Button --}}
    <button x-on:click.prevent="setupMenu = true"
        class="group items-center hidden lg:flex px-4 py-2 text-sm font-medium rounded-r-md text-gray-600 hover:bg-primary-100 hover:text-primary-800 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-white mt-2 w-full">
        <x-heroicon-o-cog-6-tooth data-tippy-content="{{ t('setup') }}" data-tippy-placement="right"
            class="mr-4 flex-shrink-0 h-6 w-6 text-gray-500 group-hover:text-primary-700 dark:text-slate-400 group-hover:dark:text-slate-300"
            aria-hidden="true" />
        <span class="whitespace-nowrap" x-show="!isCollapsed" x-transition:enter.duration.700ms>{{ t('setup') }}</span>
    </button>

    {{-- Mobile Setup Button --}}
    <button x-on:click.prevent="mobileOpen = true"
        class="group lg:hidden flex items-center px-4 py-2 text-sm font-medium rounded-r-md text-gray-600 hover:bg-primary-100 hover:text-primary-800 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-white mt-2 w-full">
        <x-heroicon-o-cog-6-tooth data-tippy-content="{{ t('setup') }}" data-tippy-placement="right"
            class="mr-4 flex-shrink-0 h-6 w-6 text-gray-500 group-hover:text-primary-700 dark:text-slate-400 group-hover:dark:text-slate-300"
            aria-hidden="true" />
        <span class="whitespace-nowrap" x-show="!isCollapsed" x-transition:enter.duration.700ms>{{ t('setup') }}</span>
    </button>

    {{-- Mobile Setup Menu Slot --}}
    @slot('mobileSetupMenu')
    @foreach($setupMenuitems as $setupItem)
    <x-admin.sidebar-navigation-item :route="$setupItem->route" :route-names="$setupItem->active_routes"
        :icon="$setupItem->icon" class="w-5 h-5 mr-2" :label="t($setupItem->label)"
        :permission="$setupItem->permission" />
    @endforeach
    @endslot
</x-admin.sidebar-layout>