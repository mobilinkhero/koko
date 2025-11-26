<div
  <?php echo e($attributes->merge(['class' => 'bg-white ring-1 ring-slate-300 rounded-lg dark:bg-transparent dark:ring-slate-600'])); ?>>
  <?php if(isset($header)): ?>
    <div
      <?php echo e($header->attributes->class(['border-b border-slate-300 px-4 py-3 sm:px-6 dark:border-slate-600'])); ?>>
      <?php echo e($header); ?>

    </div>
  <?php endif; ?>
  <?php if(isset($content)): ?>
    <div <?php echo e($content->attributes->class(['px-4 py-4 sm:p-6'])); ?>>
      <?php echo e($content); ?>

    </div>
  <?php endif; ?>
  <?php if(isset($footer)): ?>
    <div
      <?php echo e($footer->attributes->class(['border-t bg-slate-50 dark:bg-transparent rounded-b-lg border-slate-300 px-4 py-3 sm:px-6 dark:border-slate-600'])); ?>>
      <?php echo e($footer); ?>

    </div>
  <?php endif; ?>
</div>
<?php /**PATH /var/www/vhosts/projectnow.run.place/httpdocs/resources/views/components/card.blade.php ENDPATH**/ ?>