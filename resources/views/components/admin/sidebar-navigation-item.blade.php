@props([
'route' => null,
'routeNames' => [],
'icon' => null,
'label' => null,
'tooltip' => null,
'badge' => null,
'permission' => null,
'isActive' => false,
'collapsed' => false,
'class' => '',
])

@php
// Handle both single route and multiple route names
$routeNames = is_array($routeNames) ? $routeNames : (is_string($routeNames) ? [$routeNames] : []);
if ($route) {
$routeNames[] = $route;
}

// Check if current route is active
$isCurrentlyActive = $isActive || (!empty($routeNames) && request()->routeIs($routeNames));

// Define CSS classes
$baseClasses = 'group flex items-center px-4 py-2 text-sm font-medium rounded-r-md transition-colors duration-200';
$activeClasses = 'border-l-4 border-primary-600 bg-primary-50 dark:border-primary-600 text-primary-700 dark:bg-slate-900
dark:text-white';
$inactiveClasses = 'text-gray-600 hover:bg-primary-100 hover:text-primary-800 dark:text-slate-300
dark:hover:bg-slate-700
dark:hover:text-white';

$iconActiveClasses = 'text-primary-600 dark:text-slate-300';
$iconInactiveClasses = 'text-gray-500 group-hover:text-primary-700 dark:text-slate-400 group-hover:dark:text-slate-300';

$linkClasses = $baseClasses . ' ' . ($isCurrentlyActive ? $activeClasses : $inactiveClasses);
$iconClasses = 'mr-4 flex-shrink-0 h-6 w-6 ' . ($isCurrentlyActive ? $iconActiveClasses : $iconInactiveClasses);
@endphp

@if (!$permission || checkPermission($permission))
<a wire:navigate href="{{ $route ? route($route) : '#' }}" class="{{ $linkClasses }}" @if($tooltip && $collapsed)
    data-tippy-content="{{ $tooltip }}" data-tippy-placement="right" @endif>

    @if ($icon && !empty($icon))
    <x-dynamic-component component="{{ $icon }}" class="{{ $class }}" aria-hidden="true" />
    @endif

    <span class="whitespace-nowrap" x-show="!isCollapsed" x-transition:enter.duration.700ms>
        {{ $label }}
    </span>

    @if ($badge)
    <span
        class="ml-auto inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-primary-500 bg-primary-100 dark:bg-primary-700 rounded-full">
        {{ $badge }}
    </span>
    @endif
</a>
@endif