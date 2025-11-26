<div class="space-y-6 md:px-0">
    <x-slot:title>
        {{ t('create_plan') }}
    </x-slot:title>

      <x-breadcrumb :items="[
        ['label' => t('dashboard'), 'route' => route('admin.dashboard')],
        ['label' => t('subscription_plans'), 'route' => route('admin.plans.list')],
        ['label' => $isUpdate ? t('edit_plan') : t('create_plan')]
]" />

    <form wire:submit.prevent="save">
        <div class="flex flex-col lg:flex-row gap-6 items-start" x-data="{ isFree: @entangle('is_free') }">
            <!-- Left Column (Personal Information) -->
            <div class="w-full lg:w-6/12">
                <x-card class="rounded-lg shadow-sm mb-10">
                    <x-slot:header>
                        <div class="flex items-center gap-4">
                            <x-heroicon-o-document-text class="w-8 h-8 text-primary-600" />
                            <h1 class="text-xl font-semibold text-slate-700 dark:text-slate-300">
                                {{ t('plan_details') }}
                            </h1>
                        </div>
                    </x-slot:header>
                    <x-slot:content class="space-y-4">
                        <!-- Name -->
                        <div>
                            <div class="flex item-centar justify-start gap-1">
                                <span class="text-danger-500">*</span>
                                <x-label for="name" :value="t('name')" />
                            </div>
                            <x-input id="name" type="text" class="block w-full mt-1" wire:model="name" autocomplete="off"/>
                            <x-input-error for="name" class="mt-2" />
                        </div>
                        <!-- Slug -->
                        <div x-data="{ slugify() { $wire.set('slug', this.slugifyValue($wire.get('name'))); }, slugifyValue(text) { return text.toString().toLowerCase().replace(/\s+/g, '-').replace(/[^\w\-]+/g, '').replace(/\-\-+/g, '-').replace(/^-+/, '').replace(/-+$/, ''); } }">
                            <div class="flex items-center justify-between">
                                <div class="flex item-centar justify-start gap-1">
                                <span class="text-danger-500">*</span>
                                <x-label for="slug" :value="t('slug')" />
                                </div>
                                @if (!$isUpdate)
                                <button type="button" x-on:click="slugify()"
                                    class="text-xs text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300">
                                    {{ t('generate_from_name') }}
                                </button>
                                @endif
                            </div>
                            <x-input id="slug" type="text"
                                class="block w-full mt-1"
                                wire:model="slug"
                                autocomplete="off"
                                placeholder="{{ t('auto_generated_if_empty') }}"
                                :disabled="$isUpdate"
                                :title="$isUpdate ? t('slug_cannot_be_edited_after_creation') : ''"
                                />
                            <x-input-error for="slug" class="mt-2" />
                        </div>
                        <!-- Description -->
                        <div>
                            <div class="flex item-centar justify-start gap-1">
                                <span class="text-danger-500">*</span>
                                <x-label for="description" :value="t('description')" />
                            </div>
                            <x-textarea id="description" class="block w-full mt-1" rows="3" autocomplete="off"
                                wire:model="description"></x-textarea>
                            <x-input-error for="description" class="mt-2" />
                        </div>
                        <!-- Color Picker -->
                        <div x-data="{ color: @entangle('color') }">
                            <div class="flex items-center justify-start gap-1">
                                <x-label for="color"
                                    class="dark:text-gray-300 block text-sm font-medium text-gray-700">
                                    {{ t('color') }}
                                </x-label>
                            </div>
                            <div class="group relative">
                                <div class="flex items-center gap-3">
                                    <x-input x-model="color" type="text" id="status-color-text"
                                        class="w-full pl-11 pr-4 py-2.5"
                                        placeholder="{{ t('status_color_placeholder') }}" />
                                    <div class="absolute left-3 top-1/2 -translate-y-1/2">
                                        <label for="status-color-picker" class="cursor-pointer">
                                            <div class="w-6 h-6 rounded-md border-2 border-slate-200 shadow-sm overflow-hidden transition-transform hover:scale-105 dark:border-slate-600"
                                                :style="`background-color: ${color}`">
                                                <x-input id="status-color-picker" type="color" x-model="color" autocomplete="off"
                                                    class="opacity-0 absolute inset-0 w-full h-full cursor-pointer" />
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <x-input-error for="color" class="mt-2" />
                        </div>

                    </x-slot:content>
                </x-card>
            </div>
            <div class="w-full lg:w-6/12 ">
                <x-card class="rounded-lg shadow-sm mb-6">
                    <x-slot:header>
                        <div class="flex items-center gap-4">
                            <x-heroicon-o-currency-dollar class="w-8 h-8 text-primary-600" />
                            <h1 class="text-xl font-semibold text-slate-700 dark:text-slate-300">
                                {{ t('pricing_and_limits') }}
                            </h1>
                        </div>
                    </x-slot:header>
                    <x-slot:content class="space-y-4">
                        <!--monthly Price -->
                        <div class="flex justify-center items-center gap-4">
                            <div class="w-full" x-show="isFree != 1" x-cloak>
                                <div class="flex item-center justify-start gap-1">
                                    <span class="text-danger-500">*</span>
                                    <x-label for="price" :value="t('price')" />
                                </div>
                                <div class="relative mt-1 rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <span
                                            class="text-gray-500 dark:text-gray-400 sm:text-sm">{{ $baseCurrencySymbol }}</span>
                                    </div>
                                    <x-input id="price" type="number" step="0.01" min="0" autocomplete="off"
                                        class="block w-full pl-7" wire:model="price" />
                                </div>
                                <x-input-error for="price" class="mt-2" />
                            </div>
                            <!-- Trial Days -->
                            <div class="w-full" x-show="isFree != 1" x-cloak>
                                <div class="w-full md:w-1/2" x-data="{ billingPeriod: @entangle('billing_period') }" x-cloak>
                                    <div class="gap-4">
                                        <!-- Label first -->
                                        <x-label for="billing_period"
                                            class="font-medium flex flex-col sm:flex-row sm:items-center gap-x-1">
                                            {{ t('billing_period') }}:
                                            <span class="text-primary-500"
                                                x-text="billingPeriod === 'monthly' ? '{{ t('monthly') }}' : '{{ t('yearly') }}'"></span>
                                        </x-label>

                                        <!-- Switch after label -->
                                        <x-toggle
                                            id="billing_period"
                                            name="billing_period"
                                            :value="$billing_period === 'yearly'"
                                            x-on:toggle-changed="billingPeriod = billingPeriod === 'monthly' ? 'yearly' : 'monthly'"
                                        />

                                    </div>
                                    <x-input-error for="billing_period" class="mt-2" />
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-start gap-4">
                            <div class="w-full md:w-1/2" x-data="{ freePlan: @entangle('is_free') }">
                                <div class="w-full" x-show="freePlan">
                                    <div class="flex justify-start items-center gap-2">
                                        <x-label for="trial_days" :value="t('trial_days')" />
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            ({{ t('trial_days_description') }})
                                        </p>
                                    </div>
                                    <x-input id="trial_days" type="number" min="0" class="block w-full mt-1" autocomplete="off"
                                        wire:model="trial_days" />
                                    <x-input-error for="trial_days" class="mt-2" />
                                </div>
                            </div>
                        </div>
                        <!-- Plan Flags -->
                        <div class="space-y-4 rounded-md bg-white dark:bg-transparent">
                            <div class="border-t border-gray-200 dark:border-gray-700 my-6"></div>
                            <div class="grid gap-6 grid-cols-1 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-2">
                                <div>
                                    <div class="flex justify-start items-center gap-3">
                                        <x-toggle id="featured" name="featured" :value="!!$featured" wire:model="featured" />
                                        <x-label for="featured" class="font-medium">
                                            {{ t('featured_plan') }}
                                        </x-label>
                                    </div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                        {{ t('featured_plan_description') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </x-slot:content>
                </x-card>
            </div>
        </div>
        <!-- Features Section -->
        @if (isset($availableFeatures) && count($availableFeatures) > 0)
            <x-card class="rounded-lg shadow-sm mb-20">
                <x-slot:header>
                    <div class="flex items-center gap-4">
                        <x-heroicon-o-check-circle class="w-8 h-8 text-primary-600" />
                        <h1 class="text-xl font-semibold text-slate-700 dark:text-slate-300">
                            {{ t('plan_features') }}
                        </h1>
                    </div>
                </x-slot:header>
                <x-slot:content class="space-y-4">
                    <div class="rounded-md">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-3 xl:grid-cols-5">
                            @foreach ($availableFeatures as $feature)
                                <div class="group relative overflow-hidden p-5 bg-white dark:bg-gray-800 rounded-lg border border-gray-300 dark:border-gray-700 transition-all duration-300 hover:shadow-lg hover:border-primary-200 dark:hover:border-primary-800"
                                    x-cloak>

                                    <div class="flex items-start">
                                        <div class="flex-1">
                                            <!-- Feature name with tooltip for long names -->
                                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white group-hover:text-primary-700 dark:group-hover:text-primary-400 transition-colors"
                                                title="{{ t($feature->slug) }}">
                                                @if ($feature->default == 1)
                                                    <span class="text-danger-500">*</span>
                                                @endif
                                                {{ t($feature->slug) }}
                                            </h3>

                                            <!-- Feature description with better styling -->
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 line-clamp-2">
                                                {{ $feature->description }}
                                            </p>

                                            <!-- Feature controls -->
                                            <div class="mt-3 relative">
                                                <x-input type="text"
                                                    class="block w-full pr-12 rounded-md border-gray-200 dark:border-gray-700 focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50" autocomplete="off"
                                                    wire:model="features.{{ $feature->id }}"
                                                    placeholder="{{ t('enter_limit') }}" />
                                                <div
                                                    class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-xs text-gray-400">
                                                    {{ t('units') }}
                                                </div>
                                            </div>
                                            <x-input-error for="features.{{ $feature->id }}" class="mt-2" />
                                        </div>
                                    </div>

                                </div>
                            @endforeach
                        </div>
                    </div>
                    <x-input-error for="features" class="mt-2" />
                </x-slot:content>
            </x-card>
        @endif
        <!-- Footer Actions Bar -->
        <div
            class="fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600 z-10">
            <div class="flex justify-end px-6 py-3">
                <x-button.secondary class="mx-2" onclick="window.history.back()">
                    {{ t('cancel') }}
                </x-button.secondary>
                <x-button.loading-button type="submit">
                    {{ $isUpdate ? t('update_plan') : t('create_plan') }}
                </x-button.loading-button>
            </div>
        </div>
    </form>
</div>
