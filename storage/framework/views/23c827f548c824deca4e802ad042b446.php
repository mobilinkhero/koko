<div>
     <?php $__env->slot('title', null, []); ?> 
        <?php echo e(t('system_logs')); ?>

     <?php $__env->endSlot(); ?>

    <?php if (isset($component)) { $__componentOriginale19f62b34dfe0bfdf95075badcb45bc2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale19f62b34dfe0bfdf95075badcb45bc2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.breadcrumb','data' => ['items' => [
        ['label' => t('dashboard'), 'route' => route('admin.dashboard')],
        ['label' => t('system_logs')],
    ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('breadcrumb'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
        ['label' => t('dashboard'), 'route' => route('admin.dashboard')],
        ['label' => t('system_logs')],
    ])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale19f62b34dfe0bfdf95075badcb45bc2)): ?>
<?php $attributes = $__attributesOriginale19f62b34dfe0bfdf95075badcb45bc2; ?>
<?php unset($__attributesOriginale19f62b34dfe0bfdf95075badcb45bc2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale19f62b34dfe0bfdf95075badcb45bc2)): ?>
<?php $component = $__componentOriginale19f62b34dfe0bfdf95075badcb45bc2; ?>
<?php unset($__componentOriginale19f62b34dfe0bfdf95075badcb45bc2); ?>
<?php endif; ?>
    <div class="bg-white dark:bg-gray-800 rounded-md" x-data="{ confirmDelete: false, selectedLog: null }">
        <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['class' => 'rounded-md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'rounded-md']); ?>
             <?php $__env->slot('header', null, []); ?> 
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <h3 class="text-xl font-medium text-gray-900 dark:text-white">
                        <?php echo e(t('log_viewer')); ?>

                    </h3>
                    <div class="flex flex-col gap-2 sm:flex-row">
                        <div class="relative w-fit max-w-full">
                            <select wire:model.live="selectedFile"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-md focus:ring-primary-500 focus:border-primary-500 pr-8 pl-2 py-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                                <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $logFiles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <option value="<?php echo e($file); ?>"><?php echo e($file); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <option value=""><?php echo e(t('no_log_files')); ?></option>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            </select>
                        </div>

                        <div class="flex gap-2">
                            <button x-on:click="confirmDelete = true"
                                class="text-white bg-danger-500 hover:bg-danger-600 focus:ring-4 focus:ring-danger-300 font-medium rounded-md text-sm px-4 py-2.5 dark:bg-danger-600 dark:hover:bg-danger-700 focus:outline-none dark:focus:ring-danger-800"
                                <?php if(empty($logFiles)): ?> disabled <?php endif; ?>>
                                <span class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <?php echo e(t('delete')); ?>

                                </span>
                            </button>
                            <button wire:click="refreshLogFiles"
                                class="text-white bg-primary-600 hover:bg-primary-700 focus:ring-4 focus:ring-primary-300 font-medium rounded-md text-sm px-4 py-2.5 dark:bg-primary-600 dark:hover:bg-primary-700 focus:outline-none dark:focus:ring-primary-800">
                                <span class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <?php echo e(t('refresh')); ?>

                                </span>
                            </button>
                        </div>
                    </div>
                </div>
             <?php $__env->endSlot(); ?>
             <?php $__env->slot('content', null, []); ?> 
                <!-- Filter Options -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
                    <div class="lg:col-span-7">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400"
                                    viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <input wire:model.live.debounce.300ms="searchTerm" type="text"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-md focus:ring-primary-500 focus:border-primary-500 block w-full pl-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                placeholder="Search logs...">
                        </div>
                    </div>
                    <div class="md:col-span-3">
                        <select wire:model.live="perPage"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-md focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                            <option value="15">15 <?php echo e(t('per_page')); ?></option>
                            <option value="30">30 <?php echo e(t('per_page')); ?></option>
                            <option value="50">50 <?php echo e(t('per_page')); ?></option>
                            <option value="100">100 <?php echo e(t('per_page')); ?></option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <button
                            x-on:click="if (confirm('Are you sure you want to clear all log files? This action cannot be undone.')) $wire.call('clearAllLogs')"
                            class="w-full text-white bg-danger-500 hover:bg-danger-600 focus:ring-4 focus:ring-danger-300 font-medium rounded-md text-sm px-4 py-2.5 dark:bg-danger-600 dark:hover:bg-danger-700 focus:outline-none dark:focus:ring-danger-800"
                            <?php if(empty($logFiles)): ?> disabled <?php endif; ?>>
                            <?php echo e(t('clear_all_logs')); ?>

                        </button>

                    </div>
                </div>

                <!-- Log Level Filters -->
                <div class="mt-4 flex flex-wrap gap-2">
                    <button wire:click="toggleLogLevel('emergency')"
                        class="px-3 py-1 rounded-full text-xs font-medium <?php echo e($logLevels['emergency'] ? 'bg-danger-500 text-white' : 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300'); ?>">
                        <?php echo e(t('emergency')); ?>

                    </button>
                    <button wire:click="toggleLogLevel('alert')"
                        class="px-3 py-1 rounded-full text-xs font-medium <?php echo e($logLevels['alert'] ? 'bg-danger-500 text-white' : 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300'); ?>">
                        <?php echo e(t('alert')); ?>

                    </button>
                    <button wire:click="toggleLogLevel('critical')"
                        class="px-3 py-1 rounded-full text-xs font-medium <?php echo e($logLevels['critical'] ? 'bg-danger-500 text-white' : 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300'); ?>">
                        <?php echo e(t('critical')); ?>

                    </button>
                    <button wire:click="toggleLogLevel('error')"
                        class="px-3 py-1 rounded-full text-xs font-medium <?php echo e($logLevels['error'] ? 'bg-danger-500 text-white' : 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300'); ?>">
                        <?php echo e(t('error')); ?>

                    </button>
                    <button wire:click="toggleLogLevel('warning')"
                        class="px-3 py-1 rounded-full text-xs font-medium <?php echo e($logLevels['warning'] ? 'bg-warning-500 text-white' : 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300'); ?>">
                        <?php echo e(t('warning')); ?>

                    </button>
                    <button wire:click="toggleLogLevel('notice')"
                        class="px-3 py-1 rounded-full text-xs font-medium <?php echo e($logLevels['notice'] ? 'bg-info-500 text-white' : 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300'); ?>">
                        <?php echo e(t('notice')); ?>

                    </button>
                    <button wire:click="toggleLogLevel('info')"
                        class="px-3 py-1 rounded-full text-xs font-medium <?php echo e($logLevels['info'] ? 'bg-info-500 text-white' : 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300'); ?>">
                        <?php echo e(t('info_log')); ?>

                    </button>
                    <button wire:click="toggleLogLevel('debug')"
                        class="px-3 py-1 rounded-full text-xs font-medium <?php echo e($logLevels['debug'] ? 'bg-success-500 text-white' : 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300'); ?>">
                        <?php echo e(t('debug')); ?>

                    </button>
                    <button wire:click="toggleLogLevel('local')"
                        class="px-3 py-1 rounded-full text-xs font-medium <?php echo e(isset($logLevels['local']) && $logLevels['local'] ? 'bg-purple-500 text-white' : 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300'); ?>">
                        <?php echo e(t('local')); ?>

                    </button>
                </div>

                <!-- Log Viewer -->
                <div wire:loading.remove class="mt-4 overflow-x-auto rounded-md">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300 w-[10%]">
                                    <?php echo e(t('level')); ?>

                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300 w-[20%]">
                                    <?php echo e(t('date')); ?>

                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300 w-[60%]">
                                    <?php echo e(t('content')); ?>

                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300 w-[10%]">
                                    <?php echo e(t('actions')); ?>

                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                            <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                            $levelColor = match (strtolower($log['level'])) {
                                                'emergency'
                                                    => 'bg-danger-800 text-white dark:bg-danger-900 dark:text-danger-100',
                                                'alert' => 'bg-danger-600 text-white dark:bg-danger-800 dark:text-danger-200',
                                                'critical' => 'bg-danger-500 text-white dark:bg-danger-700 dark:text-danger-200',
                                                'error' => 'bg-danger-400 text-white dark:bg-danger-600 dark:text-danger-100',
                                                'warning'
                                                    => 'bg-amber-400 text-amber-900 dark:bg-amber-600 dark:text-amber-100',
                                                'notice'
                                                    => 'bg-info-400 text-white dark:bg-info-600 dark:text-info-100',
                                                'info' => 'bg-info-500 text-white dark:bg-sky-600 dark:text-sky-100',
                                                'debug'
                                                    => 'bg-emerald-400 text-emerald-900 dark:bg-emerald-700 dark:text-emerald-100',
                                                'local'
                                                    => 'bg-purple-400 text-white dark:bg-purple-700 dark:text-purple-100',
                                                default
                                                    => 'bg-gray-300 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                            };
                                        ?>
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo e($levelColor); ?>">
                                            <?php echo e(strtoupper($log['level'])); ?>

                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        <?php echo e($log['date']); ?>

                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        <div x-data="{ expanded: false }" class="relative">
                                            <div x-show="!expanded" class="line-clamp-2">
                                                <?php echo e(Str::limit($log['content'], 150)); ?>

                                            </div>
                                            <div x-show="expanded" class="whitespace-pre-wrap"><?php echo e($log['content']); ?>

                                            </div>
                                            <!--[if BLOCK]><![endif]--><?php if(strlen($log['content']) > 150): ?>
                                                <button x-on:click="expanded = !expanded"
                                                    class="text-primary-600 hover:text-primary-500 text-xs mt-1 dark:text-primary-400 dark:hover:text-primary-300">
                                                    <span x-show="!expanded">Show more</span>
                                                    <span x-show="expanded">Show less</span>
                                                </button>
                                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button
                                            x-on:click="$dispatch('open-modal', 'log-detail-modal'); selectedLog = <?php echo \Illuminate\Support\Js::from($log)->toHtml() ?>"
                                            class="text-primary-600 hover:text-primary-900 dark:text-primary-400 dark:hover:text-primary-300">
                                            <?php echo e(t('view')); ?>

                                        </button>

                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="4"
                                        class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                        <!--[if BLOCK]><![endif]--><?php if(!$selectedFile): ?>
                                            <?php echo e(t('no_log_file_selected')); ?>.
                                        <?php elseif(empty($searchTerm) && count(array_filter($logLevels)) === 0): ?>
                                            <?php echo e(t('no_log_entries_found_the_file_may_be_empty')); ?>

                                        <?php else: ?>
                                            <?php echo e(t('no_log_entries')); ?>

                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    </td>
                                </tr>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </tbody>
                    </table>
                </div>

                <!-- Loading indicator -->
                <div wire:loading class="flex justify-center items-center py-12">
                    <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-primary-600"></div>
                </div>

                <!-- Pagination -->
                <!--[if BLOCK]><![endif]--><?php if($logs->count() > 0 && $totalPages > 1): ?>
                    <div
                        class="mt-4 px-4 py-3 flex items-center justify-between border-t border-gray-200 dark:border-gray-700 sm:px-6">
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700 dark:text-gray-300">
                                    <?php echo e(t('showing_page')); ?> <span class="font-medium"><?php echo e($this->page); ?></span>
                                    <?php echo e(t('of')); ?> <span class="font-medium"><?php echo e($totalPages); ?></span>
                                </p>
                            </div>
                            <div>
                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px"
                                    aria-label="Pagination">
                                    <button wire:click="setPage(1)" <?php if($this->page === 1): ?> disabled <?php endif; ?>
                                        class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700 <?php if($this->page === 1): ?> opacity-50 cursor-not-allowed <?php endif; ?>">
                                        <span class="sr-only"><?php echo e(t('first')); ?></span>
                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                            fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd"
                                                d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                            fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd"
                                                d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <button wire:click="previousPage" <?php if($this->page === 1): ?> disabled <?php endif; ?>
                                        class="relative inline-flex items-center px-2 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700 <?php if($this->page === 1): ?> opacity-50 cursor-not-allowed <?php endif; ?>">
                                        <span class="sr-only"><?php echo e(t('previous')); ?></span>
                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                            fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd"
                                                d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <span
                                        class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300">
                                        <?php echo e($this->page); ?> / <?php echo e($totalPages); ?>

                                    </span>
                                    <button wire:click="nextPage" <?php if($this->page === $totalPages): ?> disabled <?php endif; ?>
                                        class="relative inline-flex items-center px-2 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700 <?php if($this->page === $totalPages): ?> opacity-50 cursor-not-allowed <?php endif; ?>">
                                        <span class="sr-only"><?php echo e(t('next')); ?></span>
                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                            fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd"
                                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <button wire:click="setPage(<?php echo e($totalPages); ?>)"
                                        <?php if($this->page === $totalPages): ?> disabled <?php endif; ?>
                                        class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700 <?php if($this->page === $totalPages): ?> opacity-50 cursor-not-allowed <?php endif; ?>">
                                        <span class="sr-only"><?php echo e(t('last')); ?></span>
                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                            fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd"
                                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                            fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd"
                                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </nav>
                            </div>
                        </div>
                    </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
             <?php $__env->endSlot(); ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal53747ceb358d30c0105769f8471417f6)): ?>
<?php $attributes = $__attributesOriginal53747ceb358d30c0105769f8471417f6; ?>
<?php unset($__attributesOriginal53747ceb358d30c0105769f8471417f6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal53747ceb358d30c0105769f8471417f6)): ?>
<?php $component = $__componentOriginal53747ceb358d30c0105769f8471417f6; ?>
<?php unset($__componentOriginal53747ceb358d30c0105769f8471417f6); ?>
<?php endif; ?>

        <!-- Delete Confirmation Modal -->
        <div x-show="confirmDelete" class="fixed inset-0 overflow-y-auto z-50" style="display: none;">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="confirmDelete" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity"
                    x-on:click="confirmDelete = false">
                    <div class="absolute inset-0 bg-gray-500 opacity-75 dark:bg-gray-900"></div>
                </div>

                <!-- Modal panel -->
                <div x-show="confirmDelete" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-md text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-danger-100 dark:bg-danger-900 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-danger-600 dark:text-danger-400" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                                    <?php echo e(t('delete_log_file')); ?>

                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        <?php echo e(t('delete_log_file_confirmation')); ?>

                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-danger-600 text-base font-medium text-white hover:bg-danger-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-danger-500 sm:ml-3 sm:w-auto sm:text-sm"
                            wire:click="deleteFile" x-on:click="confirmDelete = false">
                            <?php echo e(t('delete')); ?>

                        </button>
                        <button type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                            x-on:click="confirmDelete = false">
                            <?php echo e(t('cancel')); ?>

                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Log Detail Modal -->
        <?php if (isset($component)) { $__componentOriginal9f64f32e90b9102968f2bc548315018c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9f64f32e90b9102968f2bc548315018c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal','data' => ['name' => 'log-detail-modal','maxWidth' => '4xl']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'log-detail-modal','maxWidth' => '4xl']); ?>
            <div class="p-6 break-words">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white"
                    x-text="selectedLog ? 'Log Entry: ' + selectedLog.date : 'Log Detail'"></h3>

                <div class="mt-4">
                    <div class="mb-2">
                        <span class="font-semibold text-gray-700 dark:text-gray-300"><?php echo e(t('level')); ?>:</span>
                        <span x-text="selectedLog ? selectedLog.level.toUpperCase() : ''"
                            class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                            :class="{
                                'bg-danger-100 text-danger-800 dark:bg-danger-900 dark:text-danger-300': selectedLog && [
                                    'emergency',
                                    'alert', 'critical', 'error'
                                ].includes(selectedLog.level),
                                'bg-warning-100 text-warning-800 dark:bg-warning-900 dark:text-warning-300': selectedLog &&
                                    selectedLog.level === 'warning',
                                'bg-info-100 text-info-800 dark:bg-info-900 dark:text-info-300': selectedLog && [
                                    'notice',
                                    'info'
                                ].includes(selectedLog.level),
                                'bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-300': selectedLog &&
                                    selectedLog.level === 'debug'
                            }"></span>
                    </div>

                    <div class="mb-2">
                        <span class="font-semibold text-gray-700 dark:text-gray-300"><?php echo e(t('date')); ?>:</span>
                        <span x-text="selectedLog ? selectedLog.date : ''"
                            class="ml-2 text-gray-600 dark:text-gray-400"></span>
                    </div>

                    <div class="mb-2">
                        <span class="font-semibold text-gray-700 dark:text-gray-300"><?php echo e(t('environment')); ?>:</span>
                        <span x-text="selectedLog ? selectedLog.environment : ''"
                            class="ml-2 text-gray-600 dark:text-gray-400"></span>
                    </div>

                    <!-- Updated JSON Content Display with log parsing -->
                    <div x-data="{
                        formattedContent: '',
                        messageContent: '',
                        jsonContent: null,
                        hasJson: false,
                    
                        parseLogContent() {
                            if (!selectedLog || !selectedLog.content) {
                                this.formattedContent = '';
                                this.messageContent = '';
                                this.hasJson = false;
                                return;
                            }
                    
                            const content = selectedLog.content;
                    
                            // Try to extract message and JSON parts
                            const jsonMatch = content.match(/(\{.*\})/);
                    
                            if (jsonMatch) {
                                // Extract the message part (text before JSON)
                                const messagePart = content.substring(0, jsonMatch.index).trim();
                                this.messageContent = messagePart;
                    
                                // Extract and format the JSON part
                                const jsonString = jsonMatch[0];
                                try {
                                    const parsedJson = JSON.parse(jsonString);
                    
                                    // Check for nested JSON strings within properties
                                    for (const key in parsedJson) {
                                        if (typeof parsedJson[key] === 'string' &&
                                            parsedJson[key].startsWith('{') &&
                                            parsedJson[key].endsWith('}')) {
                                            try {
                                                parsedJson[key] = JSON.parse(parsedJson[key]);
                                            } catch (e) {
                                                // Keep as string if parsing fails
                                            }
                                        }
                                    }
                    
                                    this.jsonContent = parsedJson;
                                    this.formattedContent = JSON.stringify(parsedJson, null, 2);
                                    this.hasJson = true;
                                } catch (e) {
                                    // If JSON parsing fails, show original content
                                    this.messageContent = content;
                                    this.hasJson = false;
                                }
                            } else {
                                // No JSON found, treat entire content as message
                                this.messageContent = content;
                                this.hasJson = false;
                            }
                        },
                    
                        init() {
                            this.$watch('selectedLog', () => this.parseLogContent());
                            // Parse initially if selectedLog is already set
                            if (selectedLog) this.parseLogContent();
                        }
                    }">
                        <span class="font-semibold text-gray-700 dark:text-gray-300"><?php echo e(t('content')); ?>:</span>
                        <div class="mt-2 bg-gray-100 dark:bg-gray-700 p-4 rounded-md">
                            <!-- Log message part -->
                            <div x-show="messageContent" class="mb-2 text-gray-800 dark:text-gray-200">
                                <span x-text="messageContent"></span>
                            </div>

                            <!-- JSON part with syntax highlighting -->
                            <div x-show="hasJson">
                                <div class="border-t border-gray-300 dark:border-gray-600 my-2 pt-2">
                                    <h4 class="text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">JSON Data:
                                    </h4>
                                    <pre x-html="formatJsonWithHighlighting(formattedContent)"
                                        class="whitespace-pre-wrap break-words text-sm text-gray-800 dark:text-gray-200 overflow-auto max-h-96"></pre>
                                </div>
                            </div>

                            <!-- Fallback for non-JSON content -->
                            <div x-show="!hasJson && !messageContent">
                                <pre x-text="selectedLog ? selectedLog.content : ''"
                                    class="whitespace-pre-wrap  text-sm text-gray-800 dark:text-gray-200 overflow-auto max-h-96"></pre>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button x-on:click="$dispatch('close-modal', 'log-detail-modal')"
                        class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 focus:outline-none">
                        <?php echo e(t('close')); ?>

                    </button>
                </div>
            </div>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9f64f32e90b9102968f2bc548315018c)): ?>
<?php $attributes = $__attributesOriginal9f64f32e90b9102968f2bc548315018c; ?>
<?php unset($__attributesOriginal9f64f32e90b9102968f2bc548315018c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9f64f32e90b9102968f2bc548315018c)): ?>
<?php $component = $__componentOriginal9f64f32e90b9102968f2bc548315018c; ?>
<?php unset($__componentOriginal9f64f32e90b9102968f2bc548315018c); ?>
<?php endif; ?>

        <!-- JSON highlighting -->
        <script>
            function formatJsonWithHighlighting(json) {
                if (!json) return '';

                return json
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(
                        /("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g,
                        function(match) {
                            let cls = 'text-info-600 dark:text-info-400'; // number
                            if (/^"/.test(match)) {
                                if (/:$/.test(match)) {
                                    cls = 'text-danger-600 dark:text-danger-400'; // key
                                } else {
                                    cls = 'text-success-600 dark:text-success-400'; // string
                                }
                            } else if (/true|false/.test(match)) {
                                cls = 'text-purple-600 dark:text-purple-400'; // boolean
                            } else if (/null/.test(match)) {
                                cls = 'text-gray-600 dark:text-gray-400'; // null
                            }
                            return '<span class="' + cls + '">' + match + '</span>';
                        });
            }
        </script>
    </div>
</div>
<?php /**PATH /home/ahtisham/app.chatvoo.com/Modules/LogViewer/resources/views/livewire/log-viewer.blade.php ENDPATH**/ ?>