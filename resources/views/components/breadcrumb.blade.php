@props(['items' => [], 'size' => 'normal', 'variant' => 'standard', 'width' => 'full'])

@php
$sizeClasses = [
    'compact' => 'px-2 py-1.5 text-xs',
    'normal' => 'px-3 py-2 text-sm',
    'large' => 'px-4 py-4 text-base'
];

$variantClasses = [
    'standard' => 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-600 shadow-sm',
    'compact' => 'bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600',
    'minimal' => 'bg-transparent border-transparent'
];

$widthClasses = [
    'full' => 'w-full',
    'container' => 'w-full max-w-7xl mx-auto',
    'content' => 'w-full max-w-6xl mx-auto',
    'table' => 'w-full max-w-full overflow-x-auto'
];

// Ensure we have valid string values
$size = $size ?? 'normal';
$variant = $variant ?? 'standard';
$width = $width ?? 'full';

$currentSizeClass = $sizeClasses[$size] ?? $sizeClasses['normal'];
$currentVariantClass = $variantClasses[$variant] ?? $variantClasses['standard'];
$currentWidthClass = $widthClasses[$width] ?? $widthClasses['full'];

// Responsive icon sizes
$iconSize = match($size) {
    'compact' => 'w-3 h-3 sm:w-3 sm:h-3',
    'large' => 'w-4 h-4 sm:w-5 sm:h-5 lg:w-6 lg:h-6',
    default => 'w-4 h-4 sm:w-4 sm:h-4'
};

// Responsive chevron sizes
$chevronSize = match($size) {
    'compact' => 'w-2 h-2 sm:w-2.5 sm:h-2.5',
    'large' => 'w-3 h-3 sm:w-4 sm:h-4',
    default => 'w-2.5 h-2.5 sm:w-3 sm:h-3'
};

// Responsive text sizes
$textSize = match($size) {
    'compact' => 'text-xs sm:text-xs',
    'large' => 'text-sm sm:text-base lg:text-lg',
    default => 'text-xs sm:text-sm'
};

// Responsive spacing
$spacing = match($size) {
    'compact' => 'space-x-0.5 sm:space-x-1',
    'large' => 'space-x-1 sm:space-x-2 lg:space-x-3',
    default => 'space-x-1 sm:space-x-1 md:space-x-2'
};

$marginBottom = $variant === 'compact' ? 'mb-3 sm:mb-4' : 'mb-4 sm:mb-6';
@endphp

<div class="{{ $currentWidthClass }} {{ $marginBottom }}">
    <nav aria-label="breadcrumb" class="w-full">
        <ol class="flex flex-wrap items-center {{ $spacing }} rtl:space-x-reverse {{ $currentVariantClass }} {{ $currentSizeClass }} rounded-lg border">
            @foreach($items as $index => $item)
                @if($loop->first)
                    {{-- Home icon for first item --}}
                    <li class="inline-flex items-center min-w-0">
                        @if(isset($item['route']) && $item['route'])
                            <a href="{{ $item['route'] }}"
                               class="inline-flex items-center font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white transition-colors duration-200 {{ $textSize }} truncate">
                                <x-heroicon-o-home class="{{ $iconSize }} me-1 sm:me-2 flex-shrink-0" />
                                <span class="hidden sm:inline truncate">{{ $item['label'] }}</span>
                                <span class="sm:hidden truncate">{{ Str::limit($item['label'], 8) }}</span>
                            </a>
                        @else
                            <span class="inline-flex items-center font-medium text-gray-700 dark:text-gray-400 {{ $textSize }} truncate">
                                <x-heroicon-o-home class="{{ $iconSize }} me-1 sm:me-2 flex-shrink-0" />
                                <span class="hidden sm:inline truncate">{{ $item['label'] }}</span>
                                <span class="sm:hidden truncate">{{ Str::limit($item['label'], 8) }}</span>
                            </span>
                        @endif
                    </li>
                @elseif($loop->last)
                    {{-- Last item (current page) --}}
                    <li class="inline-flex items-center min-w-0">
                        <x-heroicon-o-chevron-right class="rtl:rotate-180 {{ $chevronSize }} text-primary-400 mx-0.5 sm:mx-1 flex-shrink-0" />
                        <span class="font-medium text-gray-500 dark:text-gray-400 {{ $textSize }} truncate">
                            <span class="hidden sm:inline truncate">{{ $item['label'] }}</span>
                            <span class="sm:hidden truncate">{{ Str::limit($item['label'], 12) }}</span>
                        </span>
                    </li>
                @else
                    {{-- Middle items --}}
                    <li class="inline-flex items-center min-w-0">
                        <x-heroicon-o-chevron-right class="rtl:rotate-180 {{ $chevronSize }} text-primary-400 mx-0.5 sm:mx-1 flex-shrink-0" />
                        @if(isset($item['route']) && $item['route'])
                            <a href="{{ $item['route'] }}"
                               class="font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white transition-colors duration-200 {{ $textSize }} truncate">
                                <span class="hidden sm:inline truncate">{{ $item['label'] }}</span>
                                <span class="sm:hidden truncate">{{ Str::limit($item['label'], 10) }}</span>
                            </a>
                        @else
                            <span class="font-medium text-gray-700 dark:text-gray-400 {{ $textSize }} truncate">
                                <span class="hidden sm:inline truncate">{{ $item['label'] }}</span>
                                <span class="sm:hidden truncate">{{ Str::limit($item['label'], 10) }}</span>
                            </span>
                        @endif
                    </li>
                @endif
            @endforeach
        </ol>
    </nav>
</div>
