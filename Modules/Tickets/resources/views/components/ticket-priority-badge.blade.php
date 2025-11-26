@props(['priority'])

@php
$classes = match ($priority) {
'high' => 'bg-danger-100 text-danger-700 dark:bg-danger-900/50 dark:text-danger-300 border border-danger-200
dark:border-danger-800',
'medium' => 'bg-warning-100 text-warning-700 dark:bg-warning-900/50 dark:text-warning-300 border border-warning-200
dark:border-warning-800',
'low' => 'bg-success-100 text-success-700 dark:bg-success-900/50 dark:text-success-300 border border-success-200
dark:border-success-800',
default => 'bg-gray-100 text-gray-700 dark:bg-gray-900/50 dark:text-gray-300 border border-gray-200
dark:border-gray-800',
};
@endphp

<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $classes }}">
    {{ ucfirst($priority) }}
</span>