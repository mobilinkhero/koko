<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">

<head>
    <?php echo $__env->make('themes.thecore.components.elements.head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</head>

<body class="flex flex-col min-h-screen" x-data="{ activeSection: 'home' }" x-init="window.addEventListener('scroll', () => {
    const sections = document.querySelectorAll('section');
    sections.forEach(section => {
        const rect = section.getBoundingClientRect();
        if (rect.top <= window.innerHeight / 2 && rect.bottom >= window.innerHeight / 2) {
            activeSection = section.id;
        }
    });
});">

    <!-- Navigation menu Start block -->
    <?php echo $__env->make('themes.thecore.components.elements.menu', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <!-- Navigation menu End block -->

    <!-- Hero section Start block -->
    <section id="home">
        <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('frontend.hero-section', []);

$__html = app('livewire')->mount($__name, $__params, 'lw-3912845618-0', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
    </section>
    <!-- Hero section End block -->

    <!-- Partner logo's Start block -->
    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('frontend.partner-logos', []);

$__html = app('livewire')->mount($__name, $__params, 'lw-3912845618-1', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
    
    <!-- Partner logo's End block -->

    <!-- Features Start block -->
    <section class="bg-gray-50 dark:bg-gray-800" id="features">
        <div class="max-w-screen-xl px-4 py-8 mx-auto space-y-12 lg:space-y-20 lg:py-24 lg:px-6">
            <!-- Row 1 Start-->
            <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('frontend.unique-feature', []);

$__html = app('livewire')->mount($__name, $__params, 'lw-3912845618-2', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
            <!-- Row 1 End-->

            <!-- Row 2 Start-->
            <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('frontend.feature', []);

$__html = app('livewire')->mount($__name, $__params, 'lw-3912845618-3', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
            <!-- Row 2 End-->
            <!-- Row 2 Start-->
            <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('frontend.feature-two', []);

$__html = app('livewire')->mount($__name, $__params, 'lw-3912845618-4', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
            <!-- Row 2 End-->
             <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('frontend.feature-three', []);

$__html = app('livewire')->mount($__name, $__params, 'lw-3912845618-5', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
        </div>
    </section>
    <!--Features End block -->

    <!-- Testimonials start block -->
    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('frontend.testimonials', []);

$__html = app('livewire')->mount($__name, $__params, 'lw-3912845618-6', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
    <!-- Testimonials End block -->

    <!-- Pricing plans Start block -->
    <section class="bg-white dark:bg-gray-900" id="pricing">
        <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('frontend.pricing-plans', []);

$__html = app('livewire')->mount($__name, $__params, 'lw-3912845618-7', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
    </section>
    <!-- Pricing plans End block -->

    <!--FAQs Start block -->
    <section id="faq">
        <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('frontend.faq-list', []);

$__html = app('livewire')->mount($__name, $__params, 'lw-3912845618-8', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
    </section>
    <!-- FAQs End block -->

    <!-- Footer Start block -->
    <?php echo $__env->make('themes.thecore.components.elements.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <!-- Footer End block -->
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

</body>

</html>
<?php /**PATH /home/adphwiudpl/mark.chatvoo.com/resources/views/default_landing_page.blade.php ENDPATH**/ ?>