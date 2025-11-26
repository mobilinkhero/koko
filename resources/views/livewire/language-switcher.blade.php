<x-dropdown>
    <x-slot:trigger>
        <x-button.primary-round class="mx-2">
            <x-heroicon-s-language class="w-4 h-4" />
        </x-button.primary-round>
    </x-slot:trigger>
    <x-slot:content>
        @foreach (app('App\Services\LanguageService')->getAvailableLanguages() as $language)
        @php
        $additionalClass =
        $language->code == $currentLocale
        ? 'bg-primary-50 dark:border-primary-600 text-primary-700 dark:bg-slate-900'
        : 'text-gray-600 hover:bg-primary-50 hover:text-primary-800 dark:text-slate-300 dark:hover:bg-slate-700
        dark:hover:text-white';
        @endphp
        <div class="flex items-center justify-between px-4 py-2 {{ $additionalClass }}">
            <button wire:click="setLocale('{{ $language->code }}')"
                class="flex-1 text-left flex items-center justify-between">
                <span>{{ $language->name }}</span>
                @if($language->code === $currentLocale)
                <span
                    class="ml-2 px-2 py-1 text-xs bg-success-100 text-success-700 rounded dark:bg-success-800 dark:text-success-300">
                    ✓ {{ t('active') }}
                </span>
                @endif
            </button>
        </div>
        @endforeach
    </x-slot:content>
</x-dropdown>