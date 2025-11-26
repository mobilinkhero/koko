<span
    {{ $attributes->merge([
        'class' =>
            'inline-flex items-center p-2 rounded-md font-medium bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 shadow-sm',
    ]) }}>
    <x-carbon-infinity-symbol class="h-5 w-5 mr-1.5" />
    {{ $slot }}
</span>
