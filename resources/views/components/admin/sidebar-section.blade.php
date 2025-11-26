@props([
    'title' => null,
    'collapsed' => false,
])

@if ($title)
    <p
        @if(!$collapsed) x-show="!isCollapsed" @endif
        x-transition:enter.duration.700ms
        class="text-sm text-gray-500 dark:text-slate-400 font-medium px-5 py-4 whitespace-nowrap">
        {{ $title }}
    </p>
@endif

{{ $slot }}
