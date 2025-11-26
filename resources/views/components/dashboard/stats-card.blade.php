@props([
'title' => '',
'value' => null,
'suffix' => '',
'limit' => null,
'suffix_limit' => '',
'subtitle' => null,
'action' => null,
'color' => 'blue',
'icon' => null,
'progress' => null,
'bg' => false
])

@php
$colorClasses = [
'blue' => 'text-info-600 dark:text-info-400',
'emerald' => 'text-emerald-600 dark:text-emerald-400',
'purple' => 'text-purple-600 dark:text-purple-400',
'amber' => 'text-warning-600 dark:text-warning-400',
'indigo' => 'text-primary-600 dark:text-primary-400',
'red' => 'text-danger-600 dark:text-danger-400',
'cyan' => 'text-cyan-600 dark:text-cyan-400',
'orange' => 'text-orange-600 dark:text-orange-400',
'rose' => 'text-rose-600 dark:text-rose-400',
];

$bgClasses = [
'blue' => 'bg-info-100 dark:bg-info-900/30',
'emerald' => 'bg-emerald-100 dark:bg-emerald-900/30',
'purple' => 'bg-purple-100 dark:bg-purple-900/30',
'amber' => 'bg-warning-100 dark:bg-warning-900/30',
'indigo' => 'bg-primary-100 dark:bg-primary-900/30',
'red' => 'bg-danger-100 dark:bg-danger-900/30',
'cyan' => 'bg-cyan-100 dark:bg-cyan-900/30',
'orange' => 'bg-orange-100 dark:bg-orange-900/30',
'rose' => 'bg-rose-100 dark:bg-rose-900/30',
];

$progressBarClasses = [
'blue' => 'bg-info-500',
'emerald' => 'bg-emerald-500',
'purple' => 'bg-purple-500',
'amber' => 'bg-warning-500',
'indigo' => 'bg-primary-500',
'red' => 'bg-danger-500',
'cyan' => 'bg-cyan-500',
'orange' => 'bg-orange-500',
'rose' => 'bg-rose-500',
];

$buttonClasses = [
'blue' => 'text-info-600 hover:text-info-700 dark:text-info-400 dark:hover:text-info-300',
'emerald' => 'text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300',
'purple' => 'text-purple-600 hover:text-purple-700 dark:text-purple-400 dark:hover:text-purple-300',
'amber' => 'text-warning-600 hover:text-warning-700 dark:text-warning-400 dark:hover:text-warning-300',
'indigo' => 'text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300',
'red' => 'text-danger-600 hover:text-danger-700 dark:text-danger-400 dark:hover:text-danger-300',
'cyan' => 'text-cyan-600 hover:text-cyan-700 dark:text-cyan-400 dark:hover:text-cyan-300',
'orange' => 'text-orange-600 hover:text-orange-700 dark:text-orange-400 dark:hover:text-orange-300',
'rose' => 'text-rose-600 hover:text-rose-700 dark:text-rose-400 dark:hover:text-rose-300',
];

$iconBgClass = $bg ? $bgClasses[$color] : 'bg-white dark:bg-gray-800';
@endphp

<div
    class="bg-white ring-1 ring-slate-300 rounded-lg dark:bg-gray-800 dark:ring-slate-600 p-6 hover:shadow-md transition-all duration-200 group">
    <div class="flex items-start justify-between">
        <div class="flex-1">
            <h3 class="text-lg font-medium text-slate-800 dark:text-slate-200">{{ $title }}</h3>
            @if($value !== null)
            <div class="flex items-baseline mt-1 space-x-2">
                <span class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $value }}{{ $suffix }}</span>
                @if($limit !== null)
                <span class="text-lg text-slate-600 dark:text-slate-400">/</span>
                <span class="text-lg font-semibold text-slate-700 dark:text-slate-300">{{ $limit }}{{ $suffix_limit
                    }}</span>
                @endif
            </div>
            @endif
        </div>
        @if($icon)
        <div class="ml-4 rounded-lg {{ $iconBgClass }} p-3">
            {{ $icon }}
        </div>
        @endif
    </div>

    @if($progress !== null)
    <div class="mb-4">
        <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2">
            <div class="{{ $progressBarClasses[$color] }} h-2 rounded-full transition-all duration-300"
                style="width: {{ $progress }}%"></div>
        </div>
    </div>
    @endif

    <div class="flex items-center justify-between">
        @if($subtitle)
        <span class="text-sm text-slate-600 dark:text-slate-400">{{ $subtitle }}</span>
        @else
        <span></span>
        @endif

        @if($action && isset($attributes['href']))
        <a href="{{ $attributes['href'] }}"
            class="{{ $buttonClasses[$color] }} text-sm font-medium hover:underline transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-{{ $color }}-500">
            {{ $action }}
        </a>
        @elseif($action)
        <button
            class="{{ $buttonClasses[$color] }} text-sm font-medium hover:underline transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-{{ $color }}-500">
            {{ $action }}
        </button>
        @endif
    </div>
</div>