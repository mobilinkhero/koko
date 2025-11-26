<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'align' => 'right',
    'width' => '48',
    'isDropUp' => false,
    'closeOnClick' => true,
    'triggerClasses' => '',
    'contentClasses' => 'py-1 bg-white dark:bg-slate-800',
    'customClasses' => '',
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
    'align' => 'right',
    'width' => '48',
    'isDropUp' => false,
    'closeOnClick' => true,
    'triggerClasses' => '',
    'contentClasses' => 'py-1 bg-white dark:bg-slate-800',
    'customClasses' => '',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<?php
  switch ($align) {
      case 'left':
          $alignmentClasses = 'origin-top-left left-0';
          break;
      case 'top':
          $alignmentClasses = 'origin-top';
          break;
      case 'right':
      default:
          $alignmentClasses = 'origin-top-right right-0';
          break;
  }

  switch ($width) {
      case '48':
          $width = 'w-48';
          break;
      case '64':
          $width = 'w-64';
          break;
      case '80':
          $width = 'w-80';
          break;
      case '96':
          $width = 'w-96';
          break;
      case 'full':
          $width = 'w-full';
          break;
  }
?>

<div class="relative " x-data="{ open: false }" x-on:click.outside="open = false"
  @close.stop="open = false">
  <div x-on:click="open = ! open" class="<?php echo e($triggerClasses); ?>">
    <?php echo e($trigger); ?>

  </div>

  <div x-show="open" x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="transform opacity-0 scale-95"
    x-transition:enter-end="transform opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-75"
    x-transition:leave-start="transform opacity-100 scale-100"
    x-transition:leave-end="transform opacity-0 scale-95"
    class="absolute  z-50 my-2 <?php echo e($width); ?> rounded-md shadow-lg <?php echo e($alignmentClasses); ?> <?php echo e($isDropUp ? 'bottom-full' : 'top-full'); ?> 
        <?php echo e($customClasses); ?>"
    style="display: none;" <?php if($closeOnClick): ?> x-on:click="open = false" <?php endif; ?>>
    <div
      class="rounded-md ring-1 ring-black ring-opacity-5  dark:ring-slate-600 <?php echo e($contentClasses); ?>">
      <?php echo e($content); ?>

    </div>
  </div>
</div>
<?php /**PATH /home/ahtisham/app.chatvoo.com/resources/views/components/dropdown.blade.php ENDPATH**/ ?>