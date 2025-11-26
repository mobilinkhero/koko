<div >
    <x-slot:title>
        {{ t('Subscription') }}
    </x-slot:title>

       <x-breadcrumb :items="[
        ['label' => t('dashboard'), 'route' => route('admin.dashboard')],
        ['label' => t('subscriptions')],
    ]" />

    <x-card class="rounded-lg">
        <x-slot:content>
            <div class="lg:mt-0" wire:poll.30s="refreshTable">
                <livewire:admin.tables.subscription-table />
            </div>
        </x-slot:content>
    </x-card>

</div>
