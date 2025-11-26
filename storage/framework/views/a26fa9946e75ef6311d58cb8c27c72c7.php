<link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">
<div x-data="{ billingPeriod: '<?php echo e($defaultBillingPeriod); ?>' }">
    <div style="background-image: url('<?php echo e(asset('img/landingpage-image/plan-section/plansection-bg.png')); ?>'); background-size: cover; background-position: center; background-repeat: no-repeat;"
        class="max-w-screen-xl px-4 py-8 mx-auto lg:py-24 lg:px-6 text-center">

        <h2 class="mb-4 text-3xl font-extrabold tracking-tight text-gray-800 font-display">
            <?php echo e($pricingSettings['theme.pricing_section_title'] ?: 'Default Title'); ?>

        </h2>
        <p class="mb-5 font-light text-gray-600 sm:text-lg dark:text-gray-400">
            <?php echo e($pricingSettings['theme.pricing_section_subtitle'] ?: 'Default Title'); ?>

        </p>

        <!--[if BLOCK]><![endif]--><?php if($showBillingToggle): ?>
        <!-- Billing Period Toggle Switch -->
        <div class="flex justify-center mb-8">
            <div class="inline-flex rounded-full p-1 border border-gray-300">
                <button type="button" x-on:click="billingPeriod = 'monthly'"
                    :class="billingPeriod === 'monthly'
                        ?
                        'bg-primary-600 text-white' :
                        'text-gray-700'"
                    class="px-4 py-1.5 text-sm font-semibold rounded-full transition-all duration-200">
                    Monthly
                </button>
                <button type="button" x-on:click="billingPeriod = 'yearly'"
                    :class="billingPeriod === 'yearly'
                        ?
                        'bg-primary-600 text-white' :
                        'text-gray-700'"
                    class="px-4 py-1.5 text-sm font-semibold rounded-full transition-all duration-200">
                    Yearly
                </button>
            </div>
        </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

        <div data-aos="fade-down" data-aos-once="true" data-aos-duration="2000"
            class="space-y-8 lg:grid lg:grid-cols-3 sm:gap-6 xl:gap-10 lg:space-y-0">
            <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $plansFeatures; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div <?php if($showBillingToggle): ?>
                        x-show="billingPeriod === '<?php echo e(strtolower($plan->billing_period)); ?>' || '<?php echo e($plan->is_free ? 'true' : 'false'); ?>' === 'true'"
                     <?php endif; ?>
                    class="pricing-card relative flex flex-col w-full p-6 mx-auto text-center text-gray-900 bg-white border border-gray-100 rounded-lg shadow dark:border-gray-600 xl:p-8 dark:bg-gray-800 dark:text-white hover:shadow-lg transition-all duration-300 ease-in-out <?php if($plan['featured'] != 0): ?> ring-2 ring-primary-600 dark:ring-primary-500 <?php endif; ?>"
                    <?php if($showBillingToggle): ?> x-cloak <?php endif; ?>>
                    <!--[if BLOCK]><![endif]--><?php if($plan['featured'] != 0): ?>
                        <!-- Corner Ribbon -->
                        <div class="absolute -top-px -right-px">
                            <div class="relative w-[200px] h-[200px] overflow-hidden">
                                <div
                                    class="absolute top-[30px] right-[-75px] w-[250px] h-[40px] bg-danger-500 transform rotate-45">
                                    <p class="mt-[8px] text-center text-base font-bold text-white">
                                        Most Popular
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                    <h3 class="mb-4 text-2xl font-semibold"><?php echo e($plan->name); ?></h3>
                    <p class="whitespace-normal break-normal dark:text-gray-400 font-light text-gray-500 text-lg">
                        <?php echo e($plan->description); ?></p>

                    <div class="flex items-baseline justify-center my-8">
                        <!--[if BLOCK]><![endif]--><?php if($plan->is_free): ?>
                            <span class="text-4xl font-extrabold text-primary-600 dark:text-primary-400">Free</span>
                        <?php else: ?>
                            <span class="mr-2 text-4xl font-extrabold text-primary-600 dark:text-primary-400">
                                <?php echo e(get_base_currency()->format($plan->price)); ?>

                            </span>
                            <span class="text-gray-500 dark:text-gray-400">/<?php echo e(ucfirst($plan->billing_period)); ?></span>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>

                    <ul role="list" class="mb-8 space-y-4 text-left">
                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $plan['planFeatures']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feature): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <!--[if BLOCK]><![endif]--><?php if($feature['value'] != 0): ?>
                                <li class="flex items-center space-x-3">
                                    <svg class="w-5 h-5 text-primary-500 dark:text-primary-400 flex-shrink-0"
                                        fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="text-gray-700 dark:text-gray-300">
                                        <?php echo e(t($feature['slug'])); ?>:
                                        <span class="text-primary-600 dark:text-primary-400 font-medium">
                                            <?php echo e($feature['value'] == '-1' ? 'Unlimited' : number_format($feature['value'])); ?>

                                        </span>
                                    </span>
                                </li>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                    </ul>

                    <div class="mt-auto">
                        <?php
                            $user = auth()->user();
                            $link = route('register', ['plan_id' => $plan->id]);

                            if ($user) {
                                if (is_null($user->tenant_id)) {
                                    $link = route('admin.dashboard', ['plan_id' => $plan->id]);
                                } elseif ($user->user_type === 'tenant') {
                                    $link = tenant_route('tenant.subscription');
                                }
                            }
                        ?>

                        <a href="<?php echo e($link); ?>"
                            class="inline-flex items-center justify-center w-full px-5 py-3 text-sm font-medium text-center text-white rounded-lg bg-primary-600 hover:bg-primary-700 focus:ring-4 focus:ring-primary-200 dark:focus:ring-primary-900 transition-colors duration-200">
                            <?php echo e(__('get_started_pricing_plans')); ?>

                            <svg class="w-5 h-5 ml-2 -mr-1" fill="currentColor" viewBox="0 0 20 20"
                                xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                    d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z"
                                    clip-rule="evenodd"></path>
                            </svg>
                        </a>
                    </div>
                </div>

            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <!-- No plans message if filtered plans are empty -->
                <div x-cloak class="text-center py-10">
                    <p class="text-gray-600 dark:text-gray-400">No plans available for the selected billing period.
                    </p>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

        </div>
    </div>
    <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
    <script>
        AOS.init();
    </script>
<?php /**PATH /home/adphwiudpl/mark.chatvoo.com/resources/views/livewire/frontend/pricing-plans.blade.php ENDPATH**/ ?>