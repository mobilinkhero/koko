@props(['status'])

@php
$classes = match ($status) {
'open' => 'bg-info-100 text-info-700 dark:bg-info-900/50 dark:text-info-300 border border-info-200
dark:border-info-800',
'answered' => 'bg-success-100 text-success-700 dark:bg-success-900/50 dark:text-success-300 border border-success-200
dark:border-success-800',
'on_hold' => 'bg-warning-100 text-warning-700 dark:bg-warning-900/50 dark:text-warning-300 border border-warning-200
dark:border-warning-800',
'closed' => 'bg-gray-100 text-gray-700 dark:bg-gray-900/50 dark:text-gray-300 border border-gray-200
dark:border-gray-800',
default => 'bg-gray-100 text-gray-700 dark:bg-gray-900/50 dark:text-gray-300 border border-gray-200
dark:border-gray-800',
};
@endphp

<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $classes }}">
    {{ t($status) }}
</span>