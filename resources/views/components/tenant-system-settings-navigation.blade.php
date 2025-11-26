{{-- resources/views/components/dynamic-navigation.blade.php --}}
@props(['config' => 'tenant_settings', 'defaultLabel' => null])

@php
$baseNavigationItems = config("settings-navigation.{$config}", []);

// Apply filters using the millat/laravel-hooks package
$navigationItems = apply_filters("{$config}_navigation", $baseNavigationItems);

$filteredItems = collect($navigationItems)->filter(function ($item) {
return !isset($item['condition']) || eval("return {$item['condition']};");
});

$currentItem = $filteredItems->first(function ($item) {
return request()->routeIs($item['route']);
});

$currentLabel = $currentItem ? t($currentItem['label']) ?? $currentItem['fallback_label'] ?? $currentItem['label'] :
($defaultLabel ?? t('please_select_an_option'));
@endphp

<div {!! $attributes !!}>
    {{-- Mobile Dropdown --}}
    <div class="sm:hidden">
        <x-dropdown width="full" align="top">
            <x-slot:trigger>
                <button type="button"
                    class="relative w-full cursor-default rounded-md border border-slate-300 bg-white py-2 pl-3 pr-10 text-left shadow-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 dark:focus:ring-offset-slate-800">
                    {{ $currentLabel }}
                    <span class="pointer-events-none absolute inset-y-0 right-0 ml-3 flex items-center pr-3">
                        <x-heroicon-m-chevron-up-down class="h-5 w-5 text-slate-400" />
                    </span>
                </button>
            </x-slot:trigger>
            <x-slot:content>
                @foreach ($filteredItems as $item)
                    @if (isset($item['feature_required']))
                        @hasFeature($item['feature_required'])
                            <x-dropdown-link href="{{ tenant_route($item['route']) }}">
                                {{ t($item['label']) ?? $item['fallback_label'] ?? $item['label'] }}
                            </x-dropdown-link>
                        @endhasFeature
                    @else
                        <x-dropdown-link href="{{ tenant_route($item['route']) }}">
                            {{ t($item['label']) ?? $item['fallback_label'] ?? $item['label'] }}
                        </x-dropdown-link>
                    @endif
                @endforeach
            </x-slot:content>
        </x-dropdown>
    </div>

    {{-- Desktop Navigation --}}
    <div class="hidden sm:block">
        <div class="bg-white ring-1 ring-slate-300 sm:rounded-lg dark:bg-transparent dark:ring-slate-600 p-4">
            <div>
                <nav class="flex flex-col gap-1 justify-start" aria-label="Tabs">
                    @foreach ($filteredItems as $item)
                    @if (isset($item['feature_required']))
                        @hasFeature($item['feature_required'])
                            <a href="{{ tenant_route($item['route']) }}"
                                @class([ 'flex items-center space-x-3 text-sm p-1 py-2 rounded-t-md border-b font-medium hover:bg-gray-50 dark:hover:bg-slate-800'
                                , 'text-primary-600 dark:bg-slate-800'=> request()->routeIs($item['route']),
                                'border-slate-200 text-slate-500 hover:text-slate-700 hover:border-slate-300
                                dark:border-slate-600 dark:text-slate-400 dark:hover:text-slate-300' =>
                                !request()->routeIs($item['route']),
                                ])>
                                <x-dynamic-component :component="$item['icon']" class="w-6 h-6 flex-shrink-0" />
                                <span>{{ t($item['label']) ?? $item['fallback_label'] ?? $item['label'] }}</span>
                            </a>
                        @endhasFeature
                    @else
                        <a href="{{ tenant_route($item['route']) }}"
                            @class([ 'flex items-center space-x-3 text-sm p-1 py-2 rounded-t-md border-b font-medium hover:bg-gray-50 dark:hover:bg-slate-800'
                            , 'text-primary-600 dark:bg-slate-800'=> request()->routeIs($item['route']),
                            'border-slate-200 text-slate-500 hover:text-slate-700 hover:border-slate-300
                            dark:border-slate-600 dark:text-slate-400 dark:hover:text-slate-300' =>
                            !request()->routeIs($item['route']),
                            ])>
                            <x-dynamic-component :component="$item['icon']" class="w-6 h-6 flex-shrink-0" />
                            <span>{{ t($item['label']) ?? $item['fallback_label'] ?? $item['label'] }}</span>
                        </a>
                    @endif
                    @endforeach
                </nav>
            </div>
        </div>
    </div>
</div>