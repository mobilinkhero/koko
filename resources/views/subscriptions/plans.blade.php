<x-app-layout>
<x-card class="p-6">
    <x-slot:content>
        <h1 class="text-2xl font-bold mb-6">{{ t('subscription_plans') }}</h1>

        <livewire:subscription.plan-selector />
    </x-slot:content>
</x-card>
</x-app-layout>

