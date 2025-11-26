<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
'title' => '',
'value' => null,
'suffix' => '',
'limit' => null,
'suffix_limit' => '',
'subtitle' => null,
'action' => null,
'color' => 'blue',
'icon' => null,
'progress' => null,
'bg' => false
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
'title' => '',
'value' => null,
'suffix' => '',
'limit' => null,
'suffix_limit' => '',
'subtitle' => null,
'action' => null,
'color' => 'blue',
'icon' => null,
'progress' => null,
'bg' => false
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<?php
$colorClasses = [
'blue' => 'text-info-600 dark:text-info-400',
'emerald' => 'text-emerald-600 dark:text-emerald-400',
'purple' => 'text-purple-600 dark:text-purple-400',
'amber' => 'text-warning-600 dark:text-warning-400',
'indigo' => 'text-primary-600 dark:text-primary-400',
'red' => 'text-danger-600 dark:text-danger-400',
'cyan' => 'text-cyan-600 dark:text-cyan-400',
'orange' => 'text-orange-600 dark:text-orange-400',
'rose' => 'text-rose-600 dark:text-rose-400',
];

$bgClasses = [
'blue' => 'bg-info-100 dark:bg-info-900/30',
'emerald' => 'bg-emerald-100 dark:bg-emerald-900/30',
'purple' => 'bg-purple-100 dark:bg-purple-900/30',
'amber' => 'bg-warning-100 dark:bg-warning-900/30',
'indigo' => 'bg-primary-100 dark:bg-primary-900/30',
'red' => 'bg-danger-100 dark:bg-danger-900/30',
'cyan' => 'bg-cyan-100 dark:bg-cyan-900/30',
'orange' => 'bg-orange-100 dark:bg-orange-900/30',
'rose' => 'bg-rose-100 dark:bg-rose-900/30',
];

$progressBarClasses = [
'blue' => 'bg-info-500',
'emerald' => 'bg-emerald-500',
'purple' => 'bg-purple-500',
'amber' => 'bg-warning-500',
'indigo' => 'bg-primary-500',
'red' => 'bg-danger-500',
'cyan' => 'bg-cyan-500',
'orange' => 'bg-orange-500',
'rose' => 'bg-rose-500',
];

$buttonClasses = [
'blue' => 'text-info-600 hover:text-info-700 dark:text-info-400 dark:hover:text-info-300',
'emerald' => 'text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300',
'purple' => 'text-purple-600 hover:text-purple-700 dark:text-purple-400 dark:hover:text-purple-300',
'amber' => 'text-warning-600 hover:text-warning-700 dark:text-warning-400 dark:hover:text-warning-300',
'indigo' => 'text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300',
'red' => 'text-danger-600 hover:text-danger-700 dark:text-danger-400 dark:hover:text-danger-300',
'cyan' => 'text-cyan-600 hover:text-cyan-700 dark:text-cyan-400 dark:hover:text-cyan-300',
'orange' => 'text-orange-600 hover:text-orange-700 dark:text-orange-400 dark:hover:text-orange-300',
'rose' => 'text-rose-600 hover:text-rose-700 dark:text-rose-400 dark:hover:text-rose-300',
];

$iconBgClass = $bg ? $bgClasses[$color] : 'bg-white dark:bg-gray-800';
?>

<div
    class="bg-white ring-1 ring-slate-300 rounded-lg dark:bg-gray-800 dark:ring-slate-600 p-6 hover:shadow-md transition-all duration-200 group">
    <div class="flex items-start justify-between">
        <div class="flex-1">
            <h3 class="text-lg font-medium text-slate-800 dark:text-slate-200"><?php echo e($title); ?></h3>
            <!--[if BLOCK]><![endif]--><?php if($value !== null): ?>
            <div class="flex items-baseline mt-1 space-x-2">
                <span class="text-2xl font-bold text-slate-900 dark:text-slate-100"><?php echo e($value); ?><?php echo e($suffix); ?></span>
                <!--[if BLOCK]><![endif]--><?php if($limit !== null): ?>
                <span class="text-lg text-slate-600 dark:text-slate-400">/</span>
                <span class="text-lg font-semibold text-slate-700 dark:text-slate-300"><?php echo e($limit); ?><?php echo e($suffix_limit); ?></span>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </div>
        <!--[if BLOCK]><![endif]--><?php if($icon): ?>
        <div class="ml-4 rounded-lg <?php echo e($iconBgClass); ?> p-3">
            <?php echo e($icon); ?>

        </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    </div>

    <!--[if BLOCK]><![endif]--><?php if($progress !== null): ?>
    <div class="mb-4">
        <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2">
            <div class="<?php echo e($progressBarClasses[$color]); ?> h-2 rounded-full transition-all duration-300"
                style="width: <?php echo e($progress); ?>%"></div>
        </div>
    </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <div class="flex items-center justify-between">
        <!--[if BLOCK]><![endif]--><?php if($subtitle): ?>
        <span class="text-sm text-slate-600 dark:text-slate-400"><?php echo e($subtitle); ?></span>
        <?php else: ?>
        <span></span>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

        <!--[if BLOCK]><![endif]--><?php if($action && isset($attributes['href'])): ?>
        <a href="<?php echo e($attributes['href']); ?>"
            class="<?php echo e($buttonClasses[$color]); ?> text-sm font-medium hover:underline transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-<?php echo e($color); ?>-500">
            <?php echo e($action); ?>

        </a>
        <?php elseif($action): ?>
        <button
            class="<?php echo e($buttonClasses[$color]); ?> text-sm font-medium hover:underline transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-<?php echo e($color); ?>-500">
            <?php echo e($action); ?>

        </button>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    </div>
</div><?php /**PATH /home/ahtisham/app.chatvoo.com/resources/views/components/dashboard/stats-card.blade.php ENDPATH**/ ?>