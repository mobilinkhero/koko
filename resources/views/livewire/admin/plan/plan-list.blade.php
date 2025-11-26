<div class="space-y-6 md:px-0">
    <x-slot:title>
        {{ t('Plans') }}
    </x-slot:title>
    <x-breadcrumb :items="[['label' => t('dashboard'), 'route' => route('admin.dashboard')], ['label' => t('subscription_plans')]]" />

    <!-- Header Section with Filters -->
    <div class="flex flex-col justify-between space-y-4 md:space-y-0 md:flex-row md:items-center">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ t('subscription_plans') }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ t('manage_your_subscription_plans_and_pricing') }}
            </p>
        </div>

        <div class="flex flex-col gap-4 xl:flex-row space-y-3 justify-between sm:space-y-0 ">
            <div class="flex justify-between items-center gap-4">
                <div class="space-y-1">
                    <div class="flex justify-between items-center gap-3">
                        <div>
                            <x-label for="showActiveOnly" class="font-medium">
                                {{ t('show_active_only') }}
                            </x-label>
                        </div>
                        <x-toggle id="showActiveOnly" wire:model.live="showActiveOnly" :value="$showActiveOnly" />
                    </div>
                </div>
                @if (checkPermission('admin.plans.create'))
                    <x-button.primary wire:navigate href="{{ route('admin.plans.create') }}">
                        <x-heroicon-m-plus class="w-4 h-4 mr-2" />
                        {{ t('create_plan') }}
                    </x-button.primary>
                @endif
            </div>
        </div>
    </div>

    <!-- Plans Cards View -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @forelse($plans as $plan)
            <div
                class="flex flex-col relative overflow-hidden bg-white border rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700 hover:shadow-md transition-shadow h-full">
                <!-- Featured/Default Badge -->
                @if ($plan->featured)
                    <div class="absolute top-0 right-0">
                        @if ($plan->featured)
                            <div class="px-3 py-1 text-xs font-medium text-white bg-primary-600 rounded-bl-lg">
                                {{ t('featured') }}
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Plan Header -->
                <div class="p-4 border-b dark:border-gray-700" style="background-color: {{ $plan->color }}15;">
                    <div class="flex items-center">
                        <div class="flex items-center justify-center flex-shrink-0 w-10 h-10 rounded-full"
                            style="background-color: {{ $plan->color }};">
                            <span class="text-sm font-bold text-white">{{ $plan->name[0] }}</span>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ $plan->name }}</h3>
                            <div class="flex mt-1">
                                @if ($plan->is_free)
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-200">
                                        {{ t('free') }}
                                    </span>
                                @endif
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $plan->is_active ? 'bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-200' : 'bg-danger-100 text-danger-800 dark:bg-danger-900 dark:text-danger-200' }} ml-2">
                                    {{ $plan->is_active ? t('active') : t('inactive') }}
                                </span>

                            </div>
                            @php
                                $link = route('register', ['plan_id' => $plan->id]);
                            @endphp
                            <div class="mt-3 flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400 truncate">
                                <span class="truncate">{{ $link }} </span>
                                <button onclick="copyToClipboard('{{ $link }}')"
                                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300" title="Copy URL">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Plan Details - This will grow/shrink based on content -->
                <div class="p-4 flex-grow">
                    <div class="flex justify-between items-center mb-3">
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">
                            @if ($plan->is_free)
                                {{ t('free') }}
                            @else
                                {{ get_base_currency()->format($plan->price) }}
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    /{{ $plan->billing_period == 'monthly' ? t('monthly') : t('yearly') }}
                                </span>
                            @endif
                        </div>
                        @if ($plan->trial_days > 0 && $plan->is_free == 1)
                            <div
                                class="px-2 py-1 text-xs font-medium text-primary-800 bg-primary-100 rounded-full dark:bg-primary-900 dark:text-primary-200 flex items-center justify-center">
                                {{ 'trial ' . $plan->trial_days . ' days' }}
                            </div>
                        @endif
                    </div>

                    <p class="mb-4 text-sm text-gray-500 dark:text-gray-400 line-clamp-2">
                        {{ $plan->description }}
                    </p>

                    <!-- Features List -->
                    <div>
                        <h4 class="text-xs font-semibold text-gray-500 uppercase dark:text-gray-400">
                            {{ t('features') }}</h4>
                        <ul class="mt-2 space-y-2">
                            @foreach ($plan->planFeatures as $feature)
                                @if (!empty($feature->value))
                                    <li
                                        class="flex items-start group/feature hover:bg-gray-50 dark:hover:bg-gray-700 p-1 rounded-md transition-colors">
                                        <x-heroicon-o-check-circle
                                            class="w-4 h-4 mt-0.5 text-success-500 dark:text-success-400 flex-shrink-0" />
                                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-300 flex-grow">
                                            {{ t($feature->slug) }}:
                                            <span class="font-medium">
                                                {{ $feature->value == '-1' ? 'Unlimited' : number_format($feature->value) }}</span>
                                        </span>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                </div>

                <!-- Actions Footer - Always at bottom -->
                <div class="mt-auto flex border-t divide-x dark:border-gray-700 dark:divide-gray-700">
                    @if (checkPermission('admin.plans.edit'))
                        <button wire:click="editPlan({{ $plan->id }})"
                            class="flex items-center justify-center flex-1 px-4 py-2 text-sm font-medium text-primary-600 hover:bg-primary-50 dark:text-primary-400 dark:hover:bg-gray-700 transition-colors">
                            <x-heroicon-o-pencil-square class="w-4 h-4 mr-1" />
                            {{ t('edit') }}
                        </button>
                    @endif

                    @if (checkPermission('admin.plans.edit'))
                        <button
                            wire:click="updateStatus({{ $plan->id }}, {{ $plan->is_active ? 'false' : 'true' }})"
                            class="flex items-center justify-center flex-1 px-4 py-2 text-sm font-medium {{ $plan->is_active ? 'text-warning-600 hover:bg-warning-50 dark:text-warning-400 dark:hover:bg-gray-700' : 'text-success-600 hover:bg-success-50 dark:text-success-400 dark:hover:bg-gray-700' }} transition-colors">
                            @if ($plan->is_active)
                                <x-heroicon-o-pause-circle class="w-4 h-4 mr-1" />
                                {{ t('deactivate') }}
                            @else
                                <x-heroicon-o-play-circle class="w-4 h-4 mr-1" />
                                {{ t('activate') }}
                            @endif
                        </button>
                    @endif

                    @if (checkPermission('admin.plans.delete'))
                        @if (!$plan->is_free)
                            <button wire:click="confirmDelete({{ $plan->id }})"
                                class="flex items-center justify-center flex-1 px-4 py-2 text-sm font-medium text-danger-600 hover:bg-danger-50 dark:text-danger-400 dark:hover:bg-gray-700 transition-colors">
                                <x-heroicon-o-trash class="w-4 h-4 mr-1" />
                                {{ t('delete') }}
                            </button>
                        @endif
                    @endif

                </div>
            </div>
        @empty
            <div class="col-span-full p-6 text-center bg-white rounded-lg shadow dark:bg-gray-800">
                <x-heroicon-o-exclamation-circle class="w-12 h-12 mx-auto text-gray-400" />
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">{{ t('no_plans_found') }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ t('get_started_by_creating_a_new_plan') }}
                </p>
                <div class="mt-6">
                    @if (checkPermission('admin.plans.create'))
                        <x-button.primary wire:navigate href="{{ route('admin.plans.create') }}">
                            <x-heroicon-m-plus class="-ml-1 mr-2 w-5 h-5" />
                            {{ t('create_plan') }}
                        </x-button.primary>
                    @endif
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if ($plans->hasPages())
        <div class="mt-4">
            {{ $plans->links() }}
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    <x-modal.confirm-box :maxWidth="'lg'" :id="'delete-plan-modal'" title="{{ t('delete_plan_title') }}"
        wire:model.defer="confirmingDeletion" description="{{ t('delete_message') }} ">
        <div
            class="border-neutral-200 border-neutral-500/30 flex justify-end items-center sm:block space-x-3 bg-gray-100 dark:bg-gray-700 ">
            <x-button.cancel-button wire:click="$set('confirmingDeletion', false)">
                {{ t('cancel') }}
            </x-button.cancel-button>
            <x-button.delete-button wire:click="delete" wire:loading.attr="disabled" class="mt-3 sm:mt-0">
                {{ t('delete') }}
            </x-button.delete-button>
        </div>
    </x-modal.confirm-box>
</div>
