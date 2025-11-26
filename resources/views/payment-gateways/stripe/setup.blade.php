<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ t('stripe_setup') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if (session('status'))
                    <div class="mb-4 font-medium text-sm text-success-600">
                        {{ session('status') }}
                    </div>
                    @endif

                    <form method="POST" action="{{ route('stripe.setup') }}">
                        @csrf

                        <!-- Stripe Publishable Key -->
                        <div>
                            <x-label for="stripe_publishable_key" :value="t('stripe_publishable_key')" />
                            <x-input id="stripe_publishable_key" class="block mt-1 w-full" type="text"
                                name="stripe_publishable_key"
                                value="{{ old('stripe_publishable_key', $settings->stripe_publishable_key) }}" required
                                autofocus />
                            @error('stripe_publishable_key')
                            <span class="text-danger-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Stripe Secret Key -->
                        <div class="mt-4">
                            <x-label for="stripe_secret_key" :value="t('stripe_secret_key')" />
                            <x-input id="stripe_secret_key" class="block mt-1 w-full" type="text"
                                name="stripe_secret_key"
                                value="{{ old('stripe_secret_key', $settings->stripe_secret_key) }}" required />
                            @error('stripe_secret_key')
                            <span class="text-danger-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <div class="flex items-center justify-end mt-4">
                            <x-button class="ml-4">
                                {{ t('save_settings') }}
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">{{ t('test_mode') }}</h3>
                <p class="mt-1 text-sm text-gray-600">{{ t('use_test_cards') }}</p>

                <ul class="mt-4 list-disc pl-5">
                    <li>{{ t('visa') }} 4242 4242 4242 4242</li>
                    <li>{{ t('mastercard') }} 5555 5555 5555 4444</li>
                    <li>{{ t('american_express') }} 3782 8224 6310 005</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">{{ t('live_mode') }}</h3>
                <p class="mt-1 text-sm text-gray-600">{{ t('use_card_numbers') }}</p>

                <ul class="mt-4 list-disc pl-5">
                    <li>{{ t('visa') }} 4000 0000 0000 0002</li>
                    <li>{{ t('mastercard') }} 5200 8282 8282 8210</li>
                    <li>{{ t('american_express') }} 3714 4963 5398 431</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">{{ t('webhook_url') }}</h3>
                <p class="mt-1 text-sm text-gray-600">{{ t('set_up_webhook_url_stripe') }}
                </p>

                <p class="mt-4 text-sm text-gray-800">{{ url('/stripe/webhook') }}</p>
            </div>
        </div>
    </div>
</x-app-layout>