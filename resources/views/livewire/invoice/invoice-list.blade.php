<div>
    <x-slot:title>
        {{ t('invoices') }}
    </x-slot:title>
    <x-breadcrumb :items="[
        ['label' => t('dashboard'), 'route' => tenant_route('tenant.dashboard')],
        ['label' => t('invoices')],
    ]" />
    <x-card class="rounded-lg">
        <x-slot:content>
            <div class="lg:mt-0" wire:poll.30s="refreshTable">
                <livewire:tenant.tables.invoice-table />
            </div>
        </x-slot:content>
    </x-card>
</div>
