<x-guest-layout>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <h1 class="text-3xl font-bold mb-8 text-center text-gray-800">{{ t('choose_your_subscription_plan') }}</h1>
        <p class="text-center text-lg text-gray-600 mb-8">{{ t('select_best_plan_getstarted') }}</p>

        <livewire:subscription.public-plan-selector />

        <div class="mt-8 text-center">
            <p class="text-gray-600 mb-4">{{ t('already_have_an_account') }}</p>
            <div class="space-x-4">
                <a href="{{ route('login') }}"
                    class="inline-flex items-center px-5 py-2 border border-transparent text-base font-medium rounded-md text-white bg-info-600 hover:bg-info-700">
                    {{ t('login') }}
                </a>
                <a href="{{ route('register') }}"
                    class="inline-flex items-center px-5 py-2 border border-gray-300 text-base font-medium rounded-md text-info-700 bg-white hover:bg-gray-50">
                    {{ t('register') }}
                </a>
            </div>
        </div>
    </div>
</x-guest-layout>