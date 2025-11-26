<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ t('webhook_settings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-10">
                <ul
                    class="flex flex-wrap text-sm font-medium text-center text-gray-500 border-b border-gray-200 dark:border-gray-700 dark:text-gray-400">
                    {{-- <li class="mr-2">
                        <a href="{{ route('admin.settings.payment') }}"
                            class="inline-block p-4 rounded-t-lg hover:text-gray-600 hover:bg-gray-50 dark:hover:bg-gray-800 dark:hover:text-gray-300">
                            {{ __('Payment') }}
                        </a>
                    </li> --}}
                    <li class="mr-2">
                        <a href="{{ route('admin.settings.webhooks') }}" aria-current="page"
                            class="inline-block p-4 text-info-600 bg-gray-100 rounded-t-lg active dark:bg-gray-800 dark:text-info-500">
                            {{ t('webhooks') }}
                        </a>
                    </li>
                </ul>
            </div>

            @if (session('success'))
            <div class="mb-6 bg-success-100 dark:bg-success-900 p-4 rounded-md">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-success-400 dark:text-success-300" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-success-800 dark:text-success-200">
                            {{ session('success') }}
                        </p>
                    </div>
                </div>
            </div>
            @endif

            @if (session('error'))
            <div class="mb-6 bg-danger-100 dark:bg-danger-900 p-4 rounded-md">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-danger-400 dark:text-danger-300" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-danger-800 dark:text-danger-200">
                            {{ session('error') }}
                        </p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Advanced Webhook Management -->
            <div class="mt-6">
                <livewire:admin.payment.manage-stripe-webhooks />
            </div>
        </div>
    </div>
</x-app-layout>