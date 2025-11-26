<x-app-layout>
    <x-slot:title>
        {{ t('transactions') }}
    </x-slot:title>
    <x-breadcrumb :items="[['label' => t('dashboard'), 'route' => route('admin.dashboard')], ['label' => t('transactions')]]" />
    <x-card class="lg:mx-0 rounded-lg mt-4" x-init="setInterval(() => {
        Livewire.dispatch('refreshTable');
    
    }, 30000);">
        <x-slot:content>
            <div class="mt-8 lg:mt-0">
                <livewire:admin.tables.transaction-table />
            </div>
        </x-slot:content>
    </x-card>


</x-app-layout>
