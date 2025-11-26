<x-app-layout>
    <x-card class="p-6">
        <x-slot:content>
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h1 class="text-2xl font-bold">{{ $plan->name }}</h1>
                    <p class="text-gray-600 mt-1">{{ $plan->description }}</p>
                </div>
                <div class="text-right">
                    <div class="text-3xl font-bold">{{ $plan->formatted_price }}</div>
                </div>
            </div>

            <!-- Plan Features -->
            @if(!empty($plan->features))
            <div class="mt-6">
                <h2 class="text-lg font-semibold mb-3">{{ t('features') }}</h2>
                <ul class="space-y-2">
                    @foreach($plan->features as $feature)
                    <li class="flex items-start">
                        <svg class="h-5 w-5 text-success-500 mr-2 flex-shrink-0 mt-0.5" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                        <span>{{ $feature }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- Trial Info -->
            @if($plan->trial_period)
            <div class="mt-6 bg-info-50 p-4 rounded-md border border-info-100">
                <div class="flex">
                    <svg class="h-6 w-6 text-info-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <h3 class="font-medium text-info-800">{{ $plan->trial_period }}{{ t('day_free_trial') }}</h3>
                        <p class="text-info-700 text-sm mt-1">{{ t('start_with_free_trial') }}</p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Subscription Button -->
            <div class="mt-8 flex justify-center">
                @auth
                <x-button.primary href="{{ route('checkout', ['plan' => $plan->uid]) }}" class="px-6 py-3 text-base">
                    @if($plan->trial_period)
                    {{ t('start_free_trial') }}
                    @else
                    {{ t('subscribe_now') }}
                    @endif
                </x-button.primary>
                @else
                <div class="text-center">
                    <p class="mb-4 text-gray-600">{{ t('login_to_subscribe_plan') }}</p>
                    <x-button.secondary href="{{ route('login') }}" class="text-sm">
                        {{ t('login') }}
                    </x-button.secondary>
                    <x-button.primary href="{{ route('register') }}" class="ml-3 text-sm">
                        {{ t('register') }}
                    </x-button.primary>
                </div>
                @endauth
            </div>

            <!-- Back Link -->
            <div class="mt-8 text-center">
                <a href="{{ tenant_route('tenant.subscription') }}" class="text-info-600 hover:underline">
                    {{ t('back_to_plans') }}
                </a>
            </div>
        </x-slot:content>
    </x-card>
</x-app-layout>