<div x-data="couponFormHandler()" x-init="init()">
    <!-- Page Title -->
    <x-slot:title>
        {{ $coupon ? 'Edit Coupon: ' . $coupon->code : 'Create New Coupon' }}
    </x-slot:title>

    <!-- Breadcrumb -->
    <div class="relative rounded-lg lg:w-3/4">
        <x-breadcrumb :items="[
            ['label' => t('dashboard'), 'route' => route('admin.dashboard')],
            ['label' => 'Coupons', 'route' => route('admin.coupons.list')],
            ['label' => $coupon ? 'Edit Coupon' : 'Create New Coupon']
        ]" />
    </div>

    <!-- Form -->
    <form wire:submit.prevent="save" novalidate>
        <x-card class="relative rounded-lg lg:w-3/4">
            <x-slot:header>
                <h2 class="text-md font-medium text-slate-700 dark:text-slate-300">
                    {{ t('Coupon Information') }}
                </h2>
            </x-slot:header>

            <x-slot:content>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- Coupon Code -->
                    <div class="col-span-1">
                        <div class="flex items-center gap-1">
                            <span class="text-danger-500">*</span>
                            <x-label for="code" :value="t('Coupon Code')" />
                        </div>
                        <div class="flex">
                            <x-input id="code" type="text" wire:model.defer="code" placeholder="e.g. SAVE20"
                                class="flex-1 rounded-r-none" />
                            <x-button.secondary type="button" wire:click="generateCode" class="rounded-l-none">
                                {{ t('Generate') }}
                            </x-button.secondary>
                        </div>
                        <x-input-error for="code" class="mt-1" />
                    </div>

                    <!-- Coupon Name -->
                    <div class="col-span-1">
                        <div class="flex items-center gap-1">
                            <span class="text-danger-500">*</span>
                            <x-label for="name" :value="t('Name')" />
                        </div>
                        <x-input id="name" type="text" wire:model.defer="name" placeholder="e.g. Summer Sale" />
                        <x-input-error for="name" class="mt-1" />
                    </div>

                    <!-- Description -->
                    <div class="col-span-2">
                        <x-label for="description" :value="t('Description (Optional)')" />
                        <x-textarea id="description" wire:model.defer="description" rows="3"
                            placeholder="Describe the purpose of this coupon" />
                        <x-input-error for="description" class="mt-1" />
                    </div>

                    <!-- Coupon Type & Value -->
                    <div class="col-span-2">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Type -->
                            <div>
                                <div class="flex items-center gap-1">
                                    <span class="text-danger-500">*</span>
                                    <x-label :value="t('Type')" />
                                </div>
                                <div class="mt-1">
                                    <div class="flex flex-col space-y-3 sm:flex-row sm:space-y-0 sm:space-x-6">
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="radio" x-model="couponType" wire:model="type"
                                                value="percentage"
                                                class="h-4 w-4 text-primary-600 border-gray-300 focus:ring-primary-500">
                                            <span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                {{ t('Percentage') }}
                                            </span>
                                        </label>
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="radio" x-model="couponType" wire:model="type"
                                                value="fixed_amount"
                                                class="h-4 w-4 text-primary-600 border-gray-300 focus:ring-primary-500">
                                            <span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                {{ t('Fixed Amount') }}
                                            </span>
                                        </label>
                                    </div>
                                </div>
                                <x-input-error for="type" class="mt-1" />
                            </div>

                            <!-- Value -->
                            <div>
                                <div class="flex items-center gap-1">
                                    <span class="text-danger-500">*</span>
                                    <label for="value"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        <span
                                            x-text="couponType === 'percentage' ? '{{ t('Percentage') }}' : '{{ t('Amount') }}'"></span>
                                    </label>
                                </div>
                                <div class="relative mt-1">
                                    <x-input id="value" type="number" wire:model.defer="value"
                                        x-bind:class="couponType === 'percentage' ? 'pr-10' : 'pl-10'" step="0.01"
                                        min="0"
                                        x-bind:placeholder="couponType === 'percentage' ? 'Enter percentage (1-100)' : 'Enter amount'" />

                                    <!-- Percentage Symbol -->
                                    <div x-show="couponType === 'percentage'"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 text-sm font-medium">%</span>
                                    </div>

                                    <!-- Currency Symbol -->
                                    <div x-show="couponType === 'fixed_amount'"
                                        class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 text-sm font-medium" x-text="currencySymbol"></span>
                                    </div>
                                </div>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                                    x-show="couponType === 'percentage'">
                                    {{ t('Enter a percentage between 1 and 100') }}
                                </p>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                                    x-show="couponType === 'fixed_amount'">
                                    {{ t('Enter the fixed discount amount in') }} <span x-text="currencySymbol"></span>
                                </p>
                                <x-input-error for="value" class="mt-1" />
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="col-span-1">
                        <x-label :value="t('Status')" />
                        <div class="flex items-center mt-1">
                            <x-toggle id="active-toggle" wire:change="toggleActiveSwitch" :value="$is_active" />
                            <span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ $is_active ? t('Active') : t('Inactive') }}
                            </span>
                        </div>
                    </div>

                    <!-- Divider -->
                    <div class="col-span-2 my-4 border-t border-gray-200 dark:border-gray-700"></div>
                    <h3 class="col-span-2 text-lg font-medium text-gray-900 dark:text-gray-100">
                        {{ t('Restrictions & Limitations') }}
                    </h3>

                    <!-- Usage Limit -->
                    <div class="col-span-2 lg:col-span-1">
                        <x-label for="usage_limit" :value="t('Total Usage Limit (Optional)')" />
                        <x-input id="usage_limit" type="number" wire:model.defer="usage_limit"
                            placeholder="Leave blank for unlimited" min="1" />
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            {{ t('Maximum number of times this coupon can be used') }}
                        </p>
                        <x-input-error for="usage_limit" class="mt-1" />
                    </div>

                    <!-- Usage Limit Per Customer -->
                    <div class="col-span-2 lg:col-span-1">
                        <x-label for="usage_limit_per_customer" :value="t('Usage Limit Per Customer (Optional)')" />
                        <x-input id="usage_limit_per_customer" type="number" wire:model.defer="usage_limit_per_customer"
                            placeholder="Leave blank for unlimited" min="1" />
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            {{ t('Maximum number of times a single customer can use this coupon') }}
                        </p>
                        <x-input-error for="usage_limit_per_customer" class="mt-1" />
                    </div>

                    <!-- Minimum Amount -->
                    <div class="col-span-2 lg:col-span-1">
                        <x-label for="minimum_amount" :value="t('Minimum Amount (Optional)')" />
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm" x-text="currencySymbol"></span>
                            </div>
                            <x-input id="minimum_amount" type="number" wire:model.defer="minimum_amount" class="pl-8"
                                step="0.01" min="0" placeholder="Leave blank for no minimum" />
                        </div>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            {{ t('Minimum subtotal required for coupon to be valid') }}
                        </p>
                        <x-input-error for="minimum_amount" class="mt-1" />
                    </div>

                    <!-- Maximum Discount -->
                    <div class="col-span-2 lg:col-span-1">
                        <x-label for="maximum_discount" :value="t('Maximum Discount (Optional)')" />
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm" x-text="currencySymbol"></span>
                            </div>
                            <x-input id="maximum_discount" type="number" wire:model.defer="maximum_discount"
                                class="pl-8" step="0.01" min="0" placeholder="Leave blank for no maximum" />
                        </div>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            {{ t('Maximum discount amount when using percentage') }}
                        </p>
                        <x-input-error for="maximum_discount" class="mt-1" />
                    </div>

                    <!-- First Payment Only -->
                    <div class="col-span-2">
                        <div class="flex items-center">
                            <x-toggle id="first_payment_only" wire:change="togglePaymentSwitch"
                                :value="$first_payment_only" />
                            <span class="ml-2 text-sm text-slate-700 dark:text-slate-300">
                                {{ t('Apply to first payment only') }}
                            </span>
                        </div>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            {{ t('If checked, coupon will only apply to the first payment of a subscription') }}
                        </p>
                    </div>

                    <!-- Date Range -->
                    <div class="col-span-2">
                        <h3 class="font-medium text-slate-700 dark:text-slate-300 mb-2">
                            {{ t('Validity Period (Optional)') }}
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Start Date -->
                            <!-- Start Date -->
                            <div>
                                <x-label for="starts_at" :value="t('Start Date')" />
                                <div class="relative">
                                    <input type="text" id="starts_at" wire:model.defer="starts_at" class="block mt-1 w-full border-slate-300 rounded-md shadow-sm text-slate-900 sm:text-sm 
                   focus:ring-primary-500 focus:border-primary-500 disabled:opacity-50 
                   dark:border-slate-500 dark:bg-slate-800 dark:placeholder-slate-500 
                   dark:text-slate-200 pl-10 pr-3" />
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <x-heroicon-o-calendar class="h-5 w-5 text-gray-400" />
                                    </div>
                                </div>
                                <x-input-error for="starts_at" class="mt-1" />
                            </div>

                            <!-- Expiry Date -->
                            <div>
                                <x-label for="expires_at" :value="t('Expiry Date')" />
                                <div class="relative">
                                    <input type="text" id="expires_at" wire:model.defer="expires_at" class="block mt-1 w-full border-slate-300 rounded-md shadow-sm text-slate-900 sm:text-sm 
                   focus:ring-primary-500 focus:border-primary-500 disabled:opacity-50 
                   dark:border-slate-500 dark:bg-slate-800 dark:placeholder-slate-500 
                   dark:text-slate-200 pl-10 pr-3" />
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <x-heroicon-o-calendar class="h-5 w-5 text-gray-400" />
                                    </div>
                                </div>
                                <x-input-error for="expires_at" class="mt-1" />
                            </div>

                        </div>
                    </div>

                    <!-- Applicable Plans OR Billing Periods -->
                    <div class="col-span-2">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Applicable Plans -->
                            <div>
                                <x-label :value="t('Applicable Plans (Optional)')" />
                                <div class="mt-2">
                                    <div class="relative">
                                        <button type="button" @click="plansOpen = !plansOpen"
                                            :disabled="hasBillingPeriods()"
                                            :class="{ 'opacity-50 cursor-not-allowed': hasBillingPeriods() }"
                                            class="relative w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 
                                                   rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-pointer 
                                                   focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                                            <span class="block truncate text-gray-500 dark:text-gray-400"
                                                x-show="getSelectedPlansCount() === 0">
                                                <span x-show="!hasBillingPeriods()">Select applicable plans...</span>
                                                <span x-show="hasBillingPeriods()">Disabled (billing periods
                                                    selected)</span>
                                            </span>
                                            <span class="block truncate text-gray-900 dark:text-gray-100"
                                                x-show="getSelectedPlansCount() > 0">
                                                <span x-text="getSelectedPlansCount()"></span> plan<span
                                                    x-show="getSelectedPlansCount() !== 1">s</span> selected
                                            </span>
                                            <span
                                                class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                                <x-heroicon-o-chevron-up-down class="w-5 h-5 text-gray-400" />
                                            </span>
                                        </button>

                                        <!-- Dropdown Panel -->
                                        <div x-show="plansOpen && !hasBillingPeriods()" @click.away="plansOpen = false"
                                            x-transition:enter="transition ease-out duration-100"
                                            x-transition:enter-start="transform opacity-0 scale-95"
                                            x-transition:enter-end="transform opacity-100 scale-100"
                                            x-transition:leave="transition ease-in duration-75"
                                            x-transition:leave-start="transform opacity-100 scale-100"
                                            x-transition:leave-end="transform opacity-0 scale-95" class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 shadow-lg max-h-60 
                                                   rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto 
                                                   focus:outline-none sm:text-sm">
                                            <template x-for="plan in availablePlans" :key="plan.id">
                                                <div @click="togglePlan(plan.id)"
                                                    class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-primary-50 dark:hover:bg-gray-700"
                                                    :class="{ 'bg-primary-50 dark:bg-gray-700': isPlanSelected(plan.id) }">
                                                    <span class="block truncate"
                                                        :class="{ 'font-semibold text-primary-900 dark:text-primary-100': isPlanSelected(plan.id), 
                                                                  'font-normal text-gray-900 dark:text-gray-100': !isPlanSelected(plan.id) }"
                                                        x-text="plan.name"></span>
                                                    <span x-show="isPlanSelected(plan.id)"
                                                        class="absolute inset-y-0 right-0 flex items-center pr-4 text-primary-600 dark:text-primary-400">
                                                        <x-heroicon-o-check class="w-5 h-5" />
                                                    </span>
                                                </div>
                                            </template>
                                            <div x-show="availablePlans.length === 0"
                                                class="py-2 pl-3 pr-9 text-gray-500 dark:text-gray-400">
                                                No plans available
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Selected Plans Display -->
                                    <div class="flex flex-wrap gap-2 mt-2" x-show="getSelectedPlans().length > 0">
                                        <template x-for="plan in getSelectedPlans()" :key="plan.id">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                         bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-200">
                                                <span x-text="plan.name"></span>
                                                <button type="button" @click="removePlan(plan.id)"
                                                    class="ml-1.5 inline-flex items-center justify-center w-4 h-4 text-primary-400 hover:text-primary-600">
                                                    <x-heroicon-o-x-mark class="w-3 h-3" />
                                                </button>
                                            </span>
                                        </template>
                                    </div>
                                </div>
                                <x-input-error for="applicable_plans" class="mt-1" />
                            </div>

                            <!-- Applicable Billing Periods -->
                            <div>
                                <x-label :value="t('Applicable Billing Periods (Optional)')" />
                                <div class="mt-2">
                                    <div class="relative">
                                        <button type="button" @click="periodsOpen = !periodsOpen" :disabled="hasPlans()"
                                            :class="{ 'opacity-50 cursor-not-allowed': hasPlans() }"
                                            class="relative w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 
                                                   rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-pointer 
                                                   focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                                            <span class="block truncate text-gray-500 dark:text-gray-400"
                                                x-show="getSelectedPeriodsCount() === 0">
                                                <span x-show="!hasPlans()">Select applicable billing periods...</span>
                                                <span x-show="hasPlans()">Disabled (plans selected)</span>
                                            </span>
                                            <span class="block truncate text-gray-900 dark:text-gray-100"
                                                x-show="getSelectedPeriodsCount() > 0">
                                                <span x-text="getSelectedPeriodsCount()"></span> period<span
                                                    x-show="getSelectedPeriodsCount() !== 1">s</span> selected
                                            </span>
                                            <span
                                                class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                                <x-heroicon-o-chevron-up-down class="w-5 h-5 text-gray-400" />
                                            </span>
                                        </button>

                                        <!-- Dropdown Panel -->
                                        <div x-show="periodsOpen && !hasPlans()" @click.away="periodsOpen = false"
                                            x-transition:enter="transition ease-out duration-100"
                                            x-transition:enter-start="transform opacity-0 scale-95"
                                            x-transition:enter-end="transform opacity-100 scale-100"
                                            x-transition:leave="transition ease-in duration-75"
                                            x-transition:leave-start="transform opacity-100 scale-100"
                                            x-transition:leave-end="transform opacity-0 scale-95" class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 shadow-lg max-h-60 
                                                   rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto 
                                                   focus:outline-none sm:text-sm">
                                            <template x-for="period in availablePeriods" :key="period.id">
                                                <div @click="togglePeriod(period.id)"
                                                    class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-primary-50 dark:hover:bg-gray-700"
                                                    :class="{ 'bg-primary-50 dark:bg-gray-700': isPeriodSelected(period.id) }">
                                                    <span class="block truncate"
                                                        :class="{ 'font-semibold text-primary-900 dark:text-primary-100': isPeriodSelected(period.id), 
                                                                  'font-normal text-gray-900 dark:text-gray-100': !isPeriodSelected(period.id) }"
                                                        x-text="period.name"></span>
                                                    <span x-show="isPeriodSelected(period.id)"
                                                        class="absolute inset-y-0 right-0 flex items-center pr-4 text-primary-600 dark:text-primary-400">
                                                        <x-heroicon-o-check class="w-5 h-5" />
                                                    </span>
                                                </div>
                                            </template>
                                            <div x-show="availablePeriods.length === 0"
                                                class="py-2 pl-3 pr-9 text-gray-500 dark:text-gray-400">
                                                No billing periods available
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Selected Billing Periods Display -->
                                    <div class="flex flex-wrap gap-2 mt-2" x-show="getSelectedPeriods().length > 0">
                                        <template x-for="period in getSelectedPeriods()" :key="period.id">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                         bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-200">
                                                <span x-text="period.name"></span>
                                                <button type="button" @click="removePeriod(period.id)"
                                                    class="ml-1.5 inline-flex items-center justify-center w-4 h-4 text-primary-400 hover:text-primary-600">
                                                    <x-heroicon-o-x-mark class="w-3 h-3" />
                                                </button>
                                            </span>
                                        </template>
                                    </div>
                                </div>
                                <x-input-error for="applicable_billing_periods" class="mt-1" />
                            </div>
                        </div>
                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                            {{ t('Select either plans OR billing periods (not both). Leave empty to apply to all.') }}
                        </p>
                    </div>

                </div>
            </x-slot:content>

            <x-slot:footer>
                <div class="flex justify-end space-x-3">
                    <x-button.cancel-button href="{{ route('admin.coupons.list') }}">
                        {{ t('cancel') }}
                    </x-button.cancel-button>
                    <x-button.loading-button type="submit" target="save">
                        {{ $coupon ? t('update') : t('create') }}
                    </x-button.loading-button>
                </div>
            </x-slot:footer>
        </x-card>
    </form>
</div>


<script>
    // Main Alpine.js Form Handler
    function couponFormHandler() {
        return {
            // State
            couponType: @entangle('type'),
            currencySymbol: '{{ $this->getCurrencySymbol() }}',
            plansOpen: false,
            periodsOpen: false,
            availablePlans: @json($plans->map(fn($plan) => ['id' => $plan->id, 'name' => $plan->name])->values()),
            availablePeriods: @json(collect($availableBillingPeriods)->map(fn($period) => ['id' => $period, 'name' => ucfirst($period)])->values()),

            // Initialize
            init() {
                // Component initialized
            },

            // Plans Methods
            getSelectedPlans() {
                const selected = this.$wire.applicable_plans || [];
                return this.availablePlans.filter(plan => selected.includes(plan.id));
            },

            getSelectedPlansCount() {
                return (this.$wire.applicable_plans || []).length;
            },

            isPlanSelected(planId) {
                return (this.$wire.applicable_plans || []).includes(planId);
            },

            hasPlans() {
                return this.getSelectedPlansCount() > 0;
            },

            togglePlan(planId) {
                const currentPlans = [...(this.$wire.applicable_plans || [])];
                const index = currentPlans.indexOf(planId);

                if (index > -1) {
                    currentPlans.splice(index, 1);
                } else {
                    currentPlans.push(planId);
                    // Clear billing periods (either/or logic)
                    this.$wire.set('applicable_billing_periods', []);
                }

                this.$wire.set('applicable_plans', currentPlans);
                this.plansOpen = false;
            },

            removePlan(planId) {
                const currentPlans = (this.$wire.applicable_plans || []).filter(id => id !== planId);
                this.$wire.set('applicable_plans', currentPlans);
            },

            // Billing Periods Methods
            getSelectedPeriods() {
                const selected = this.$wire.applicable_billing_periods || [];
                return this.availablePeriods.filter(period => selected.includes(period.id));
            },

            getSelectedPeriodsCount() {
                return (this.$wire.applicable_billing_periods || []).length;
            },

            isPeriodSelected(periodId) {
                return (this.$wire.applicable_billing_periods || []).includes(periodId);
            },

            hasBillingPeriods() {
                return this.getSelectedPeriodsCount() > 0;
            },

            togglePeriod(periodId) {
                const currentPeriods = [...(this.$wire.applicable_billing_periods || [])];
                const index = currentPeriods.indexOf(periodId);

                if (index > -1) {
                    currentPeriods.splice(index, 1);
                } else {
                    currentPeriods.push(periodId);
                    // Clear plans (either/or logic)
                    this.$wire.set('applicable_plans', []);
                }

                this.$wire.set('applicable_billing_periods', currentPeriods);
                this.periodsOpen = false;
            },

            removePeriod(periodId) {
                const currentPeriods = (this.$wire.applicable_billing_periods || []).filter(id => id !== periodId);
                this.$wire.set('applicable_billing_periods', currentPeriods);
            }
        }
    }
</script>
<script>
    window.addEventListener("load", function() {
        // Example date/time formats
        const date_format = "Y-m-d";
        const is24Hour = true;

        window.flatPickrStartDate = flatpickr("#starts_at", {
            dateFormat: `${date_format} `,
            enableTime: true,
            allowInput: true,
            time_24hr: is24Hour,
            minDate: "today",
            disableMobile: true,
        });

        window.flatPickrEndDate = flatpickr("#expires_at", {
            dateFormat: `${date_format}`,
            enableTime: true,
            allowInput: true,
            time_24hr: is24Hour,
            minDate: "today",
            disableMobile: true,
        });
    });
</script>