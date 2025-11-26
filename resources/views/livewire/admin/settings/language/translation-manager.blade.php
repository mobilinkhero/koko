<div class="px-4 md:px-0">
    <x-slot:title>{{ t('translation_management') }}</x-slot:title>

    <x-breadcrumb :items="[
        ['label' => t('dashboard'), 'route' => route('admin.dashboard')],
        ['label' => t('language'), 'route' => route('admin.languages')],
        ['label' => t('translate_language')]
]" />

    <x-card class="lg:mx-0 rounded-lg">
        <x-slot:content>
            <x-slot:header>
                <x-settings-heading class="font-display">
                    {{ t('translate_language') }}
                </x-settings-heading>
                <x-settings-description>
                    {{ t('translate_language') }} {{ t('english_to') }}
                    <span class="text-primary-400 font-medium">{{ $languageName[0] }} </span>
                </x-settings-description>
            </x-slot:header>
            <div class="mx-auto md:px-0">
                <livewire:admin.tables.language-line-table :languageCode="$code"
                    wire:key="translation-manager-{{ $code }}" />
            </div>
        </x-slot:content>
    </x-card>
</div>
