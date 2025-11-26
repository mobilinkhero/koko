@props(['value', 'label','count' => t('remaining')])

<span {{ $attributes->merge([
    'class' =>
    'inline-flex items-center p-2 rounded-md text-sm font-medium bg-info-50 text-info-700 dark:bg-info-900/30
    dark:text-info-300 border border-info-200 dark:border-info-800 shadow-sm',
    ]) }}>
    <x-heroicon-s-chart-bar class="h-4 w-4 mr-1.5" />
    {{ $label }}:
    <span class="mx-1 font-bold">{{ $value }}</span>/<span class="ml-1 font-bold">{{ $count }}</span>
</span>